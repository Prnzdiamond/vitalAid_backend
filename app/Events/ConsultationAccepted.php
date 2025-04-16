<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ConsultationAccepted
{
    use Dispatchable, SerializesModels;

    public $consultation;

    public function __construct($consultation)
    {
        $this->consultation = $consultation;
    }

    public function broadcastOn()
    {
        return new Channel('consultations');
    }

    public function broadcastAs()
    {
        return 'consultation.accepted';
    }
}
