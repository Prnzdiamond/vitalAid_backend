<?php

namespace App\Http\Resources\VitalAid;

use Illuminate\Http\Request;
use App\Models\VitalAid\EventReaction;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Only include full participants and reactions data if specifically requested
        // Otherwise just include counts to keep responses lightweight
        $includeDetails = $request->query('include_details', false);

        $data = [
            'id' => $this->_id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'banner_url' => $this->banner_url,
            'event_manager' => $this->event_manager,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'capacity' => $this->capacity,
            'contact_info' => $this->contact_info,
            'requires_verification' => $this->requires_verification,
            'provides_certificate' => $this->provides_certificate,
            'event_manager_tag' => $this->whenLoaded('eventManager', function () {
                return $this->eventManager->_tag ?? null;
            }),
            'category' => $this->category,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Always include counts
            'participants_count' => $this->whenCounted('eventParticipants', function () {
                return $this->eventParticipants_count;
            }, $this->eventParticipants()->count()),
            'reactions_summary' => [
                'likes' => EventReaction::countLikes($this->_id),
                'dislikes' => EventReaction::countDislikes($this->_id)
            ]
        ];

        // Only include full details when requested
        if ($includeDetails) {
            $data['participants'] = EventParticipantResource::collection(
                $this->whenLoaded('eventParticipants', function () {
                    return $this->eventParticipants;
                }, function () {
                    return $this->eventParticipants()->get();
                })
            );

            $data['reactions'] = EventReactionResource::collection(
                $this->whenLoaded('eventReactions', function () {
                    return $this->eventReactions;
                }, function () {
                    return $this->eventReactions()->get();
                })
            );
        }

        return $data;
    }
}
