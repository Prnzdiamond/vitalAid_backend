<?php

namespace App\Jobs\ConsultationJobs;

use Illuminate\Support\Facades\Log;
use App\Models\VitalAid\Consultation;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckInactivityTimeout implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $consultations = Consultation::where('status', 'in_progress')
            ->where('last_message_at', '<', now()->subMinutes(5))
            ->get();

        foreach ($consultations as $consultation) {
            Log::info("doing it");
            $consultation->update(['status' => 'completed']);
        }
    }

}
