<?php

namespace App\Models\Admin;

use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Eloquent\Model;
use App\Overrides\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $connection = "mongodb";
    protected $collection = "admins";

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'is_active',
        'last_login_at',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function isActive()
    {
        return $this->is_active === true;
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function canManage($resource)
    {
        // Define permissions based on role
        $permissions = [
            'super_admin' => ['*'], // All permissions
            'admin' => [
                'users',
                'events',
                'donations',
                'consultations',
                'communities',
                'verifications',
                'reports'
            ],
            'moderator' => ['events', 'communities', 'verifications'],
        ];

        return in_array('*', $permissions[$this->role] ?? []) ||
            in_array($resource, $permissions[$this->role] ?? []);
    }

    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }
}
