<?php

namespace App\Models\VitalAid;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $collection = 'withdrawal_requests'; // MongoDB collection name
    protected $connection = 'mongodb';

    protected $fillable = [
        'donation_request_id',
        'org_id',
        'amount',
        'bank_details',
        'status',
        'payout_reference',
    ];

    protected $casts = [
        'amount' => 'float',
        'bank_details' => 'array',
    ];

    // Optional: define relationships
    public function donationRequest()
    {
        return $this->belongsTo(DonationRequest::class, 'donation_request_id');
    }

    public function organization()
    {
        return $this->belongsTo(User::class, 'org_id');
    }
}
