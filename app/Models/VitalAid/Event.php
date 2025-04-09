<?php

namespace App\Models\VitalAid;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;



class Event extends Model
{
    protected $collection = 'events'; // MongoDB collection
    protected $fillable = ['title', 'description', 'location', 'event_manager', 'start_time', 'end_time', 'status'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function eventManager()
    {
        return $this->belongsTo(User::class, 'event_manager');
    }

    public function eventParticipants()
    {
        return $this->hasMany(EventParticipant::class, 'event_id');
    }
}
