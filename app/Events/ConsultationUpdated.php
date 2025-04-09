<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ConsultationUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $consultation;

    public function __construct($consultation)
    {
        $this->consultation = $consultation;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('consultations.' . $this->consultation->id);
    }

    public function broadcastAs()
    {
        return 'consultation.updated';
    }
}
