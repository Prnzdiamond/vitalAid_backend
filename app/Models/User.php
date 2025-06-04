<?php

namespace App\Models;

use Exception;
use App\Models\VitalAid\Event;
use App\Models\VitalAid\Donation;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\VitalAid\Consultation;
use Illuminate\Support\Facades\Storage;
use App\Models\VitalAid\CommunityMember;
use App\Models\VitalAid\DonationRequest;
use App\Models\VitalAid\EventParticipant;
use App\Overrides\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $connection = "mongodb";
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        '_tag',
        'phone_number',
        'role',
        'password',
        'description',
        'location',
        'type',
        'visibility',
        'logo',
        'banner',
        'website',
        'social_links',
        'specialization',
        'qualifications',
        'available_hours',
        'experience_years',
        'registration_number',
        'founding_date',
        'mission_statement',
        'target_audience',
        'is_verified',
        'verification_status',
        'verification_documents',
        'verification_submitted_at',
        'verification_approved_at',
        'verification_rejected_at',
        'verification_rejection_reason',
        'verified_by',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'social_links' => 'array',
            'available_hours' => 'array',
            'is_verified' => 'boolean',
            'verification_documents' => 'array',
            'verification_submitted_at' => 'datetime',
            'verification_approved_at' => 'datetime',
            'verification_rejected_at' => 'datetime',
        ];
    }

    protected $appends = ['name', 'verification_progress', 'required_documents', 'document_urls'];

    public function isRole($role)
    {
        return $this->role === $role;
    }
    public function isVerified()
    {
        return $this->is_verified === true && $this->verification_status === 'approved';
    }

    public function getDocumentUrlsAttribute()
    {
        if (!$this->verification_documents)
            return [];

        $documentUrls = [];
        foreach ($this->verification_documents as $docType => $filePath) {
            $documentUrls[$docType] = [
                'url' => asset('storage/' . $filePath),
                'filename' => basename($filePath),
                'uploaded_at' => $this->verification_submitted_at?->format('Y-m-d H:i:s')
            ];
        }
        return $documentUrls;
    }

    public function getDocumentUrl(string $documentType)
    {
        if (!$this->verification_documents || !isset($this->verification_documents[$documentType]))
            return null;

        $filePath = $this->verification_documents[$documentType];
        return [
            'url' => asset('storage/' . $filePath),
            'filename' => basename($filePath),
            'path' => $filePath
        ];
    }

    public function hasDocument(string $documentType)
    {
        return isset($this->verification_documents[$documentType]) &&
            !empty($this->verification_documents[$documentType]) &&
            Storage::disk('public')->exists($this->verification_documents[$documentType]);
    }

    public function getDocumentSize(string $documentType)
    {
        if (!$this->hasDocument($documentType))
            return null;

        try {
            return Storage::disk('public')->size($this->verification_documents[$documentType]);
        } catch (Exception $e) {
            return null;
        }
    }

    public function getDocumentInfo(string $documentType)
    {
        if (!$this->hasDocument($documentType))
            return null;

        $filePath = $this->verification_documents[$documentType];
        try {
            return [
                'url' => asset('storage/' . $filePath),
                'filename' => basename($filePath),
                'size' => Storage::disk('public')->size($filePath),
                'last_modified' => Storage::disk('public')->lastModified($filePath),
                'mime_type' => Storage::disk('public')->mimeType($filePath)
            ];
        } catch (Exception $e) {
            return ['url' => asset('storage/' . $filePath), 'filename' => basename($filePath), 'error' => 'Could not retrieve file metadata'];
        }
    }

    public function getVerificationProgressAttribute()
    {
        if (!in_array($this->role, ['health_expert', 'charity', 'community']))
            return 100;

        $requiredDocs = $this->getRequiredDocumentsAttribute();
        $uploadedDocs = $this->verification_documents ?? [];

        if (empty($requiredDocs))
            return 0;

        $uploadedCount = collect($requiredDocs)->keys()->filter(
            fn($docType) =>
            isset($uploadedDocs[$docType]) && !empty($uploadedDocs[$docType])
        )->count();

        return round(($uploadedCount / count($requiredDocs)) * 100);
    }

    public function getRequiredDocumentsAttribute()
    {
        return match ($this->role) {
            'health_expert' => [
                'government_id' => 'Government-issued ID',
                'professional_license' => 'Professional License/Certification',
                'education_proof' => 'Proof of Education (Degree/Diploma)',
                'employment_letter' => 'Employment/Practice Affiliation Letter',
                'registration_number' => 'Professional Registration Number',
            ],
            'charity' => [
                'representative_id' => 'Government-issued ID of Representative',
                'registration_certificate' => 'Certificate of Registration/Incorporation',
                'tax_identification' => 'Tax Identification Number/Certificate',
                'mission_statement' => 'Mission Statement/Constitution',
                'address_proof' => 'Official Address Proof',
                'trustees_list' => 'List of Trustees/Executives',
            ],
            'community' => [
                'representative_id' => 'Government-issued ID of Representative',
                'leadership_letter' => 'Community Leadership Letter/Endorsement',
                'group_constitution' => 'Group Constitution/Meeting Minutes',
                'group_evidence' => 'Group Photo/Event Evidence',
                'location_proof' => 'Location Proof',
            ],
            default => []
        };
    }

    public function getOptionalDocuments()
    {
        return match ($this->role) {
            'health_expert' => ['health_council_id' => 'Health Council ID Card', 'online_profile' => 'LinkedIn/Online Portfolio'],
            'charity' => ['website_proof' => 'Official Website/Social Media', 'partnership_letters' => 'Partnership Letters with Verified Bodies'],
            'community' => ['lga_endorsement' => 'LGA/NGO Endorsements', 'community_certificates' => 'Community Registry/Recognition Certificates'],
            default => []
        };
    }

    public function submitForVerification(array $documents)
    {
        $this->update([
            'verification_documents' => $documents,
            'verification_status' => 'pending',
            'verification_submitted_at' => now()
        ]);
    }

    public function approveVerification($verifiedBy = null)
    {
        $this->update([
            'is_verified' => true,
            'verification_status' => 'approved',
            'verification_approved_at' => now(),
            'verified_by' => $verifiedBy,
            'verification_rejection_reason' => null
        ]);
    }

    public function rejectVerification($reason, $rejectedBy = null)
    {
        $this->update([
            'is_verified' => false,
            'verification_status' => 'rejected',
            'verification_rejected_at' => now(),
            'verification_rejection_reason' => $reason,
            'verified_by' => $rejectedBy
        ]);
    }

    public function resetVerification()
    {
        $this->update([
            'is_verified' => false,
            'verification_status' => null,
            'verification_documents' => [],
            'verification_submitted_at' => null,
            'verification_approved_at' => null,
            'verification_rejected_at' => null,
            'verification_rejection_reason' => null,
            'verified_by' => null
        ]);
    }

    public function hasCompleteDocuments()
    {
        $requiredDocs = $this->getRequiredDocumentsAttribute();
        $uploadedDocs = $this->verification_documents ?? [];

        return collect($requiredDocs)->keys()->every(
            fn($docType) =>
            isset($uploadedDocs[$docType]) && !empty($uploadedDocs[$docType])
        );
    }

    public function getMissingDocuments()
    {
        $requiredDocs = $this->getRequiredDocumentsAttribute();
        $uploadedDocs = $this->verification_documents ?? [];

        return collect($requiredDocs)->filter(
            fn($docName, $docType) =>
            !isset($uploadedDocs[$docType]) || empty($uploadedDocs[$docType])
        )->toArray();
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true)->where('verification_status', 'approved');
    }
    public function scopeNeedsVerification($query)
    {
        return $query->whereIn('role', ['health_expert', 'charity', 'community'])
            ->where(fn($q) => $q->where('is_verified', false)->orWhereNull('is_verified')->orWhere('verification_status', '!=', 'approved'));
    }
    public function scopePendingVerification($query)
    {
        return $query->where('verification_status', 'pending');
    }

    // Relationships
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

    public function getNameAttribute()
    {
        return in_array($this->role, ['community', 'charity'])
            ? ($this->first_name ?: 'Organization')
            : "{$this->first_name} {$this->last_name}";
    }

    public function communityMembers()
    {
        return $this->role === 'community' ? $this->hasMany(CommunityMember::class, 'community_id') : null;
    }
    public function activeCommunityMembers()
    {
        return $this->role === 'community' ? $this->communityMembers()->where('status', 'active') : null;
    }

    public function getMembersCountAttribute()
    {
        return $this->role === 'community'
            ? CommunityMember::where('community_id', $this->id)->where('status', 'active')->count()
            : null;
    }

    public function getIsMemberAttribute()
    {
        if ($this->role !== 'community' || !($user = Auth::user()))
            return $this->role === 'community' ? false : null;

        return CommunityMember::where('community_id', $this->id)
            ->where('user_id', $user->id)->where('status', 'active')->exists();
    }

    public static function communities()
    {
        return self::where('role', 'community');
    }
    public static function orderByLocation($query, $userLocation)
    {
        return $userLocation ? $query->orderByRaw("CASE WHEN location LIKE '%$userLocation%' THEN 0 ELSE 1 END") : $query;
    }

    public function getDashboardData()
    {
        return match ($this->role) {
            'user' => $this->getUserDashboardData(),
            'health_expert' => $this->getHealthExpertDashboardData(),
            'charity' => $this->getCharityDashboardData(),
            'community' => $this->getCommunityDashboardData(),
            default => []
        };
    }

    private function getUserDashboardData()
    {
        $now = now();
        $consultationsRequested = $this->consultationsRequested()->with('doctor')->latest('last_message_at')->get();
        $donationsMade = $this->donation()->with('donationRequest')->latest()->get();

        $eventsAttended = $this->joinedEvents()->with(['event'])
            ->whereHas('event', fn($q) => $q->where('end_time', '<', $now))->get()
            ->map(fn($ep) => $ep->event);

        $upcomingEvents = $this->joinedEvents()->with(['event'])
            ->whereHas('event', fn($q) => $q->where('start_time', '>', $now))->get()
            ->map(fn($ep) => $ep->event)->sortBy('start_time');

        return [
            'consultations_requested' => $consultationsRequested,
            'donations_made' => $donationsMade,
            'events_attended' => $eventsAttended,
            'upcoming_events' => $upcomingEvents,
            'first_three_upcoming_events' => $upcomingEvents->take(3),
            'first_three_recent_consultations' => $consultationsRequested->take(3),
            'consultations_count' => $consultationsRequested->count(),
            'donations_count' => $donationsMade->count(),
            'events_attended_count' => $eventsAttended->count(),
            'upcoming_events_count' => $upcomingEvents->count(),
        ];
    }

    private function getHealthExpertDashboardData()
    {
        $now = now();
        $consultationsHandled = $this->consultationsHandled()->with('user')->latest('last_message_at')->get();
        $activeRequestedConsultations = Consultation::where('status', '!=', 'completed')->whereNull('doctor_id')->latest('last_message_at')->get();
        $averageRating = $this->consultationsHandled()->whereNotNull('rating')->avg('rating') ?? 0;
        $followUpRequests = $this->consultationsHandled()->where('follow_up_requested', true)->latest('follow_up_requested_at')->get();
        $donationsMade = $this->donation()->with('donationRequest')->latest()->get();

        $eventsAttended = $this->joinedEvents()->with(['event'])
            ->whereHas('event', fn($q) => $q->where('end_time', '<', $now))->get()
            ->map(fn($ep) => $ep->event);

        $upcomingEvents = $this->joinedEvents()->with(['event'])
            ->whereHas('event', fn($q) => $q->where('start_time', '>', $now))->get()
            ->map(fn($ep) => $ep->event)->sortBy('start_time');

        return [
            'consultations_handled' => $consultationsHandled,
            'active_requested_consultations' => $activeRequestedConsultations,
            'average_rating' => $averageRating,
            'follow_up_requests' => $followUpRequests,
            'donations_made' => $donationsMade,
            'events_attended' => $eventsAttended,
            'upcoming_events' => $upcomingEvents,
            'first_three_recent_consultations_accepted' => $consultationsHandled->take(3),
            'consultations_handled_count' => $consultationsHandled->count(),
            'active_consultations_count' => $activeRequestedConsultations->count(),
            'follow_up_requests_count' => $followUpRequests->count(),
            'donations_made_count' => $donationsMade->count(),
            'events_attended_count' => $eventsAttended->count(),
            'upcoming_events_count' => $upcomingEvents->count(),
        ];
    }

    private function getCharityDashboardData()
    {
        $now = now();
        $donationRequests = $this->donationRequests()->get();
        $donationsReceived = Donation::whereIn('donation_request_id', $donationRequests->pluck('_id'))->with(['user', 'donationRequest'])->get();
        $totalAmountRaised = $donationsReceived->sum('amount');
        $ongoingDonations = $donationRequests->where('status', 'active');

        $upcomingEvents = $this->joinedEvents()->with(['event'])
            ->whereHas('event', fn($q) => $q->where('start_time', '>', $now))->get()
            ->map(fn($ep) => $ep->event)->sortBy('start_time');

        return [
            'donations_received' => $donationsReceived,
            'total_amount_raised' => $totalAmountRaised,
            'ongoing_donations' => $ongoingDonations,
            'upcoming_events' => $upcomingEvents,
            'first_three_ongoing_donations' => $ongoingDonations->take(3),
            'first_three_upcoming_events' => $upcomingEvents->take(3),
            'donations_received_count' => $donationsReceived->count(),
            'donation_requests_count' => $donationRequests->count(),
            'ongoing_donations_count' => $ongoingDonations->count(),
            'upcoming_events_count' => $upcomingEvents->count(),
        ];
    }

    private function getCommunityDashboardData()
    {
        $now = now();
        $currentYear = $now->year;
        $eventsHosted = $this->createdEvents()->get();
        $eventsHostedThisYear = $eventsHosted->filter(fn($event) => $event->created_at->year === $currentYear);
        $upcomingEvents = $eventsHosted->filter(fn($event) => $event->start_time > $now)->sortBy('start_time');
        $members = CommunityMember::where('community_id', $this->id)->where('status', 'active')->with('user')->get();

        return [
            'members' => $members,
            'events_hosted_this_year' => $eventsHostedThisYear,
            'upcoming_events' => $upcomingEvents,
            'first_three_upcoming_events' => $upcomingEvents->take(3),
            'top_three_events_hosted' => $eventsHosted->take(3),
            'members_count' => $members->count(),
            'events_hosted_count' => $eventsHosted->count(),
            'events_hosted_this_year_count' => $eventsHostedThisYear->count(),
            'upcoming_events_count' => $upcomingEvents->count(),
        ];
    }
}