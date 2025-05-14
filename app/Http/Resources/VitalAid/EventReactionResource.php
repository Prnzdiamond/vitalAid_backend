<?php

namespace App\Http\Resources\VitalAid;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class EventReactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = User::find($this->user_id);

        return [
            'id' => $this->_id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'user_name' => $user ? $user->_tag : 'Unknown User',
            'reaction_type' => $this->reaction_type,
            'like_count' => $this->countLikes($this->event_id),
            'dislike_count' => $this->countDislikes($this->event_id),
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
