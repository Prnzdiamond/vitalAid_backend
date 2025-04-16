<?php

namespace App\Http\Controllers\VitalAid;

use App\Models\User;
use App\Events\MessageSent;
use App\Services\AIService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\ConsultationAccepted;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Events\ConsultationRequested;
use App\Events\ConsultationUpdated;
use App\Models\VitalAid\Consultation;
use Illuminate\Notifications\DatabaseNotification;
use App\Jobs\ConsultationJobs\CheckDoctorResponseJob;
use App\Notifications\ConsultationRequested as NotifyConsultationRequested;

class ConsultationController extends Controller
{
    // Request a Consultation
    public function requestConsultation(Request $request)
    {
        $user = $request->user();

        $consultation = Consultation::create([
            'user_id' => $user->id,
            'doctor_id' => null,
            'status' => 'in_progress'
        ]);

        broadcast(new ConsultationRequested($consultation));


        $doctors = User::where('role', 'health_expert')->get();

        foreach ($doctors as $doctor) {
            Log::info("Sending notification to doctor ID: " . $doctor->id);
            $doctor->notify(new NotifyConsultationRequested($consultation));
        }

        CheckDoctorResponseJob::dispatch($consultation->id)->delay(now()->addSeconds(10));

        return response()->json(['message' => 'Consultation requested', 'consultation' => $consultation]);
    }


    // Accept Consultation
    public function acceptConsultation(Request $request, $id)
    {
        $user = $request->user();
        // if (!$user->isRole('health_expert')) {
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }
        ;
        $consultation = Consultation::findOrFail($id);
        // $consultation->update(['doctor_id' => $user->id, 'status' => 'in_progress']);

        $notifications = $user->notifications()->where("read_at", null)->get();
        foreach ($notifications as $notification) {
            $data = $notification->data;

            if ($data && isset($data['consultation_id']) && $data['consultation_id'] === (string) $consultation->id) {
                // Log::info("Deleting notification ID: " . $notification->id);
                $notification->read_at = now();
                $notification->save();
            }
        }



        return response()->json(['message' => 'Consultation accepted', 'consultation' => $consultation]);
    }

    // Send a Message
    public function sendMessage(Request $request, $id)
    {
        $user = $request->user();
        $consultation = Consultation::findOrFail($id);

        if ($consultation->isCompleted()) {
            return response()->json(['message' => 'Consultation is already completed'], 403);
        }

        // Ensure messages is an array
        $messages = $consultation->messages ?? [];

        // Append the new message
        $messages[] = [
            'sender' => $user->_tag,
            'message' => $request->message,
            'timestamp' => now(),
        ];

        // Save back the updated messages array
        $consultation->messages = $messages;
        $consultation->last_message_at = now();
        $consultation->save();

        if (count($messages) >= 5) {
            AIService::summarizeChat($id);
        }

        broadcast(new MessageSent($consultation, end($messages)));

        if ($consultation->handled_by != 'health_expert') {

            $aiResponse = AIService::generateResponse($consultation->id, $request->message);

            $aiMessage = [
                'sender' => 'AI',
                'message' => $aiResponse,
                'timestamp' => now(),
            ];

            $messages[] = $aiMessage;
            $consultation->messages = $messages;
            $consultation->save();

            // Broadcast AI response
            broadcast(new MessageSent($consultation, $aiMessage));
        }

        // Broadcast event


        Log::info(end($messages));

        return response()->json(['message' => 'Message sent', 'data' => end($messages)]);
    }

    public function takeOver(Request $request, $id)
    {

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$user->isRole("health_expert")) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $consultation = Consultation::findOrFail($id);

        if ($consultation->isCompleted()) {
            return response()->json(['message' => 'Consultation is already completed'], 403);
        }

        $consultation->handled_by = 'health_expert';
        $consultation->update(['doctor_id' => $user->id, 'status' => 'in_progress']);
        $consultation->save();

        broadcast(new ConsultationAccepted($consultation));
        event(new ConsultationAccepted($consultation));
        // send notification that the user has taken over the consulation(send it via message but set up frontend to handle types of messages sender would be system)
        $systemMessage = [
            'sender' => 'System',
            'message' => "An health expert has taken over this conversation",
            'timestamp' => now(),
        ];

        broadcast(new MessageSent($consultation, $systemMessage));
        broadcast(new ConsultationUpdated($consultation));

        return response()->json(['message' => 'Consultation taken over', 'data' => $consultation]);

    }


    // Controller method to end consultation
    public function endConsultation(Request $request, $id)
    {
        $user = $request->user();


        $consultation = Consultation::findOrFail($id);


        if ($consultation->user_id != $user->id & $consultation->doctor_id != $user->id) {
            return response()->json(['message' => 'Nothing concerns you with this consultation'], 401);
        }

        if ($consultation->status !== 'in_progress') {
            return response()->json(['message' => 'Consultation not in progress'], 400);
        }

        $consultation->status = 'completed';
        $consultation->save();

        // Optionally, broadcast an event to notify others (like UI updates)
        $systemMessage = [
            'sender' => 'System',
            'message' => "This Chat has been Ended",
            'timestamp' => now(),
        ];
        // broadcast(new ConsultationEnded($consultation));
        broadcast(new MessageSent($consultation, $systemMessage));
        broadcast(new ConsultationUpdated($consultation));

        return response()->json(['message' => 'Consultation ended successfully', 'consultation' => $consultation]);
    }




}