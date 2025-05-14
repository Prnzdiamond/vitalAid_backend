<?php

namespace App\Models\VitalAid;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;


class DonationRequest extends Model
{
    protected $collection = 'donation_requests'; // MongoDB collection
    protected $fillable = [
        'org_id',
        'title',
        'description',
        'amount_needed',
        'amount_received',
        'withdrawn_amount',
        'status',
        'banner_url',
        'other_images',
        'category',
        'is_urgent',
    ];

    protected $casts = [
        'amount_needed' => 'double',
        'amount_received' => 'double',
        'is_urgent' => 'boolean',
        'other_images' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'org_id');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'donation_request_id');
    }
}
