<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VitalAid\WithdrawalRequest;
use App\Models\VitalAid\DonationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = WithdrawalRequest::with(['donationRequest', 'organization']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $fromDate = Carbon::parse($request->date_from)->startOfDay()->toDateTime();
            $query->where('created_at', '>=', $fromDate);
        }

        if ($request->filled('date_to')) {
            $toDate = Carbon::parse($request->date_to)->endOfDay()->toDateTime();
            $query->where('created_at', '<=', $toDate);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('organization', function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $withdrawalRequests = $query->latest()->paginate(20);

        // Get statistics
        $totalRequests = WithdrawalRequest::count();
        $pendingRequests = WithdrawalRequest::where('status', 'pending')->count();
        $approvedRequests = WithdrawalRequest::where('status', 'completed')->count();
        $totalAmount = WithdrawalRequest::where('status', 'completed')->sum('amount');

        // Get analytics data
        $analyticsData = $this->getAnalyticsData();

        return view('admin.withdrawals.index', compact(
            'withdrawalRequests',
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'totalAmount'
        ) + $analyticsData);
    }

    public function show($id)
    {
        $withdrawal = WithdrawalRequest::with(['donationRequest.donations', 'organization'])
            ->findOrFail($id);

        // Calculate available amount for withdrawal
        $donationRequest = $withdrawal->donationRequest;
        $totalDonated = $donationRequest->donations()
            ->where(function ($query) {
                $query->orWhere('status', 'success')
                    ->orWhere('payment_status', 'success');
            })
            ->sum('amount');

        $alreadyWithdrawn = WithdrawalRequest::where('donation_request_id', $donationRequest->_id)
            ->where('status', 'completed')
            ->where('_id', '!=', $withdrawal->_id)
            ->sum('amount');

        $availableAmount = $totalDonated - $alreadyWithdrawn;

        return view('admin.withdrawals.show', compact('withdrawal', 'availableAmount'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'payout_reference' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        try {


            $withdrawal = WithdrawalRequest::findOrFail($id);

            // Verify available funds
            $donationRequest = DonationRequest::findOrFail($withdrawal->donation_request_id);
            $totalDonated = $donationRequest->donations()->where('status', 'success')->sum('amount');
            $alreadyWithdrawn = WithdrawalRequest::where('donation_request_id', $donationRequest->_id)
                ->where('status', 'completed')
                ->where('_id', '!=', $withdrawal->_id)
                ->sum('amount');

            $availableAmount = $totalDonated - $alreadyWithdrawn;

            if ($withdrawal->amount > $availableAmount) {
                throw new \Exception('Insufficient funds available for withdrawal');
            }

            // Update withdrawal status
            $currentDateTime = Carbon::now()->toDateTime();
            $withdrawal->update([
                'status' => 'completed',
                'payout_reference' => $request->payout_reference,
                'approved_at' => $currentDateTime,
                'approved_by' => auth()->id(),
                'admin_notes' => $request->admin_notes,
                'manual_transfer_completed' => true,
                'completed_at' => $currentDateTime,
            ]);




            Log::info('Withdrawal approved', [
                'withdrawal_id' => $withdrawal->_id,
                'amount' => $withdrawal->amount,
                'approved_by' => auth()->id()
            ]);

            return redirect()->route('admin.withdrawals.show', $withdrawal->_id)
                ->with('success', 'Withdrawal request approved successfully.');

        } catch (\Exception $e) {

            Log::error('Error approving withdrawal', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error approving withdrawal: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        try {
            $withdrawal = WithdrawalRequest::findOrFail($id);

            // Get the related donation request
            $donationRequest = DonationRequest::findOrFail($withdrawal->donation_request_id);

            $currentDateTime = Carbon::now()->toDateTime();
            $withdrawal->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => $currentDateTime,
                'rejected_by' => auth()->id()
            ]);

            // Refund the withdrawn amount back to the donation request
            // This assumes that when the withdrawal was initially created/pending,
            // the withdrawn_amount was incremented to "reserve" the funds
            $donationRequest->decrement('withdrawn_amount', $withdrawal->amount);

            Log::info('Withdrawal rejected and funds refunded', [
                'withdrawal_id' => $withdrawal->_id,
                'amount_refunded' => $withdrawal->amount,
                'reason' => $request->rejection_reason,
                'rejected_by' => auth()->id()
            ]);

            return redirect()->route('admin.withdrawals.show', $withdrawal->_id)
                ->with('success', 'Withdrawal request rejected and funds have been refunded.');

        } catch (\Exception $e) {
            Log::error('Error rejecting withdrawal', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error rejecting withdrawal request.');
        }
    }

    private function getAnalyticsData()
    {
        // Monthly withdrawal trends - MongoDB compatible
        $monthlyWithdrawals = collect();
        $currentYear = Carbon::now()->year;

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($currentYear, $month, 1)->startOfMonth()->toDateTime();
            $endDate = Carbon::create($currentYear, $month, 1)->endOfMonth()->toDateTime();

            $monthData = WithdrawalRequest::where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->get();

            $monthlyWithdrawals->push((object) [
                'month' => $month,
                'count' => $monthData->count(),
                'total' => $monthData->sum('amount')
            ]);
        }

        // Filter out months with no data
        $monthlyWithdrawals = $monthlyWithdrawals->filter(function ($item) {
            return $item->count > 0;
        });

        // Status breakdown
        $statusBreakdown = collect();
        $statuses = ['pending', 'completed', 'rejected']; // Add other statuses as needed

        foreach ($statuses as $status) {
            $requests = WithdrawalRequest::where('status', $status)->get();
            if ($requests->count() > 0) {
                $statusBreakdown->push((object) [
                    'status' => $status,
                    'count' => $requests->count(),
                    'total' => $requests->sum('amount')
                ]);
            }
        }

        // Top organizations by withdrawal amount
        $approvedWithdrawals = WithdrawalRequest::with('organization')
            ->where('status', 'completed')
            ->get();

        $topOrganizations = $approvedWithdrawals
            ->groupBy('org_id')
            ->map(function ($withdrawals, $orgId) {
                return (object) [
                    'org_id' => $orgId,
                    'organization' => $withdrawals->first()->organization,
                    'request_count' => $withdrawals->count(),
                    'total_withdrawn' => $withdrawals->sum('amount')
                ];
            })
            ->sortByDesc('total_withdrawn')
            ->take(10)
            ->values();

        // Average processing time for approved withdrawals
        $approvedWithdrawalsWithTimes = WithdrawalRequest::whereNotNull('approved_at')
            ->where('status', 'completed')
            ->get();

        $processingTimes = $approvedWithdrawalsWithTimes->map(function ($withdrawal) {
            $approvedAt = Carbon::parse($withdrawal->approved_at);
            $createdAt = Carbon::parse($withdrawal->created_at);
            return $approvedAt->diffInHours($createdAt);
        });

        $averageProcessingTime = $processingTimes->avg();

        return compact(
            'monthlyWithdrawals',
            'statusBreakdown',
            'topOrganizations',
            'averageProcessingTime'
        );
    }

    public function analytics()
    {
        $analyticsData = $this->getAnalyticsData();
        return view('admin.withdrawals.analytics', $analyticsData);
    }
}
