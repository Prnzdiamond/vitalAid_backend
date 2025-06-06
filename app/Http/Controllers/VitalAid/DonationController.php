<?php

namespace App\Http\Controllers\VitalAid;

use Illuminate\Http\Request;
use App\Models\VitalAid\Donation;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\PaystackService;
use App\Models\VitalAid\DonationRequest;
use Illuminate\Support\Facades\Validator;
use App\Notifications\GeneralNotification;

class DonationController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    /**
     * Initialize a donation payment with Paystack
     */
    public function donate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'donation_request_id' => 'required|exists:donation_requests,_id',
            'amount' => 'required|numeric|min:1',
            'is_anonymous' => 'boolean',
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

            // Create a pending donation record
            $donation = Donation::create([
                'user_id' => $user->id,
                'donation_request_id' => $request->donation_request_id,
                'amount' => $request->amount,
                'payment_status' => 'pending',
                'is_anonymous' => $request->is_anonymous ?? false,
                'status' => 'pending',
            ]);

            // CHANGED: Redirect to backend endpoint instead of frontend
            $backendUrl = config('app.url', 'http://localhost:8000');
            $callbackUrl = "{$backendUrl}/api/donations/verify/{$donation->id}";
            Log::info($callbackUrl);

            // Initialize Paystack payment
            $paymentData = $this->paystackService->initializeDonationPayment(
                $user->email,
                $request->amount,
                $callbackUrl,
                [
                    'donation_id' => $donation->id,
                    'donation_request_id' => $request->donation_request_id,
                    'user_id' => $user->id
                ]
            );

            if (!$paymentData['status']) {
                $donation->delete(); // Delete the pending donation
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to initialize payment.',
                ], 500);
            }

            // Update donation with payment reference
            $donation->paystack_reference = $paymentData['data']['reference'];
            $donation->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment initialized.',
                'data' => [
                    'authorization_url' => $paymentData['data']['authorization_url'],
                    'reference' => $paymentData['data']['reference'],
                    'donation_id' => $donation->id
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donation request not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to process donation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process donation.'
            ], 500);
        }
    }

    /**
     * Verify a donation payment after Paystack redirects back
     */
    // In DonationController.php - modify the verify method

    public function verify(Request $request, $donation_id)
    {
        try {
            $donation = Donation::findOrFail($donation_id);

            if ($donation->payment_status === 'success') {
                // Payment already verified
                $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
                $redirectUrl = "{$frontendUrl}/donate/{$donation->donation_request_id}?verified=true&donation_id={$donation->id}";

                Log::info($redirectUrl);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment already verified.',
                        'data' => [
                            'donation' => $donation,
                            'redirect_url' => $redirectUrl
                        ]
                    ], 200);
                }

                return redirect($redirectUrl);
            }

            // Verify the payment with Paystack
            $paymentData = $this->paystackService->verifyPayment($donation->paystack_reference);

            if (!$paymentData['status'] || $paymentData['data']['status'] !== 'success') {
                $donation->payment_status = 'failed';
                $donation->status = 'failed';
                $donation->save();

                $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
                $redirectUrl = "{$frontendUrl}/donate/{$donation->donation_request_id}?verified=false&donation_id={$donation->id}";

                Log::info($redirectUrl);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment verification failed.',
                        'redirect_url' => $redirectUrl
                    ], 400);
                }

                return redirect($redirectUrl);
            }

            // Update donation as successful
            $donation->payment_status = 'success';
            $donation->status = 'success';
            $donation->save();

            // Update donation request amount
            $donationRequest = DonationRequest::findOrFail($donation->donation_request_id);
            $donationRequest->increment('amount_received', $donation->amount);

            if ($donationRequest->amount_received >= $donationRequest->amount_needed) {
                $donationRequest->status = 'funded';
                $donationRequest->save();
            }

            // Send notifications
            $donationRequest->owner->notify(new GeneralNotification([
                'title' => 'Donation Made',
                'body' => "Your donation request '{$donationRequest->title}' has received {$donation->amount}",
                'type' => 'general',
                'extra' => [
                    'route' => "/donate/{$donationRequest->id}",
                    'donation_id' => $donationRequest->id,
                ]
            ]));

            $user = $donation->user;
            $user->notify(new GeneralNotification([
                'title' => 'Donated',
                'body' => "Your donation to '{$donationRequest->title}' with amount {$donation->amount} was successful. Thank you!",
                'type' => 'general',
                'extra' => [
                    'route' => "/donate/{$donationRequest->id}",
                    'donation_id' => $donationRequest->id,
                ]
            ]));

            // If API request, return JSON
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $redirectUrl = "{$frontendUrl}/donate/{$donation->donation_request_id}?verified=true&donation_id={$donation->id}";

            Log::info($redirectUrl);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully.',
                    'data' => [
                        'donation' => $donation,
                        'redirect_url' => $redirectUrl
                    ]
                ], 200);
            }

            // For browser redirect from Paystack, redirect to frontend
            return redirect($redirectUrl);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $redirectUrl = "{$frontendUrl}/donate?error=not_found";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation not found.',
                    'redirect_url' => $redirectUrl
                ], 404);
            }

            return redirect($redirectUrl);
        } catch (\Exception $e) {
            Log::error('Failed to verify payment: ' . $e->getMessage());

            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $redirectUrl = "{$frontendUrl}/donate?error=verification_failed";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to verify payment.',
                    'redirect_url' => $redirectUrl
                ], 500);
            }

            return redirect($redirectUrl);
        }
    }

    // Original method - kept for backward compatibility
    public function getOrganizationDonations(Request $request)
    {
        $user = $request->user();

        try {
            $donationRequests = $user->donationRequests()->pluck('_id');

            $donations = Donation::with('donationRequest')
                ->whereIn('donation_request_id', $donationRequests)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'donations' => $donations
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch organization donations for user ' . $user->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch organization donations.'
            ], 500);
        }
    }

    // Original method - kept for backward compatibility
    public function getUserDonations(Request $request)
    {
        $user = $request->user();

        try {
            $donations = Donation::where('user_id', $user->id)->with('donationRequest')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'donations' => $donations
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch user donations for user ' . $user->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user donations.'
            ], 500);
        }
    }
}