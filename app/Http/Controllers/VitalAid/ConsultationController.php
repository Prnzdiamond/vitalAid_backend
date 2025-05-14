<?php

namespace App\Http\Controllers\VitalAid;

use App\Events\FollowUpAccepted;
use App\Http\Resources\VitalAid\ConsultationResource;
use App\Models\User;
use App\Events\MessageSent;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\ConsultationAccepted;
use App\Http\Controllers\Controller;
use App\Events\ConsultationRequested;
use App\Events\ConsultationUpdated;
use App\Models\VitalAid\Consultation;
use App\Jobs\ConsultationJobs\CheckDoctorResponseJob;
use App\Notifications\ConsultationRequested as NotifyConsultationRequested;
use App\Notifications\FollowUpRequested;
use App\Notifications\ConsultationRated;
use App\Events\FollowUpRequested as Fup;

class ConsultationController extends Controller
{
    /**
     * Standard JSON response format
     */
    protected function jsonResponse($success, $message, $data = null, $status = 200)
    {
        $response = [
            'success' => $success,
            'message' => $message
        ];

        if ($data) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Create a new consultation request
     */
    public function requestConsultation(Request $request)
    {
        $user = $request->user();

        try {
            $consultation = Consultation::create([
                'user_id' => $user->id,
                'doctor_id' => null,
                'status' => 'in_progress'
            ]);

            $consultationResource = new ConsultationResource($consultation);
            Log::info($consultationResource->toArray($request));

            broadcast(new ConsultationRequested($consultationResource));

            // Notify all health experts
            User::where('role', 'health_expert')->get()
                ->each(function ($doctor) use ($consultation) {
                    $doctor->notify(new NotifyConsultationRequested($consultation));
                });

            CheckDoctorResponseJob::dispatch($consultation->id)->delay(now()->addSeconds(10));

            return $this->jsonResponse(true, 'Consultation requested.', [
                'consultation' => $consultationResource
            ], 201);
        } catch (\Exception $e) {
            Log::error("Failed to request consultation for user {$user->id}: {$e->getMessage()}");
            return $this->jsonResponse(false, 'Failed to request consultation.', null, 500);
        }
    }

    /**
     * Get consultation by ID
     */
    public function getConsultation(Request $request, $id)
    {
        $user = $request->user();

        try {
            $consultation = Consultation::findOrFail($id);

            if ($consultation->user_id != $user->id && $consultation->doctor_id != $user->id) {
                return $this->jsonResponse(false, 'Unauthorized for this consultation.', null, 403);
            }

            return $this->jsonResponse(true, 'Consultation fetched successfully.', [
                'consultation' => new ConsultationResource($consultation)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->jsonResponse(false, 'Consultation not found.', null, 404);
        } catch (\Exception $e) {
            Log::error("Failed to fetch consultation {$id} for user {$user->id}: {$e->getMessage()}");
            return $this->jsonResponse(false, 'Failed to fetch consultation.', null, 500);
        }
    }

    /**
     * Health expert accepts consultation
     */
    public function acceptConsultation(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || !$user->isRole('health_expert')) {
            return $this->jsonResponse(false, 'Unauthorized.', null, 401);
        }

        try {
            $consultation = Consultation::findOrFail($id);
            $consultation->update(['doctor_id' => $user->id, 'status' => 'in_progress']);

            // Mark related notifications as read
            $user->notifications()
                ->whereNull('read_at')
                ->get()
                ->each(function ($notification) use ($consultation) {
                    $data = $notification->data;
                    if ($data && isset($data['consultation_id']) && $data['consultation_id'] === (string) $consultation->id) {
                        $notification->markAsRead();
                    }
                });

            broadcast(new ConsultationAccepted($consultation));
            event(new ConsultationAccepted($consultation));

            return $this->jsonResponse(true, 'Consultation accepted.', [
                'consultation' => new ConsultationResource($consultation)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->jsonResponse(false, 'Consultation not found.', null, 404);
        } catch (\Exception $e) {
            Log::error("Failed to accept consultation {$id} by doctor {$user->id}: {$e->getMessage()}");
            return $this->jsonResponse(false, 'Failed to accept consultation.', null, 500);
        }
    }

    /**
     * Send a message in consultation
     */
    public function sendMessage(Request $request, $id)
    {
        $user = $request->user();

        try {
            $consultation = Consultation::findOrFail($id);

            if ($consultation->isCompleted()) {
                return $this->jsonResponse(false, 'Consultation is already completed.', null, 400);
            }

            $messages = $consultation->messages ?? [];
            $newMessage = [
                'sender' => $user->_tag,
                'message' => $request->message,
                'timestamp' => now(),
            ];
            $messages[] = $newMessage;

            $consultation->messages = $messages;
            $consultation->last_message_at = now();
            $consultation->save();

            broadcast(new MessageSent($consultation, end($messages)));

            // Generate AI response if not handled by health expert
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

                broadcast(new MessageSent($consultation, $aiMessage));
            }

            return $this->jsonResponse(true, 'Message sent.', end($messages));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->jsonResponse(false, 'Consultation not found.', null, 404);
        } catch (\Exception $e) {
            Log::error("Failed to send message in consultation {$id} by user {$user->id}: {$e->getMessage()}");
            return $this->jsonResponse(false, 'Failed to send message.', null, 500);
        }
    }

    /**
     * Health expert takes over a consultation
     */
    public function takeOver(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || !$user->isRole("health_expert")) {
            return $this->jsonResponse(false, 'Unauthorized.', null, 401);
        }

        try {
            $consultation = Consultation::findOrFail($id);

            if ($consultation->isCompleted()) {
                return $this->jsonResponse(false, 'Consultation is already completed.', null, 400);
            }

            $consultation->handled_by = 'health_expert';
            $consultation->update(['doctor_id' => $user->id, 'status' => 'in_progress']);
            $consultation->save();

            broadcast(new ConsultationAccepted($consultation));
            event(new ConsultationAccepted($consultation));

            $systemMessage = [
                'sender' => 'System',
                'message' => "An health expert has taken over this conversation.",
                'timestamp' => now(),
            ];

            broadcast(new MessageSent($consultation, $systemMessage));
            broadcast(new ConsultationUpdated($consultation));

            return $this->jsonResponse(true, 'Consultation taken over.', $consultation);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->jsonResponse(false, 'Consultation not found.', null, 404);
        } catch (\Exception $e) {
            Log::error("Failed to take over consultation {$id} by doctor {$user->id}: {$e->getMessage()}");
            return $this->jsonResponse(false, 'Failed to take over consultation.', null, 500);
        }
    }

    /**
     * End a consultation
     */
    public function endConsultation(Request $request, $id)
    {
        $user = $request->user();

        try {
            $consultation = Consultation::findOrFail($id);

            if ($consultation->user_id != $user->id && $consultation->doctor_id != $user->id) {
                return $this->jsonResponse(false, 'Unauthorized for this consultation.', null, 403);
            }

            if ($consultation->status !== 'in_progress') {
                return $this->jsonResponse(false, 'Consultation not in progress.', null, 400);
            }

            $consultation->status = 'completed';
            $consultation->save();

            $systemMessage = [
                'sender' => 'System',
                'message' => "This Chat has been Ended.",
                'timestamp' => now(),
            ];

            broadcast(new MessageSent($consultation, $systemMessage));
            broadcast(new ConsultationUpdated($consultation));

            return $this->jsonResponse(true, 'Consultation ended successfully.', $consultation);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->jsonResponse(false, 'Consultation not found.', null, 404);
        } catch (\Exception $e) {
            Log::error("Failed to end consultation {$id} by user {$user->id}: {$e->getMessage()}");
            return $this->jsonResponse(false, 'Failed to end consultation.', null, 500);
        }
    }

    /**
     * Get consultation history for a user
     */
    public function consultationHistory(Request $request)
    {
        $user = $request->user();

        try {
            $consultations = $user->consultationsRequested
                ->merge($user->consultationsHandled)
                ->sortByDesc('created_at')
                ->values();

            $consultationsCollection = ConsultationResource::collection($consultations);

            return $this->jsonResponse(true, 'Consultation history fetched successfully.', [
                'consultations' => $consultationsCollection
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to fetch consultation history for user {$user->id}: {$e->getMessage()}");
            return $this->jsonResponse(false, 'Failed to fetch consultation history.', null, 500);
        }
    }

    /**
     * Request follow-up for a completed consultation
     */
    public function requestFollowUp(Request $request, $id)
    {
        $user = $request->user();

        try {
            $consultation = Consultation::findOrFail($id);

            if ($consultation->user_id != $user->id && $consultation->doctor_id != $user->id) {
                return $this->jsonResponse(false, 'Unauthorized for this consultation.', null, 403);
            }

            if ($consultation->status !== 'completed') {
                return $this->jsonResponse(false, 'Cannot request follow-up on an active consultation.', null, 400);
            }

            $consultation->follow_up_requested = true;
            $consultation->follow_up_reason = $request->input('reason', '');
            $consultation->save();

            // Handle notifications based on who requested follow-up
            if ($user->id === $consultation->user_id) {
                // Patient requested follow-up
                if ($consultation->doctor_id) {
                    $doctor = User::find($consultation->doctor_id);
                    if ($doctor) {
                        broadcast(new Fup($consultation, $user));
                        $doctor->notify(new FollowUpRequested($consultation, $user));
                    }
                } else {
                    // Notify all health experts if no specific doctor
                    User::where('role', 'health_expert')->get()
                        ->each(function ($expert) use ($consultation, $user) {
                            $expert->notify(new FollowUpRequested($consultation, $user));
                        });
                }
            } else {
                // Doctor requested follow-up
                $patient = User::find($consultation->user_id);
                if ($patient) {
                    broadcast(new Fup($consultation, $user));
                    $patient->notify(new FollowUpRequested($consultation, $user));
                }
            }

            broadcast(new ConsultationUpdated($consultation));

            return $this->jsonResponse(true, 'Follow-up requested successfully.', [
                'consultation' => new ConsultationResource($consultation)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->jsonResponse(false, 'Consultation not found.', null, 404);
        } catch (\Exception $e) {
            Log::error("Failed to request follow-up for consultation {$id} by user {$user->id}: {$e->getMessage()}");
            return $this->jsonResponse(false, 'Failed to request follow-up.', null, 500);
        }
    }

    /**
     * Health expert accepts a follow-up request
     */
    public function acceptFollowUp(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || !$user->isRole('health_expert')) {
            return $this->jsonResponse(false, 'Unauthorized.', null, 401);
        }

        try {
            $consultation = Consultation::findOrFail($id);

            if (!$consultation->follow_up_requested) {
                return $this->jsonResponse(false, 'No follow-up was requested for this consultation.', null, 400);
            }

            // Update consultation status
            $consultation->status = 'in_progress';
            $consultation->doctor_id = $user->id;
            $consultation->follow_up_requested = false;
            $consultation->save();

            // Mark related notifications as read
            $user->notifications()
                ->whereNull('read_at')
                ->get()
                ->each(function ($notification) use ($consultation) {
                    $data = $notification->data;
                    if (
                        $data && isset($data['consultation_id']) &&
                        $data['consultation_id'] === (string) $consultation->id &&
                        isset($data['type']) && $data['type'] === 'follow_up'
                    ) {
                        $notification->markAsRead();
                    }
                });

            // Add system message
            $messages = $consultation->messages ?? [];
            $systemMessage = [
                'sender' => 'System',
                'message' => "This consultation has been reopened for follow-up by Health Expert {$user->_tag}.",
                'timestamp' => now(),
            ];
            $messages[] = $systemMessage;
            $consultation->messages = $messages;
            $consultation->save();

            // Broadcast updates
            broadcast(new MessageSent($consultation, $systemMessage));
            broadcast(new ConsultationUpdated($consultation));
            broadcast(new ConsultationAccepted($consultation));

            return $this->jsonResponse(true, 'Follow-up accepted successfully.', [
                'consultation' => new ConsultationResource($consultation)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->jsonResponse(false, 'Consultation not found.', null, 404);
        } catch (\Exception $e) {
            Log::error("Failed to accept follow-up for consultation {$id} by doctor {$user->id}: {$e->getMessage()}");
            return $this->jsonResponse(false, 'Failed to accept follow-up.', null, 500);
        }
    }

    /**
     * Rate a completed consultation
     */
    public function rateConsultation(Request $request, $id)
    {
        $user = $request->user();

        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500'
        ]);

        try {
            $consultation = Consultation::findOrFail($id);

            if ($consultation->user_id != $user->id) {
                return $this->jsonResponse(false, 'Only the patient can rate a consultation.', null, 403);
            }

            if ($consultation->status !== 'completed') {
                return $this->jsonResponse(false, 'Can only rate completed consultations.', null, 400);
            }

            $consultation->rating = $request->rating;
            $consultation->rating_comment = $request->input('comment', '');
            $consultation->save();

            // Notify doctor if one exists
            if ($consultation->doctor_id) {
                $doctor = User::find($consultation->doctor_id);
                if ($doctor) {
                    $doctor->notify(new ConsultationRated($consultation, $user));
                }
            }

            broadcast(new ConsultationUpdated($consultation));

            return $this->jsonResponse(true, 'Consultation rated successfully.', [
                'consultation' => new ConsultationResource($consultation)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->jsonResponse(false, 'Consultation not found.', null, 404);
        } catch (\Exception $e) {
            Log::error("Failed to rate consultation {$id} by user {$user->id}: {$e->getMessage()}");
            return $this->jsonResponse(false, 'Failed to rate consultation.', null, 500);
        }
    }

    /**
     * Get consultations for health experts
     */
    public function expertConsultations(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->isRole('health_expert')) {
            return $this->jsonResponse(false, 'Unauthorized.', null, 401);
        }

        try {
            $consultations = Consultation::where(function ($query) use ($user) {
                $query->where('doctor_id', $user->id)
                    ->orWhere('status', 'in_progress')
                    ->orWhere('follow_up_requested', true);
            })
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->jsonResponse(true, 'Expert consultations fetched successfully.', [
                'consultation' => ConsultationResource::collection($consultations)
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to fetch expert consultations for user {$user->id}: {$e->getMessage()}");
            return $this->jsonResponse(false, 'Failed to fetch expert consultations.', null, 500);
        }
    }
}
