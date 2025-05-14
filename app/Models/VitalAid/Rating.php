<?php

namespace App\Models\VitalAid;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rating extends Model
{
    use HasFactory;

    protected $connection = 'mongodb'; // Use MongoDB connection
    protected $collection = 'ratings'; // Collection name

    protected $fillable = [
        'user_id',      // The user who gives the rating
        'doctor_id',    // The health expert being rated
        'consultation_id', // Associated consultation
        'rating',       // Numeric rating (e.g., 1-5)
        'comment',      // Optional comment
    ];

    protected $casts = [
        'rating' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id');
    }

    /**
     * Calculate average rating for a doctor
     *
     * @param string $doctorId
     * @return float
     */
    public static function getAverageRatingForDoctor(string $doctorId): float
    {
        return self::where('doctor_id', $doctorId)
            ->avg('rating') ?? 0;
    }
}
