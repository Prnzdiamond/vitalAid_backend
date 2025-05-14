<?php

namespace App\Http\Resources\VitalAid;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
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
            'user_id' => $this->user_id,
            'doctor_id' => $this->doctor_id,
            'messages' => $this->messages,
            'handled_by' => $this->handled_by,
            'status' => $this->status,
            'memory' => $this->memory,
            'last_message_at' => $this->last_message_at,
            'user_tag' => $this->user ? $this->user->_tag : null,
            'doctor_tag' => $this->doctor ? $this->doctor->_tag : null,
            'rating' => $this->rating,
            'rating_comment' => $this->rating_comment,
            'follow_up_requested' => $this->follow_up_requested,
            'follow_up_request_by' => $this->follow_up_request_by,
            'follow_up_reason' => $this->follow_up_reason,
            'follow_up_requested_at' => $this->follow_up_requested_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_completed' => $this->isCompleted(),
            'is_follow_up_requested' => $this->isFollowUpRequested(),
            'follow_up_requested_by' => $this->followUpRequestedBy ? $this->followUpRequestedBy->_tag : null,

        ];
    }
}
