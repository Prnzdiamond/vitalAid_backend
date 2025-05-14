<?php

namespace App\Http\Resources\VitalAid;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationRequestResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'amount_needed' => $this->amount_needed,
            'org_id' => $this->org_id,
            'amount_received' => $this->amount_received,
            'withdrawn_amount' => $this->withdrawn_amount,
            'status' => $this->status,
            'banner_url' => $this->banner_url,
            'other_images' => $this->other_images,
            'category' => $this->category,
            'is_urgent' => $this->is_urgent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->owner->_tag,
            'donations_count' => $this->donations->count(),
            'donations' => DonationResource::collection($this->donations),
        ];
    }
}