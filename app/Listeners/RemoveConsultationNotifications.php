<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Events\ConsultationAccepted;

class RemoveConsultationNotifications
{
    public function handle(ConsultationAccepted $event)
    {
        $consultation = $event->consultation;
        Log::info("Deleting notifications for consultation ID: " . $consultation->id);

        // Fetch all health experts except the one who accepted the consultation
        $doctors = User::where('role', 'health_expert')
            ->where('id', '!=', $consultation->doctor_id)
            ->get();

        foreach ($doctors as $doctor) {
            Log::info("Checking notifications for doctor ID: " . $doctor->id);

            $notifications = $doctor->notifications()->get(); // Fetch all notifications

            foreach ($notifications as $notification) {
                $data = $notification->data;

                if ($data && isset($data['consultation_id']) && $data['consultation_id'] === (string) $consultation->id) {
                    Log::info("Deleting notification ID: " . $notification->id);
                    $notification->delete();
                }
            }
        }
    }
}
