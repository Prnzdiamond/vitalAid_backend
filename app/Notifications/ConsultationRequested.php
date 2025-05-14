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
        Log::info("checking notification for user {$notifiable->id}");
        return [
            'consultation_id' => $this->consultation->id,
            'message' => "New consultation request from User ID: {$this->consultation->user->_tag}.",
            'type' => 'consultation',
        ];
    }

    // public function toBroadcast($notifiable)
    // {
    //     return new BroadcastMessage([
    //         'consultation_id' => $this->consultation->id,
    //         'message' => "New consultation request from User ID: {$this->consultation->user->_tag}.",
    //         'type' => 'consultation',
    //     ]);
    // }

    // Define which notification channels to use (database & broadcast)
    public function via($notifiable)
    {
        return ['database'];
    }
}
