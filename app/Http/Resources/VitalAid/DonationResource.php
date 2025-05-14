<?php

namespace App\Http\Resources\VitalAid;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
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
            'user_id' => $this->is_anonymous ? '' : $this->user_id,
            'donation_request_id' => $this->donation_request_id,
            'amount' => $this->amount,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'paystack_reference' => $this->paystack_reference,
            'is_anonymous' => $this->is_anonymous,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_tag' => $this->is_anonymous ? '' : $this->user->_tag,

        ];
    }
}
