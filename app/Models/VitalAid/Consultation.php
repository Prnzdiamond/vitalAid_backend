<?php

namespace App\Models\VitalAid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

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
        'status' // in_progress, completed
    ];

    protected $casts = [
        'messages' => 'array',
    ];
}
