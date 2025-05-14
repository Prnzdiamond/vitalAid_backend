<?php

namespace App\Models\VitalAid;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityMember extends Model
{
    use HasFactory;

    protected $connection = 'mongodb'; // Use MongoDB connection
    protected $collection = 'community_members'; // Collection name

    protected $fillable = [
        'community_id',  // The community organization
        'user_id',       // The user who is a member
        'role',          // Role in the community (e.g., 'member', 'moderator', 'admin')
        'joined_at',     // When they joined
        'status',        // Status (e.g., 'active', 'inactive', 'banned')
    ];

    protected $dates = [
        'joined_at',
    ];

    public function community()
    {
        return $this->belongsTo(User::class, 'community_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all members of a community
     *
     * @param string $communityId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCommunityMembers(string $communityId)
    {
        return self::where('community_id', $communityId)
            ->where('status', 'active')
            ->with('user')
            ->get();
    }

    /**
     * Count active members of a community
     *
     * @param string $communityId
     * @return int
     */
    public static function countCommunityMembers(string $communityId): int
    {
        return self::where('community_id', $communityId)
            ->where('status', 'active')
            ->count();
    }
}
