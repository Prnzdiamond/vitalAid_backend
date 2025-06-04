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
        'approved_at',
        'approved_by',
        'admin_notes',
        'manual_transfer_completed',
        'completed_at',
        'rejection_reason',
        'rejected_at',
        'rejected_by'
    ];

    protected $casts = [
        'amount' => 'float',
        'bank_details' => 'array',
        'manual_transfer_completed' => 'boolean',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'approved_by' => 'integer',
        'rejected_by' => 'integer',
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

    // Relationship to get the admin who approved the withdrawal
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Relationship to get the admin who rejected the withdrawal
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
