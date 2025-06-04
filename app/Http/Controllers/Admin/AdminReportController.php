<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VitalAid\Event;
use App\Models\VitalAid\Donation;
use App\Models\VitalAid\DonationRequest;
use App\Models\VitalAid\Consultation;
use App\Models\VitalAid\CommunityMember;
use App\Models\VitalAid\EventParticipant;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    public function index()
    {
        // Platform overview statistics
        $totalUsers = User::count();
        $totalEvents = Event::count();
        $totalDonations = Donation::where('status', 'success')->sum('amount');
        $totalConsultations = Consultation::count();
        $totalCommunities = User::where('role', 'community')->count();

        // Growth metrics (last 30 days)
        $newUsersLastMonth = User::where('created_at', '>=', now()->subDays(30))->count();
        $newEventsLastMonth = Event::where('created_at', '>=', now()->subDays(30))->count();
        $donationsLastMonth = Donation::where('created_at', '>=', now()->subDays(30))
            ->where('status', 'success')
            ->sum('amount');

        return view('admin.reports.index', compact(
            'totalUsers',
            'totalEvents',
            'totalDonations',
            'totalConsultations',
            'totalCommunities',
            'newUsersLastMonth',
            'newEventsLastMonth',
            'donationsLastMonth'
        ));
    }

    public function userGrowth(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $year = $request->get('year', now()->year);

        // Get start and end dates for the year
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();

        $users = User::whereBetween('created_at', [$startDate, $endDate])->get();

        $data = collect();

        if ($period === 'monthly') {
            // Group by month
            $groupedUsers = $users->groupBy(function ($user) {
                return Carbon::parse($user->created_at)->month;
            });

            foreach ($groupedUsers as $month => $monthUsers) {
                $roleGroups = $monthUsers->groupBy('role');
                foreach ($roleGroups as $role => $roleUsers) {
                    $data->push([
                        'month' => $month,
                        'role' => $role,
                        'count' => $roleUsers->count()
                    ]);
                }
            }
            $data = $data->groupBy('month');
        } else {
            // Group by week
            $groupedUsers = $users->groupBy(function ($user) {
                return Carbon::parse($user->created_at)->week;
            });

            foreach ($groupedUsers as $week => $weekUsers) {
                $roleGroups = $weekUsers->groupBy('role');
                foreach ($roleGroups as $role => $roleUsers) {
                    $data->push([
                        'week' => $week,
                        'role' => $role,
                        'count' => $roleUsers->count()
                    ]);
                }
            }
            $data = $data->groupBy('week');
        }

        return view('admin.reports.user-growth', compact('data', 'period', 'year'));
    }

    public function platformUsage()
    {
        // Event participation metrics - last 30 days
        $recentEvents = Event::where('start_time', '>=', now()->subDays(30))->pluck('_id');
        $eventParticipation = EventParticipant::whereIn('event_id', $recentEvents)->count();

        // Consultation metrics
        $consultationMetrics = [
            'total' => Consultation::count(),
            'completed' => Consultation::where('status', 'completed')->count(),
            'active' => Consultation::where('status', 'in_progress')->count(),
            'average_rating' => $this->calculateAverageRating()
        ];

        // Community engagement
        $communityEngagement = CommunityMember::where('status', 'active')->count();

        // Daily active users (approximation based on recent activity)
        $dailyActiveUsers = User::where('updated_at', '>=', now()->subDays(1))->count();

        return view('admin.reports.platform-usage', compact(
            'eventParticipation',
            'consultationMetrics',
            'communityEngagement',
            'dailyActiveUsers'
        ));
    }

    public function financialSummary(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        // Build query conditions
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();

        if ($month) {
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        }

        $donations = Donation::where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalDonations = $donations->sum('amount');
        $donationCount = $donations->count();
        $averageDonation = $donationCount > 0 ? $totalDonations / $donationCount : 0;

        // Monthly breakdown for the year
        $yearDonations = Donation::where('status', 'success')
            ->whereBetween('created_at', [
                Carbon::create($year, 1, 1)->startOfDay(),
                Carbon::create($year, 12, 31)->endOfDay()
            ])
            ->get();

        $monthlyBreakdown = $yearDonations->groupBy(function ($donation) {
            return Carbon::parse($donation->created_at)->month;
        })->map(function ($monthDonations, $month) {
            return [
                'month' => $month,
                'total' => $monthDonations->sum('amount'),
                'count' => $monthDonations->count()
            ];
        })->values();

        // Top donation categories
        $donationRequests = DonationRequest::get();
        $successfulDonationRequestIds = Donation::where('status', 'success')
            ->pluck('donation_request_id')
            ->unique();

        $topCategories = $donationRequests->whereIn('_id', $successfulDonationRequestIds)
            ->groupBy('category')
            ->map(function ($categoryRequests, $category) {
                return [
                    'category' => $category,
                    'request_count' => $categoryRequests->count()
                ];
            })
            ->sortByDesc('request_count')
            ->values();

        return view('admin.reports.financial-summary', compact(
            'totalDonations',
            'donationCount',
            'averageDonation',
            'monthlyBreakdown',
            'topCategories',
            'year'
        ));
    }

    public function verificationStats()
    {
        $verificationStats = [
            'total_pending' => User::where('verification_status', 'pending')->count(),
            'total_approved' => User::where('verification_status', 'approved')->count(),
            'total_rejected' => User::where('verification_status', 'rejected')->count(),
            'health_experts_verified' => User::where('role', 'health_expert')->where('is_verified', true)->count(),
            'charities_verified' => User::where('role', 'charity')->where('is_verified', true)->count(),
            'communities_verified' => User::where('role', 'community')->where('is_verified', true)->count(),
        ];

        // Verification progress by role
        $users = User::whereIn('role', ['health_expert', 'charity', 'community'])
            ->whereNotNull('verification_status')
            ->get();

        $verificationByRole = $users->groupBy('role')->map(function ($roleUsers, $role) {
            return $roleUsers->groupBy('verification_status')->map(function ($statusUsers, $status) {
                return [
                    'verification_status' => $status,
                    'count' => $statusUsers->count()
                ];
            })->values();
        });

        return view('admin.reports.verification-stats', compact('verificationStats', 'verificationByRole'));
    }

    /**
     * Calculate average rating for consultations
     */
    private function calculateAverageRating()
    {
        $consultationsWithRating = Consultation::whereNotNull('rating')->get();

        if ($consultationsWithRating->isEmpty()) {
            return 0;
        }

        return $consultationsWithRating->avg('rating');
    }
}
