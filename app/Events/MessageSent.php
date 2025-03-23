<?php

namespace App\Events;

use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use App\Models\VitalAid\Consultation;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $consultation;
    public $message;

    public function __construct($consultation, array $message)
    {
        $this->consultation = $consultation;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        Log::info("Broadcasting messages", $this->message);
        return new PrivateChannel('consultations.' . $this->consultation->id);
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }
}
