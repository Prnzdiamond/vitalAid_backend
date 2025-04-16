<?php

namespace App\Events;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use App\Models\VitalAid\Consultation;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel; // ✅ Correct import

class ConsultationRequested implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $consultation;

    public function __construct($consultation)
    {
        $this->consultation = $consultation;
    }

    public function broadcastOn()
    {
        Log::info("Broadcasting ConsultationRequested event!");
        return new PrivateChannel('consultations'); // ✅ Use PrivteChannel
    }

    public function broadcastAs()
    {
        return 'consultation.requested';
    }
}