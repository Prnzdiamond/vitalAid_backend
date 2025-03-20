<?php

namespace App\Http\Controllers\VitalAid;

use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Events\ConsultationRequested;
use App\Models\VitalAid\Consultation;

class ConsultationController extends Controller
{
    // Request a Consultation
    public function requestConsultation(Request $request)
    {
        $user = $request->user();

        $consultation = Consultation::create([
            'user_id' => $user->id,
            'doctor_id' => null,
            'status' => 'pending',
            'handled_by' => 'doctor'
        ]);

        broadcast(new ConsultationRequested($consultation));

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
        $consultation->update(['doctor_id' => $user->id, 'status' => 'in_progress']);

        return response()->json(['message' => 'Consultation accepted', 'consultation' => $consultation]);
    }

    // Send a Message
    public function sendMessage(Request $request, $id)
    {
        $user = $request->user();
        $consultation = Consultation::findOrFail($id);

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
        $consultation->save();

        // Broadcast event
        broadcast(new MessageSent($consultation, end($messages)));


        Log::info(end($messages));

        return response()->json(['message' => 'Message sent', 'data' => end($messages)]);
    }



}
