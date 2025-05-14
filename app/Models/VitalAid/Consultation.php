<?php

namespace App\Models\VitalAid;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Consultation extends Model
{
    use HasFactory;

    protected $connection = 'mongodb'; // Use MongoDB connection
    protected $collection = 'consultations'; // Collection name

    protected $fillable = [
        'user_id',
        'doctor_id', // Nullable if AI is handling
        'messages', // Array of messages
        'handled_by',
        'status', // in_progress, requested, completed
        'memory',
        'last_message_at',
        'rating', // 1-5 star rating
        'rating_comment', // Optional comment with rating
        'follow_up_requested', // Boolean flag
        'follow_up_request_by', // user_id of who requested the follow-up
        'follow_up_reason', // Reason for follow-up
        'follow_up_requested_at' // Timestamp of follow-up request
    ];

    protected $dates = ['last_message_at', 'follow_up_requested_at'];

    protected $casts = [
        'messages' => 'array',
        'follow_up_requested' => 'boolean',
        'rating' => 'integer'
    ];

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFollowUpRequested()
    {
        return $this->follow_up_requested === true;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function followUpRequestedBy()
    {
        return $this->belongsTo(User::class, 'follow_up_request_by');
    }

    // Scope for retrieving completed consultations
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Scope for retrieving active consultations
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'completed');
    }

    // Scope for retrieving consultations with follow-up requests
    public function scopeWithFollowUpRequested($query)
    {
        return $query->where('follow_up_requested', true);
    }

    // Helper method to calculate average rating for a doctor
    public static function getAverageRatingForDoctor($doctorId)
    {
        $ratings = self::where('doctor_id', $doctorId)
            ->whereNotNull('rating')
            ->pluck('rating');

        if ($ratings->isEmpty()) {
            return 0;
        }

        return $ratings->avg();
    }
}
