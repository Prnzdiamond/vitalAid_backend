<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ConsultationRated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $consultation;
    protected $rater;

    public function __construct($consultation, $rater)
    {
        $this->consultation = $consultation;
        $this->rater = $rater;
    }

    // Specify how the notification should be stored in the database
    public function toArray($notifiable)
    {
        Log::info("Sending rating notification for consultation {$this->consultation->id} to user {$notifiable->id}");
        return [
            'consultation_id' => $this->consultation->id,
            'message' => "Your consultation has been rated {$this->consultation->rating} stars by {$this->rater->_tag}.",
            'type' => 'rating',
            'rater_id' => $this->rater->id,
            'rater_tag' => $this->rater->_tag,
            'rating' => $this->consultation->rating,
            'comment' => $this->consultation->rating_comment,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'consultation_id' => $this->consultation->id,
            'message' => "Your consultation has been rated {$this->consultation->rating} stars by {$this->rater->_tag}.",
            'type' => 'rating',
            'rater_id' => $this->rater->id,
            'rater_tag' => $this->rater->_tag,
            'rating' => $this->consultation->rating,
            'comment' => $this->consultation->rating_comment,
        ]);
    }

    // Define which notification channels to use (database & broadcast)
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }
}
