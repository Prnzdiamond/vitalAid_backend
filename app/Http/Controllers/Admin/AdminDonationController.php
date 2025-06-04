<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VitalAid\Donation;
use App\Models\VitalAid\DonationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDonationController extends Controller
{
    public function index(Request $request)
    {
        $query = Donation::with(['user', 'donationRequest.owner']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $donations = $query->latest()->paginate(20);

        // Get statistics
        $totalDonations = Donation::count();
        $totalAmount = Donation::where('status', 'success')->sum('amount');
        $pendingDonations = Donation::where('status', 'pending')->count();
        $failedDonations = Donation::where('status', 'failed')->count();

        return view('admin.donations.index', compact(
            'donations',
            'totalDonations',
            'totalAmount',
            'pendingDonations',
            'failedDonations'
        ));
    }

    public function show($id)
    {
        $donation = Donation::with(['user', 'donationRequest.owner'])->findOrFail($id);

        return view('admin.donations.show', compact('donation'));
    }

    public function analytics()
    {
        $totalAmount = Donation::where('status', 'success')->sum('amount');
        $totalDonations = Donation::count();
        $averageDonation = $totalDonations > 0 ? $totalAmount / $totalDonations : 0;

        // Monthly donation trends - MongoDB way
        $monthlyData = Donation::where('status', 'success')
            ->whereYear('created_at', now()->year)
            ->get()
            ->groupBy(function ($donation) {
                return $donation->created_at->month;
            })
            ->map(function ($donations, $month) {
                return (object) [
                    'month' => $month,
                    'count' => $donations->count(),
                    'total' => $donations->sum('amount')
                ];
            })
            ->sortBy('month')
            ->values();

        // Top donors - MongoDB way
        $topDonors = Donation::where('status', 'success')
            ->where('is_anonymous', false)
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function ($donations, $userId) {
                return (object) [
                    'user_id' => $userId,
                    'donation_count' => $donations->count(),
                    'total_donated' => $donations->sum('amount'),
                    'user' => $donations->first()->user
                ];
            })
            ->sortByDesc('total_donated')
            ->take(10)
            ->values();

        // Donation by status - MongoDB way
        $statusBreakdown = Donation::all()
            ->groupBy('status')
            ->map(function ($donations, $status) {
                return (object) [
                    'status' => $status,
                    'count' => $donations->count()
                ];
            })
            ->values();

        return view('admin.donations.analytics', compact(
            'totalAmount',
            'totalDonations',
            'averageDonation',
            'monthlyData',
            'topDonors',
            'statusBreakdown'
        ));
    }

    // NEW METHODS FOR DONATION REQUESTS

    public function requestsIndex(Request $request)
    {
        $query = DonationRequest::with(['owner', 'donations']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_urgent')) {
            $query->where('is_urgent', $request->is_urgent === '1');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhereHas('owner', function ($ownerQuery) use ($searchTerm) {
                        $ownerQuery->where('first_name', 'like', "%{$searchTerm}%")
                            ->orWhere('last_name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $donationRequests = $query->latest()->paginate(20);

        // Get statistics
        $totalRequests = DonationRequest::count();
        $activeRequests = DonationRequest::where('status', 'active')->count();
        $completedRequests = DonationRequest::where('status', 'completed')->count();
        $totalAmountNeeded = DonationRequest::sum('amount_needed');
        $totalAmountRaised = DonationRequest::sum('amount_received');
        $urgentRequests = DonationRequest::where('is_urgent', true)->where('status', 'pending')->count();

        return view('admin.donation-requests.index', compact(
            'donationRequests',
            'totalRequests',
            'activeRequests',
            'completedRequests',
            'totalAmountNeeded',
            'totalAmountRaised',
            'urgentRequests'
        ));
    }

    public function requestsShow($id)
    {
        $donationRequest = DonationRequest::with(['owner', 'donations.user'])->findOrFail($id);

        // Get donation statistics for this request
        $totalDonations = $donationRequest->donations->count();
        $successfulDonations = $donationRequest->donations->where('status', 'success');
        $totalAmountRaised = $successfulDonations->sum('amount');
        $averageDonation = $totalDonations > 0 ? $totalAmountRaised / $totalDonations : 0;
        $progressPercentage = $donationRequest->amount_needed > 0
            ? ($totalAmountRaised / $donationRequest->amount_needed) * 100
            : 0;

        // Recent donations
        $recentDonations = $donationRequest->donations()
            ->with('user')
            ->where('status', 'success')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.donation-requests.show', compact(
            'donationRequest',
            'totalDonations',
            'totalAmountRaised',
            'averageDonation',
            'progressPercentage',
            'recentDonations'
        ));
    }

    public function updateRequestStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,paused,completed,cancelled'
        ]);

        $donationRequest = DonationRequest::findOrFail($id);
        $donationRequest->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Donation request status updated successfully.');
    }
}