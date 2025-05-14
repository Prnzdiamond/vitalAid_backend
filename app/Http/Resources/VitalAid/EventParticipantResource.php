<?php

namespace App\Http\Resources\VitalAid;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'is_verified' => $this->is_verified,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_tag' => $this->user ? $this->user->_tag : null,
            'event_title' => $this->event->title,
            'event_date' => $this->event->date,
            'event_location' => $this->event->location,
        ];
    }
}