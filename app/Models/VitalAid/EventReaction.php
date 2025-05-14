<?php

namespace App\Models\VitalAid;

use App\Models\User;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventReaction extends Model
{
    use HasFactory;

    protected $connection = 'mongodb'; // Use MongoDB connection
    protected $collection = 'event_reactions'; // Collection name

    protected $fillable = [
        'event_id',
        'user_id',
        'reaction_type', // 'like' or 'dislike'
        'comment'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Count likes for an event
     *
     * @param string $eventId
     * @return int
     */
    public static function countLikes(string $eventId): int
    {
        return self::where('event_id', $eventId)
            ->where('reaction_type', 'like')
            ->count();
    }

    /**
     * Count dislikes for an event
     *
     * @param string $eventId
     * @return int
     */
    public static function countDislikes(string $eventId): int
    {
        return self::where('event_id', $eventId)
            ->where('reaction_type', 'dislike')
            ->count();
    }

    /**
     * Get top events by likes
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTopEvents(int $limit = 3)
    {
        // Get event IDs with their like counts
        $eventLikeCounts = self::where('reaction_type', 'like')
            ->groupBy('event_id')
            ->selectRaw('event_id, count(*) as like_count')
            ->orderBy('like_count', 'desc')
            ->limit($limit)
            ->pluck('event_id');

        // Fetch the actual events
        return Event::whereIn('_id', $eventLikeCounts)->get();
    }
}