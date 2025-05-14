<?php

namespace App\Models;

use App\Models\VitalAid\Event;
use App\Models\VitalAid\Donation;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Eloquent\Model;
use App\Models\VitalAid\Consultation;
use App\Models\VitalAid\DonationRequest;
use App\Models\VitalAid\EventParticipant;
use App\Models\VitalAid\CommunityMember;
use App\Overrides\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
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
        // Common fields for organizational roles (community, charity, health_expert)
        'description',
        'location',
        'type',
        'visibility',
        'logo',
        'banner',
        'website',
        'social_links',
        // Health expert specific fields
        'specialization',
        'qualifications',
        'available_hours',
        'experience_years',
        // Charity specific fields
        'registration_number',
        'founding_date',
        'mission_statement',
        'target_audience',
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
            'social_links' => 'array',
            'available_hours' => 'array',
        ];
    }

    /**
     * The attributes that should be appended to the model.
     *
     * @var array
     */
    protected $appends = [
        'name',
    ];

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

    public function donation()
    {
        return $this->hasMany(Donation::class, 'user_id');
    }

    public function donationRequests()
    {
        return $this->hasMany(DonationRequest::class, 'org_id');
    }

    public function consultationsRequested()
    {
        return $this->hasMany(Consultation::class, 'user_id');
    }

    public function consultationsHandled()
    {
        return $this->hasMany(Consultation::class, 'doctor_id');
    }

    /**
     * Get name representation based on role
     *
     * @return string
     */
    public function getNameAttribute()
    {
        // For organizational accounts, they might not have a proper first/last name
        if (in_array($this->role, ['community', 'charity'])) {
            return $this->first_name ?: 'Organization';
        }

        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get all members of the community (only applicable for community role)
     */
    public function communityMembers()
    {
        if ($this->role !== 'community') {
            return null;
        }

        return $this->hasMany(CommunityMember::class, 'community_id');
    }

    /**
     * Get active members of the community (only applicable for community role)
     */
    public function activeCommunityMembers()
    {
        if ($this->role !== 'community') {
            return null;
        }

        return $this->communityMembers()->where('status', 'active');
    }

    /**
     * Get the members count for the community (only applicable for community role)
     *
     * @return int|null
     */
    public function getMembersCountAttribute()
    {
        if ($this->role !== 'community') {
            return null;
        }

        return CommunityMember::where('community_id', $this->id)
            ->where('status', 'active')
            ->count();
    }

    /**
     * Check if the authenticated user is a member of this community
     * (only applicable for community role)
     *
     * @return bool|null
     */
    public function getIsMemberAttribute()
    {
        if ($this->role !== 'community') {
            return null;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $member = CommunityMember::where('community_id', $this->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        return $member !== null;
    }

    /**
     * Filter to only include communities
     */
    public static function communities()
    {
        return self::where('role', 'community');
    }

    /**
     * Order by location proximity (placeholder implementation)
     */
    public static function orderByLocation($query, $userLocation)
    {
        // This is a placeholder for actual geolocation sorting
        // In a real implementation, you would use coordinates and calculate distances
        if ($userLocation) {
            return $query->orderByRaw("CASE WHEN location LIKE '%$userLocation%' THEN 0 ELSE 1 END");
        }

        return $query;
    }

    public function getDashboardData()
    {
        switch ($this->role) {
            case 'user':
                return $this->getUserDashboardData();
            case 'health_expert':
                return $this->getHealthExpertDashboardData();
            case 'charity':
                return $this->getCharityDashboardData();
            case 'community':
                return $this->getCommunityDashboardData();
            default:
                return [];
        }
    }

    /**
     * Get dashboard data for regular users
     *
     * @return array
     */
    private function getUserDashboardData()
    {
        // Get timestamp for upcoming events (current time and forward)
        $now = now();

        // Get consultations requested
        $consultationsRequested = $this->consultationsRequested()
            ->with('doctor')
            ->latest('last_message_at')
            ->get();

        // Get donations made
        $donationsMade = $this->donation()
            ->with('donationRequest')
            ->latest()
            ->get();

        // Get events attended
        $eventsAttended = $this->joinedEvents()
            ->with(['event'])
            ->whereHas('event', function ($query) use ($now) {
                $query->where('end_time', '<', $now);
            })
            ->get()
            ->map(function ($eventParticipant) {
                return $eventParticipant->event;
            });

        // Get upcoming events
        $upcomingEvents = $this->joinedEvents()
            ->with(['event'])
            ->whereHas('event', function ($query) use ($now) {
                $query->where('start_time', '>', $now);
            })
            ->get()
            ->map(function ($eventParticipant) {
                return $eventParticipant->event;
            })
            ->sortBy('start_time');

        // Get first three upcoming events
        $firstThreeUpcomingEvents = $upcomingEvents->take(3);

        // Get first three most recent consultations
        $firstThreeRecentConsultations = $consultationsRequested->take(3);

        return [
            'consultations_requested' => $consultationsRequested,
            'donations_made' => $donationsMade,
            'events_attended' => $eventsAttended,
            'upcoming_events' => $upcomingEvents,
            'first_three_upcoming_events' => $firstThreeUpcomingEvents,
            'first_three_recent_consultations' => $firstThreeRecentConsultations,
            'consultations_count' => $consultationsRequested->count(),
            'donations_count' => $donationsMade->count(),
            'events_attended_count' => $eventsAttended->count(),
            'upcoming_events_count' => $upcomingEvents->count(),
        ];
    }

    /**
     * Get dashboard data for health experts
     *
     * @return array
     */
    private function getHealthExpertDashboardData()
    {
        // Get timestamp for upcoming events (current time and forward)
        $now = now();

        // Get consultations handled
        $consultationsHandled = $this->consultationsHandled()
            ->with('user')
            ->latest('last_message_at')
            ->get();

        // Get active requested consultations (not handled yet)
        $activeRequestedConsultations = Consultation::where('status', '!=', 'completed')
            ->whereNull('doctor_id')
            ->latest('last_message_at')
            ->get();

        // Calculate average rating from all rated consultations
        $averageRating = $this->consultationsHandled()
            ->whereNotNull('rating')
            ->avg('rating') ?? 0;

        // Get consultations with follow-up requests
        $followUpRequests = $this->consultationsHandled()
            ->where('follow_up_requested', true)
            ->latest('follow_up_requested_at')
            ->get();

        // Get donations made
        $donationsMade = $this->donation()
            ->with('donationRequest')
            ->latest()
            ->get();

        // Get events attended
        $eventsAttended = $this->joinedEvents()
            ->with(['event'])
            ->whereHas('event', function ($query) use ($now) {
                $query->where('end_time', '<', $now);
            })
            ->get()
            ->map(function ($eventParticipant) {
                return $eventParticipant->event;
            });

        // Get upcoming events
        $upcomingEvents = $this->joinedEvents()
            ->with(['event'])
            ->whereHas('event', function ($query) use ($now) {
                $query->where('start_time', '>', $now);
            })
            ->get()
            ->map(function ($eventParticipant) {
                return $eventParticipant->event;
            })
            ->sortBy('start_time');

        // Get first three most recent consultations accepted
        $firstThreeRecentConsultationsAccepted = $consultationsHandled->take(3);

        return [
            'consultations_handled' => $consultationsHandled,
            'active_requested_consultations' => $activeRequestedConsultations,
            'average_rating' => $averageRating,
            'follow_up_requests' => $followUpRequests,
            'donations_made' => $donationsMade,
            'events_attended' => $eventsAttended,
            'upcoming_events' => $upcomingEvents,
            'first_three_recent_consultations_accepted' => $firstThreeRecentConsultationsAccepted,
            'consultations_handled_count' => $consultationsHandled->count(),
            'active_consultations_count' => $activeRequestedConsultations->count(),
            'follow_up_requests_count' => $followUpRequests->count(),
            'donations_made_count' => $donationsMade->count(),
            'events_attended_count' => $eventsAttended->count(),
            'upcoming_events_count' => $upcomingEvents->count(),
        ];
    }

    /**
     * Get dashboard data for charities
     *
     * @return array
     */
    private function getCharityDashboardData()
    {
        // Get timestamp for upcoming events (current time and forward)
        $now = now();

        // Get donation requests created by this charity
        $donationRequests = $this->donationRequests()->get();

        // Get all donations received
        $donationsReceived = Donation::whereIn('donation_request_id', $donationRequests->pluck('_id'))
            ->with(['user', 'donationRequest'])
            ->get();

        // Calculate total amount raised from donations
        $totalAmountRaised = $donationsReceived->sum('amount');

        // Get ongoing donation requests (status is active/ongoing)
        $ongoingDonations = $donationRequests->where('status', 'active')->values();

        // Get upcoming events
        $upcomingEvents = $this->joinedEvents()
            ->with(['event'])
            ->whereHas('event', function ($query) use ($now) {
                $query->where('start_time', '>', $now);
            })
            ->get()
            ->map(function ($eventParticipant) {
                return $eventParticipant->event;
            })
            ->sortBy('start_time');

        // Get first three ongoing donation requests
        $firstThreeOngoingDonations = $ongoingDonations->take(3);

        // Get first three upcoming events
        $firstThreeUpcomingEvents = $upcomingEvents->take(3);

        return [
            'donations_received' => $donationsReceived,
            'total_amount_raised' => $totalAmountRaised,
            'ongoing_donations' => $ongoingDonations,
            'upcoming_events' => $upcomingEvents,
            'first_three_ongoing_donations' => $firstThreeOngoingDonations,
            'first_three_upcoming_events' => $firstThreeUpcomingEvents,
            'donations_received_count' => $donationsReceived->count(),
            'donation_requests_count' => $donationRequests->count(),
            'ongoing_donations_count' => $ongoingDonations->count(),
            'upcoming_events_count' => $upcomingEvents->count(),
        ];
    }

    /**
     * Get dashboard data for community organizations
     *
     * @return array
     */
    private function getCommunityDashboardData()
    {
        // Get timestamp for upcoming events and current year
        $now = now();
        $currentYear = $now->year;

        // Get events hosted by this community
        $eventsHosted = $this->createdEvents()->get();

        // Get events hosted this year
        $eventsHostedThisYear = $eventsHosted->filter(function ($event) use ($currentYear) {
            return $event->created_at->year === $currentYear;
        })->values();

        // Get upcoming events
        $upcomingEvents = $eventsHosted->filter(function ($event) use ($now) {
            return $event->start_time > $now;
        })->sortBy('start_time')->values();

        // Get first three upcoming events
        $firstThreeUpcomingEvents = $upcomingEvents->take(3);

        // Top three events hosted (placeholder - to be implemented with rating/like logic)
        $topThreeEventsHosted = $eventsHosted->take(3); // Placeholder until rating implementation

        // Get community members
        $members = CommunityMember::where('community_id', $this->id)
            ->where('status', 'active')
            ->with('user')
            ->get();

        return [
            'members' => $members,
            'events_hosted_this_year' => $eventsHostedThisYear,
            'upcoming_events' => $upcomingEvents,
            'first_three_upcoming_events' => $firstThreeUpcomingEvents,
            'top_three_events_hosted' => $topThreeEventsHosted,
            'members_count' => $members->count(),
            'events_hosted_count' => $eventsHosted->count(),
            'events_hosted_this_year_count' => $eventsHostedThisYear->count(),
            'upcoming_events_count' => $upcomingEvents->count(),
        ];
    }
}
