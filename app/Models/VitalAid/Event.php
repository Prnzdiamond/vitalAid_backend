<?php

namespace App\Models\VitalAid;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;



class Event extends Model
{
    protected $collection = 'events'; // MongoDB collection
    protected $fillable = [
        'title',
        'description',
        'location',
        'event_manager',
        'start_time',
        'end_time',
        'status',
        'category',
        'capacity',
        'contact_info',
        'requires_verification',
        'provides_certificate',
        'banner_url'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'requires_verification' => 'boolean',
        'provides_certificate' => 'boolean',
        'capacity' => 'integer',
    ];

    public function eventManager()
    {
        return $this->belongsTo(User::class, 'event_manager');
    }

    public function eventParticipants()
    {
        return $this->hasMany(EventParticipant::class, 'event_id');
    }

    public function eventReactions()
    {
        return $this->hasMany(EventReaction::class, 'event_id');
    }
}
