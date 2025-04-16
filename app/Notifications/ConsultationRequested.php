<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ConsultationRequested extends Notification implements ShouldQueue
{
    use Queueable;

    protected $consultation;

    public function __construct($consultation)
    {
        $this->consultation = $consultation;
    }

    // Specify how the notification should be stored in the database
    public function toArray($notifiable)
    {
        Log::info('Database Connection: ', [DB::connection()->getDatabaseName()]);
        return [
            'consultation_id' => $this->consultation->id,
            'message' => "New consultation request from User ID: {$this->consultation->user_id}.",
            'type' => 'consultation',
        ];
    }

    // Specify how the notification should be broadcasted (for real-time updates)
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'consultation_id' => $this->consultation->id,
            'message' => "New consultation request from User ID: {$this->consultation->user_id}.",
            'type' => 'consultation',
        ]);
    }

    // Define which notification channels to use (database & broadcast)
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }
}