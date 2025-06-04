<?php

namespace App\Http\Controllers\VitalAid;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\VitalAid\Community;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\VitalAid\CommunityMember;
use Illuminate\Support\Facades\Validator;
use App\Notifications\GeneralNotification;
use App\Http\Resources\VitalAid\UserResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CommunityController extends Controller
{
    // Common response formats
    private function successResponse($data = [], $message = 'Success', $code = 200)
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    private function errorResponse($message = 'Error', $code = 500)
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }

    // Auth check helper
    private function checkAuth(Request $request)
    {
        if (!$user = $request->user()) {
            return $this->errorResponse('Unauthorized.', 401);
        }
        return $user;
    }


    public function listCommunities(Request $request)
    {
        try {
            $user = $request->user() ?? null;

            // Start with a base query for communities
            $query = User::where('role', 'community');

            // Apply search filters if provided
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('_tag', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            // Filter by type if provided
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Sort by location proximity if user location is provided
            $userLocation = $request->user_location;
            if ($userLocation) {
                // This is a simple sorting approach - in a real app, you'd use geolocation
                $query->orderByRaw("CASE WHEN location LIKE '%$userLocation%' THEN 0 ELSE 1 END");
            }

            // Paginate results
            $perPage = $request->per_page ?? 10;
            $communities = $query->paginate($perPage);

            // Get member counts for all communities
            // Modified for MongoDB compatibility
            $communityIds = $communities->pluck('_id')->toArray();

            // Get counts using MongoDB's aggregation pipeline instead of SQL-style groupBy
            $memberCounts = [];
            $counts = CommunityMember::raw(function ($collection) use ($communityIds) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'community_id' => ['$in' => $communityIds],
                            'status' => 'active'
                        ]
                    ],
                    [
                        '$group' => [
                            '_id' => '$community_id',
                            'count' => ['$sum' => 1]
                        ]
                    ]
                ]);
            });

            // Format the results into a community_id => count array
            foreach ($counts as $count) {
                $memberCounts[$count->_id] = $count->count;
            }

            // Add member count to each community
            $communities->getCollection()->transform(function ($community) use ($memberCounts, $user) {
                $community->members_count = $memberCounts[$community->_id] ?? 0;

                // Check if logged-in user is a member
                if ($user) {
                    $community->is_member = CommunityMember::where('community_id', $community->_id)
                        ->where('user_id', $user->id)
                        ->where('status', 'active')
                        ->exists();
                } else {
                    $community->is_member = false;
                }

                return $community;
            });

            return $this->successResponse([
                'communities' => UserResource::collection($communities->items()),
                'pagination' => [
                    'total' => $communities->total(),
                    'per_page' => $communities->perPage(),
                    'current_page' => $communities->currentPage(),
                    'last_page' => $communities->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to list communities: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve communities: ' . $e->getMessage());
        }
    }

    public function getCommunity(Request $request, $communityId)
    {
        try {
            $user = $request->user() ?? null;
            // Find the community
            $community = User::where('_id', $communityId)
                ->where('role', 'community')
                ->firstOrFail();

            // Get active member count
            $memberCount = CommunityMember::where('community_id', $communityId)
                ->where('status', 'active')
                ->count();

            $community->members_count = $memberCount;

            // Check if logged-in user is a member
            if ($user) {
                $community->is_member = CommunityMember::where('community_id', $communityId)
                    ->where('user_id', $user->id)
                    ->where('status', 'active')
                    ->exists();
            } else {
                $community->is_member = false;
            }

            // Add events data
            $community->events_hosted_count = $community->createdEvents()->count();
            $community->upcoming_events_count = $community->createdEvents()
                ->where('start_time', '>', now())
                ->count();

            // Get recent members (limited to 5)
            $recentMembers = CommunityMember::where('community_id', $communityId)
                ->where('status', 'active')
                ->with('user')
                ->orderBy('joined_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->user->_id,
                        'name' => $member->user->first_name . ' ' . $member->user->last_name,
                        'tag' => $member->user->_tag,
                        'joined_at' => $member->joined_at
                    ];
                });

            // Get upcoming events (limited to 3)
            $upcomingEvents = $community->createdEvents()
                ->where('start_time', '>', now())
                ->orderBy('start_time', 'asc')
                ->limit(3)
                ->get();

            $community->upcoming_events = $upcomingEvents;

            Log::info($recentMembers);
            $community = new UserResource($community);
            Log::info($community->toArray($request));

            return $this->successResponse([
                'community' => $community,
                'recent_members' => $recentMembers
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Community not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to get community: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve community details.');
        }
    }

    public function myCommunitiesList(Request $request)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }

        try {
            // Get all communities where the user is an active member
            $membershipQuery = CommunityMember::where('user_id', $user->id)
                ->where('status', 'active')
                ->with('community')
                ->get();

            // Extract the communities from the memberships
            $communities = $membershipQuery->map(function ($membership) use ($request) {
                $community = $membership->community;

                // Add role in community
                $community->member_role = $membership->role;
                $community->joined_at = $membership->joined_at;

                // Add event counts
                $community->events_hosted_count = $community->createdEvents()->count();
                $community->upcoming_events_count = $community->createdEvents()
                    ->where('start_time', '>', now())
                    ->count();

                $community = new UserResource($community);
                Log::info($community->toArray($request));
                return $community;
            });



            return $this->successResponse([
                'communities' => UserResource::collection($communities),
                'count' => $communities->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get user communities: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve your communities.');
        }
    }


    public function joinCommunity(Request $request, $communityId)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }

        try {
            // Check if the community exists and is actually a community
            $community = User::where('_id', $communityId)
                ->where('role', 'community')
                ->firstOrFail();

            // Check if user is already a member
            $existingMembership = CommunityMember::where('community_id', $communityId)
                ->where('user_id', $user->id)
                ->first();

            if ($existingMembership) {
                if ($existingMembership->status === 'active') {
                    return $this->errorResponse('You are already a member of this community.', 409);
                } else if ($existingMembership->status === 'inactive') {
                    // Reactivate membership
                    $existingMembership->update([
                        'status' => 'active',
                        'joined_at' => now()
                    ]);

                    return $this->successResponse([], 'Rejoined community successfully.');
                }
            }

            // Create new membership
            CommunityMember::create([
                'community_id' => $communityId,
                'user_id' => $user->id,
                'role' => 'member',
                'joined_at' => now(),
                'status' => 'active',
            ]);

            // Notify the community admin
            $notificationData = [
                'title' => 'New Community Member',
                'body' => "{$user->first_name} {$user->last_name} has joined your community.",
                'type' => 'community_join',
                'extra' => [
                    'user_id' => $user->id,
                    'community_id' => $communityId
                ]
            ];

            // Send notification to community admin (the community user itself)
            $community->notify(new GeneralNotification($notificationData));

            return $this->successResponse([], 'Joined community successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Community not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to join community: ' . $e->getMessage());
            return $this->errorResponse('Failed to join community.');
        }
    }

    public function leaveCommunity(Request $request, $communityId)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }

        try {
            // Check if the community exists and is actually a community
            $community = User::where('_id', $communityId)
                ->where('role', 'community')
                ->firstOrFail();

            // Check if user is a member of the community
            $membership = CommunityMember::where('community_id', $communityId)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$membership) {
                return $this->errorResponse('You are not an active member of this community.', 404);
            }

            // Update membership status to inactive
            $membership->update([
                'status' => 'inactive',
                'left_at' => now()
            ]);

            // Notify the community admin
            $notificationData = [
                'title' => 'Member Left Community',
                'body' => "{$user->first_name} {$user->last_name} has left your community.",
                'type' => 'community_leave',
                'extra' => [
                    'user_id' => $user->id,
                    'community_id' => $communityId
                ]
            ];

            // Send notification to community admin (the community user itself)
            $community->notify(new GeneralNotification($notificationData));

            return $this->successResponse([], 'Left community successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Community not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to leave community: ' . $e->getMessage());
            return $this->errorResponse('Failed to leave community.');
        }
    }

    public function getCommunityMembers(Request $request, $communityId)
    {
        try {
            // Check if the community exists
            $community = User::where('_id', $communityId)
                ->where('role', 'community')
                ->firstOrFail();

            // Get active members with pagination
            $perPage = $request->per_page ?? 20;
            $members = CommunityMember::where('community_id', $communityId)
                ->where('status', 'active')
                ->with('user')
                ->orderBy('joined_at', 'desc')
                ->paginate($perPage);

            // Transform the data with UserResource
            $membersData = $members->getCollection()->map(function ($member) {
                return [
                    'member_role' => $member->role,
                    'joined_at' => $member->joined_at,
                    'user' => new UserResource($member->user)
                ];
            });

            // Replace the items in the paginator with our transformed data
            $members->setCollection($membersData);

            return $this->successResponse([
                'members' => $members->items(),
                'pagination' => [
                    'total' => $members->total(),
                    'per_page' => $members->perPage(),
                    'current_page' => $members->currentPage(),
                    'last_page' => $members->lastPage(),
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Community not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to get community members: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve community members.');
        }
    }

    public function notifyCommunityMembers(Request $request)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }

        // Validate the user is a community
        if ($user->role !== 'community') {
            return $this->errorResponse('Only community accounts can send community notifications.', 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get all active community members
            $members = CommunityMember::where('community_id', $user->id)
                ->where('status', 'active')
                ->with('user')
                ->get();

            if ($members->isEmpty()) {
                return $this->errorResponse('No active members found in your community.', 404);
            }

            $notificationData = [
                'title' => $request->title,
                'body' => $request->body,
                'type' => $request->type ?? 'community_announcement',
                'extra' => [
                    'community_id' => $user->id,
                    'community_name' => $user->first_name . ' ' . $user->last_name
                ]
            ];

            // Send notification to each member
            foreach ($members as $member) {
                // Skip self-notification (admin)

                    $member->user->notify(new GeneralNotification($notificationData));
            }

            return $this->successResponse([
                'members_notified' => $members->count() // Exclude self
            ], 'Notifications sent to all community members.');
        } catch (\Exception $e) {
            Log::error('Failed to send community notifications: ' . $e->getMessage());
            return $this->errorResponse('Failed to send notifications.');
        }
    }
}