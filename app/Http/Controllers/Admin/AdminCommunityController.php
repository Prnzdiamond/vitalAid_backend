<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VitalAid\CommunityMember;
use App\Models\VitalAid\Event;
use Illuminate\Http\Request;

class AdminCommunityController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'community');

        // Apply filters
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $communities = $query->latest()->paginate(15);

        // Add member counts to each community
        $communities->getCollection()->transform(function ($community) {
            $community->members_count = CommunityMember::countCommunityMembers($community->_id);
            $community->events_count = Event::where('event_manager', $community->_id)->count();
            return $community;
        });

        // Get statistics
        $totalCommunities = User::where('role', 'community')->count();
        $verifiedCommunities = User::where('role', 'community')->verified()->count();
        $pendingVerification = User::where('role', 'community')->where('verification_status', 'pending')->count();
        $totalMembers = CommunityMember::where('status', 'active')->count();

        return view('admin.communities.index', compact(
            'communities',
            'totalCommunities',
            'verifiedCommunities',
            'pendingVerification',
            'totalMembers'
        ));
    }

    public function show($id)
    {
        $community = User::where('role', 'community')->findOrFail($id);

        // Get community members
        $members = CommunityMember::getCommunityMembers($community->_id);

        // Get community events
        $events = Event::where('event_manager', $community->_id)
            ->latest()
            ->limit(10)
            ->get();

        // Get recent activity statistics
        $recentMembers = CommunityMember::where('community_id', $community->_id)
            ->where('status', 'active')
            ->where('joined_at', '>=', now()->subDays(30))
            ->count();

        $upcomingEvents = Event::where('event_manager', $community->_id)
            ->where('start_time', '>', now())
            ->count();

        return view('admin.communities.show', compact(
            'community',
            'members',
            'events',
            'recentMembers',
            'upcomingEvents'
        ));
    }

    public function members($communityId)
    {
        $community = User::where('role', 'community')->findOrFail($communityId);
        $members = CommunityMember::where('community_id', $communityId)
            ->with('user')
            ->latest('joined_at')
            ->paginate(20);

        return view('admin.communities.members', compact('community', 'members'));
    }

    public function analytics()
    {
        // Community growth over time
        $monthlyGrowth = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->where('role', 'community')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top communities by member count
        $topCommunities = User::where('role', 'community')
            ->get()
            ->map(function ($community) {
                $community->members_count = CommunityMember::countCommunityMembers($community->_id);
                return $community;
            })
            ->sortByDesc('members_count')
            ->take(10);

        // Community statistics by location
        $locationStats = User::where('role', 'community')
            ->selectRaw('location, COUNT(*) as count')
            ->whereNotNull('location')
            ->groupBy('location')
            ->orderByDesc('count')
            ->get();

        return view('admin.communities.analytics', compact(
            'monthlyGrowth',
            'topCommunities',
            'locationStats'
        ));
    }
}
