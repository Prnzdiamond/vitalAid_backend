<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ConsultationAccepted implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $consultation;

    public function __construct($consultation)
    {
        $this->consultation = $consultation;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('consultations');
    }

    public function broadcastAs()
    {
        return 'consultation.accepted';
    }
}