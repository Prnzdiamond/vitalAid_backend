<?php

namespace App\Models\VitalAid;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;

class EventParticipant extends Model
{

    protected $collection = 'event_participants'; // MongoDB collection
    protected $fillable = ['event_id', 'user_id', 'status'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}