<?php

namespace App\Models\VitalAid;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;


class Donation extends Model
{
    protected $collection = 'donations'; // MongoDB collection
    protected $fillable = [
        'user_id',
        'donation_request_id',
        'amount',
        'payment_status',
        'is_anonymous',
        'status', //(e.g., pending, successful, failed)
        'paystack_reference'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function donationRequest()
    {
        return $this->belongsTo(DonationRequest::class, 'donation_request_id');
    }

}
