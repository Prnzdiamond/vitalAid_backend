<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\VitalAid\Event;
use App\Models\VitalAid\Donation;
use App\Models\VitalAid\DonationRequest;
use App\Models\VitalAid\Consultation;
use App\Models\VitalAid\CommunityMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = $this->getDashboardStats();
        $recentActivities = $this->getRecentActivities();

        return view('admin.dashboard', compact('stats', 'recentActivities'));
    }

    private function getDashboardStats()
    {
        $totalUsers = User::count();
        $verifiedUsers = User::verified()->count();
        $pendingVerifications = User::pendingVerification()->count();

        $totalEvents = Event::count();
        $upcomingEvents = Event::where('start_time', '>', now())->count();
        $activeEvents = Event::where('status', 'active')->count();

        $totalDonations = Donation::count();
        $totalDonationAmount = Donation::where('payment_status', 'successful')->sum('amount');
        $activeDonationRequests = DonationRequest::where('status', 'active')->count();

        $totalConsultations = Consultation::count();
        $activeConsultations = Consultation::active()->count();
        $completedConsultations = Consultation::completed()->count();

        $totalCommunities = User::where('role', 'community')->count();
        $totalCommunityMembers = CommunityMember::where('status', 'active')->count();

        return [
            'users' => [
                'total' => $totalUsers,
                'verified' => $verifiedUsers,
                'pending_verification' => $pendingVerifications,
                'verification_rate' => $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 1) : 0
            ],
            'events' => [
                'total' => $totalEvents,
                'upcoming' => $upcomingEvents,
                'active' => $activeEvents
            ],
            'donations' => [
                'total_count' => $totalDonations,
                'total_amount' => $totalDonationAmount,
                'active_requests' => $activeDonationRequests
            ],
            'consultations' => [
                'total' => $totalConsultations,
                'active' => $activeConsultations,
                'completed' => $completedConsultations
            ],
            'communities' => [
                'total' => $totalCommunities,
                'members' => $totalCommunityMembers
            ]
        ];
    }

    private function getRecentActivities()
    {
        $recentUsers = User::latest()->take(5)->get();
        $recentEvents = Event::latest()->take(5)->get();
        $recentDonations = Donation::with('user', 'donationRequest')->latest()->take(5)->get();
        $recentConsultations = Consultation::with('user', 'doctor')->latest()->take(5)->get();

        return [
            'users' => $recentUsers,
            'events' => $recentEvents,
            'donations' => $recentDonations,
            'consultations' => $recentConsultations
        ];
    }

    public function getMonthlyStats()
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('M Y');

            // Create proper date boundaries for MongoDB
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $userCount = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            $eventCount = Event::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            $donationAmount = Donation::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where('payment_status', 'success')
                ->sum('amount');

            $months->push([
                'month' => $month,
                'users' => $userCount,
                'events' => $eventCount,
                'donations' => $donationAmount
            ]);
        }

        return response()->json($months);
    }
}
