<?php

namespace App\Http\Resources\VitalAid;

use Illuminate\Http\Request;
use App\Http\Resources\VitalAid\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\VitalAid\DonationRequestResource;

class WithdrawalRequestResource extends JsonResource
{
    /**
     * Transform the withdrawal request into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'donation_request_id' => $this->donation_request_id,
            'org_id' => $this->org_id,
            'amount' => $this->amount,
            'bank_details' => [
                'account_number' => isset($this->bank_details['account_number']) ?
                    $this->maskAccountNumber($this->bank_details['account_number']) : null,
                'bank_code' => $this->bank_details['bank_code'] ?? null,
                'account_name' => $this->bank_details['account_name'] ?? null,
            ],
            'status' => $this->status,
            'payout_reference' => $this->payout_reference,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Include related resources when they're loaded
            'donation_request' => $this->whenLoaded('donationRequest', function () {
                return new DonationRequestResource($this->donationRequest);
            }),
            'organization' => $this->whenLoaded('organization', function () {
                return new UserResource($this->organization);
            }),
        ];
    }

    /**
     * Mask account number for security
     *
     * @param string $accountNumber
     * @return string
     */
    private function maskAccountNumber(string $accountNumber): string
    {
        $length = strlen($accountNumber);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        $visiblePart = substr($accountNumber, -4);
        $maskedPart = str_repeat('*', $length - 4);

        return $maskedPart . $visiblePart;
    }
}
