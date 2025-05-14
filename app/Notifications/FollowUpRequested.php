<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class FollowUpRequested extends Notification implements ShouldQueue
{
    use Queueable;

    protected $consultation;
    protected $requester;

    public function __construct($consultation, $requester)
    {
        $this->consultation = $consultation;
        $this->requester = $requester;
    }

    // Specify how the notification should be stored in the database
    public function toArray($notifiable)
    {
        Log::info("Sending follow-up notification for consultation {$this->consultation->id} to user {$notifiable->id}");
        return [
            'consultation_id' => $this->consultation->id,
            'message' => "{$this->requester->_tag} has requested a follow-up for your consultation.",
            'type' => 'follow_up',
            'requester_id' => $this->requester->id,
            'requester_tag' => $this->requester->_tag,
            'reason' => $this->consultation->follow_up_reason,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'consultation_id' => $this->consultation->id,
            'message' => "{$this->requester->_tag} has requested a follow-up for your consultation.",
            'type' => 'follow_up',
            'requester_id' => $this->requester->id,
            'requester_tag' => $this->requester->_tag,
            'reason' => $this->consultation->follow_up_reason,
        ]);
    }

    // Define which notification channels to use (database & broadcast)
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }
}
