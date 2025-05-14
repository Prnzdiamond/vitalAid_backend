<?php

namespace App\Http\Resources\VitalAid;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        // Base user data shared across all roles
        $data = [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            '_tag' => $this->_tag,
            'phone_number' => $this->phone_number,
            'role' => $this->role,
            'name' => $this->name, // Using the consistent name accessor
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Add role-specific fields based on user role
        switch ($this->role) {
            case 'community':
                return array_merge($data, $this->communityData());
            case 'charity':
                return array_merge($data, $this->charityData());
            case 'health_expert':
                return array_merge($data, $this->healthExpertData());
            default:
                return $data;
        }
    }

    /**
     * Get community-specific data for the resource.
     *
     * @return array
     */
    protected function communityData(): array
    {
        return [
            'description' => $this->description,
            'location' => $this->location,
            'type' => $this->type,
            'visibility' => $this->visibility,
            'logo' => $this->logo,
            'banner' => $this->banner,
            'website' => $this->website,
            'social_links' => $this->social_links,
            'members_count' => $this->when(
                $this->relationLoaded('communityMembers'),
                fn() => $this->communityMembers->count(),
                fn() => $this->members_count ?? 0
            ),
            'is_member' => $this->when(isset($this->is_member), $this->is_member, false),
            'member_role' => $this->when(isset($this->member_role), $this->member_role),
            'joined_at' => $this->when(isset($this->joined_at), $this->joined_at),

            // Only include members if they have been loaded
            'members' => $this->when(
                $this->relationLoaded('communityMembers'),
                function () {
                    return $this->communityMembers->map(function ($member) {
                        return [
                            'user_id' => $member->user_id,
                            'role' => $member->role,
                            'joined_at' => $member->joined_at,
                            'status' => $member->status,
                            'user' => [
                                'id' => $member->user->id ?? null,
                                'first_name' => $member->user->first_name ?? null,
                                'last_name' => $member->user->last_name ?? null,
                                '_tag' => $member->user->_tag ?? null,
                                'role' => $member->user->role ?? null,
                                'logo' => $member->user->logo ?? null, // Profile picture
                            ]
                        ];
                    });
                }
            ),

            // Only include active members if they have been loaded
            'active_members' => $this->when(
                $this->relationLoaded('activeCommunityMembers'),
                function () {
                    return $this->activeCommunityMembers->map(function ($member) {
                        return [
                            'user_id' => $member->user_id,
                            'role' => $member->role,
                            'joined_at' => $member->joined_at,
                            'user' => [
                                'id' => $member->user->id ?? null,
                                'first_name' => $member->user->first_name ?? null,
                                'last_name' => $member->user->last_name ?? null,
                                '_tag' => $member->user->_tag ?? null,
                                'role' => $member->user->role ?? null,
                                'logo' => $member->user->logo ?? null, // Profile picture
                            ]
                        ];
                    });
                }
            ),

            // Events information - Include with fallbacks to zero if not set
            'events_hosted_count' => $this->events_hosted_count ?? 0,
            'upcoming_events_count' => $this->upcoming_events_count ?? 0,
            'upcoming_events' => $this->when(isset($this->upcoming_events), $this->upcoming_events, []),
        ];
    }

    /**
     * Get charity-specific data for the resource.
     *
     * @return array
     */
    protected function charityData(): array
    {
        return [
            'description' => $this->description,
            'location' => $this->location,
            'type' => $this->type,
            'visibility' => $this->visibility,
            'logo' => $this->logo,
            'banner' => $this->banner,
            'website' => $this->website,
            'social_links' => $this->social_links,
            'registration_number' => $this->registration_number,
            'founding_date' => $this->founding_date,
            'mission_statement' => $this->mission_statement,
            'target_audience' => $this->target_audience,
            'donation_requests_count' => $this->donation_requests_count ?? 0,
            'total_amount_raised' => $this->total_amount_raised ?? 0,
        ];
    }

    /**
     * Get health expert-specific data for the resource.
     *
     * @return array
     */
    protected function healthExpertData(): array
    {
        return [
            'description' => $this->description,
            'location' => $this->location,
            'specialization' => $this->specialization,
            'qualifications' => $this->qualifications,
            'available_hours' => $this->available_hours,
            'experience_years' => $this->experience_years,
            'logo' => $this->logo,         // Profile picture
            'banner' => $this->banner,     // Cover photo
            'website' => $this->website,
            'social_links' => $this->social_links,
            'average_rating' => $this->average_rating ?? 0,
            'consultations_count' => $this->consultations_handled_count ?? 0,
        ];
    }
}