<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\VitalAid\Event;
use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Foundation\Auth\User as Authenticatable;
use MongoDB\Laravel\Eloquent\Model;
use App\Models\VitalAid\EventParticipant;
use App\Overrides\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $connection = "mongodb";
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        '_tag',
        'phone_number',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function isRole($role)
    {
        return $this->role === $role;
    }

    public function joinedEvents()
    {
        return $this->hasMany(EventParticipant::class, 'user_id');
    }

    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'event_manager');
    }

}
