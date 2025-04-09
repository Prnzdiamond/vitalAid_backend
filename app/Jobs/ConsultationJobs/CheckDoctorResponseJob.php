<?php

namespace App\Jobs\ConsultationJobs;


use App\Models\VitalAid\Consultation;
use App\Events\MessageSent;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class CheckDoctorResponseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $consultationId;

    public function __construct($consultationId)
    {
        $this->consultationId = $consultationId;
    }

    public function handle()
    {
        $consultation = Consultation::find($this->consultationId);

        // If consultation still has no doctor assigned, AI takes over
        if ($consultation && $consultation->doctor_id === null) {
            $consultation->handled_by = 'ai';
            $consultation->status = 'in_progress'; // Mark as active
            $consultation->save();

            // AI generates the first response
            $aiResponse = [
                'sender' => 'AI',
                'message' => 'Hello! A doctor is currently unavailable. How can I assist you?',
                'timestamp' => now(),
            ];

            $messages = $consultation->messages ?? [];
            $messages[] = $aiResponse;
            $consultation->messages = $messages;
            $consultation->last_message_at = now();
            $consultation->save();

            // Broadcast AI response
            broadcast(new MessageSent($consultation, $aiResponse));

            Log::info("AI took over consultation {$consultation->id}");
        }
    }
}
