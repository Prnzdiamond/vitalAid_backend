<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\VitalAid\CommunityMember;
use App\Models\VitalAid\EventParticipant;
use App\Models\VitalAid\Consultation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by verification status
        if ($request->filled('verification_status')) {
            if ($request->verification_status === 'verified') {
                $query->verified();
            } elseif ($request->verification_status === 'pending') {
                $query->pendingVerification();
            } elseif ($request->verification_status === 'needs_verification') {
                $query->needsVerification();
            }
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load([
            'joinedEvents.event',
            'createdEvents',
            'donation.donationRequest',
            'donationRequests',
            'consultationsRequested',
            'consultationsHandled'
        ]);

        $userStats = $this->getUserStats($user);
        $activityData = $this->getUserActivityData($user);

        return view('admin.users.show', compact('user', 'userStats', 'activityData'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone_number' => 'nullable|string|max:20',
            'role' => 'required|in:user,health_expert,charity,community,admin',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_verified' => 'boolean',
            'verification_status' => 'nullable|in:pending,approved,rejected'
        ]);

        $user->update($request->all());

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Check if user has important dependencies
        if (
            $user->consultationsHandled()->exists() ||
            $user->donationRequests()->exists() ||
            $user->createdEvents()->exists()
        ) {
            return redirect()->back()
                ->with('error', 'Cannot delete user with active consultations, donations, or events.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'is_verified' => !$user->is_verified,
            'verification_status' => $user->is_verified ? null : 'approved'
        ]);

        $status = $user->is_verified ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "User {$status} successfully.");
    }

    private function getUserStats(User $user)
    {
        $stats = [
            'consultations_requested' => $user->consultationsRequested()->count(),
            'consultations_handled' => $user->consultationsHandled()->count(),
            'donations_made' => $user->donation()->count(),
            'donation_requests' => $user->donationRequests()->count(),
            'events_joined' => $user->joinedEvents()->count(),
            'events_created' => $user->createdEvents()->count(),
        ];

        if ($user->role === 'community') {
            $stats['community_members'] = CommunityMember::where('community_id', $user->id)
                ->where('status', 'active')
                ->count();
        }

        if ($user->role === 'health_expert') {
            $stats['average_rating'] = $user->consultationsHandled()
                ->whereNotNull('rating')
                ->avg('rating') ?? 0;
        }

        return $stats;
    }

    private function getUserActivityData(User $user)
    {
        return [
            'recent_consultations' => $user->consultationsRequested()
                ->latest('last_message_at')
                ->take(5)
                ->get(),
            'recent_donations' => $user->donation()
                ->with('donationRequest')
                ->latest()
                ->take(5)
                ->get(),
            'recent_events' => $user->joinedEvents()
                ->with('event')
                ->latest()
                ->take(5)
                ->get()
        ];
    }

    public function export(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('verification_status')) {
            if ($request->verification_status === 'verified') {
                $query->verified();
            } elseif ($request->verification_status === 'pending') {
                $query->pendingVerification();
            }
        }

        $users = $query->get();

        // Export logic here (CSV, Excel, etc.)
        return response()->json(['message' => 'Export functionality to be implemented']);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:verify,unverify,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,_id'
        ]);

        $users = User::whereIn('_id', $request->user_ids);

        switch ($request->action) {
            case 'verify':
                $users->update([
                    'is_verified' => true,
                    'verification_status' => 'approved',
                    'verification_approved_at' => now()
                ]);
                $message = 'Users verified successfully.';
                break;

            case 'unverify':
                $users->update([
                    'is_verified' => false,
                    'verification_status' => null
                ]);
                $message = 'Users unverified successfully.';
                break;

            case 'delete':
                $users->delete();
                $message = 'Users deleted successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }
}
