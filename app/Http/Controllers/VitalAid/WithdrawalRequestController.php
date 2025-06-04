<?php

namespace App\Http\Controllers\VitalAid;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\VitalAid\DonationRequest;
use App\Models\VitalAid\WithdrawalRequest;
use App\Services\PaystackService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Notifications\GeneralNotification;
use App\Http\Resources\VitalAid\WithdrawalRequestResource;
use App\Http\Resources\VitalAid\WithdrawalRequestCollection;

class WithdrawalRequestController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    /**
     * Organization submits a withdrawal request
     */
    public function requestWithdrawal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'donation_request_id' => 'required|exists:donation_requests,_id',
            'amount' => 'required|numeric|min:1',
            'bank_details.account_number' => 'required|string',
            'bank_details.bank_code' => 'required|string',
            'bank_details.account_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        try {
            $donationRequest = DonationRequest::findOrFail($request->donation_request_id);

            // Check ownership
            if ($donationRequest->org_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            // Calculate available balance (amount_received - withdrawn_amount)
            $withdrawnAmount = $donationRequest->withdrawn_amount ?? 0;
            $availableBalance = $donationRequest->amount_received - $withdrawnAmount;

            if ($request->amount > $availableBalance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient funds. Available balance: ' . $availableBalance,
                ], 400);
            }

            // Create transfer recipient on Paystack
            $recipientResponse = $this->paystackService->createTransferRecipient(
                $request->bank_details['account_name'],
                $request->bank_details['account_number'],
                $request->bank_details['bank_code']
            );

            if (!$recipientResponse['status']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create transfer recipient: ' . $recipientResponse['message'],
                ], 500);
            }

            $recipientCode = $recipientResponse['data']['recipient_code'];



            // Create withdrawal request record
            $withdrawalRequest = WithdrawalRequest::create([
                'donation_request_id' => $request->donation_request_id,
                'org_id' => $user->id,
                'amount' => $request->amount,
                'bank_details' => $request->bank_details,
                'status' => 'pending', // Will be manually approved by admin
                'recipient_code' => $recipientCode, // Store for manual transfer
            ]);

            // Update donation request withdrawn amount
            $donationRequest->withdrawn_amount = ($donationRequest->withdrawn_amount ?? 0) + $request->amount;
            $donationRequest->save();

            // Notify organization
            $user->notify(new GeneralNotification([
                'title' => 'Withdrawal Initiated',
                'body' => "Your withdrawal request for {$request->amount} from '{$donationRequest->title}' is pending admin approval.",
                'type' => 'general',
                'extra' => [
                    'withdrawal_id' => $withdrawalRequest->id,
                ]
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request initiated.',
                'data' => [
                    'withdrawal' => new WithdrawalRequestResource($withdrawalRequest),
                ]
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donation request not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to process withdrawal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Organization retrieves their withdrawal history
     */
    public function list(Request $request)
    {
        $user = $request->user();

        try {
            $withdrawals = WithdrawalRequest::where('org_id', $user->id)
                ->with('donationRequest')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'withdrawals' => WithdrawalRequestResource::collection($withdrawals)
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch withdrawals: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch withdrawal history.'
            ], 500);
        }
    }

    /**
     * Admin retrieves all withdrawal requests
     */
    // public function listAll(Request $request)
    // {
    //     // Check if user is admin
    //     $user = $request->user();
    //     if ($user->role !== 'admin') {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unauthorized.'
    //         ], 403);
    //     }

    //     try {
    //         $withdrawals = WithdrawalRequest::with(['donationRequest', 'organization'])
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'withdrawals' => WithdrawalRequestResource::collection($withdrawals)
    //             ]
    //         ], 200);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to fetch all withdrawals: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch withdrawal records.'
    //         ], 500);
    //     }
    // }

    /**
     * Check withdrawal status manually
     */
    public function checkStatus(Request $request, $id)
    {
        $user = $request->user();

        try {
            $withdrawal = WithdrawalRequest::findOrFail($id);

            // Ensure user owns this withdrawal or is admin
            if ($withdrawal->org_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.'
                ], 403);
            }


            // Only check if withdrawal is still pending
            if ($withdrawal->status !== 'pending') {
                return response()->json([
                    'success' => true,
                    'message' => 'Withdrawal status: ' . $withdrawal->status,
                    'data' => [
                        'withdrawal' => new WithdrawalRequestResource($withdrawal)
                    ]
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal is pending admin approval.',
                'data' => [
                    'withdrawal' => new WithdrawalRequestResource($withdrawal)
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal request not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to check withdrawal status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check withdrawal status.'
            ], 500);
        }
    }

    /**
     * Get available bank list for withdrawals
     */
    public function getBanks()
    {
        try {
            $banks = $this->paystackService->listBanks();

            if (!$banks['status']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch bank list.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'banks' => $banks['data']
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch banks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bank list.'
            ], 500);
        }
    }
}