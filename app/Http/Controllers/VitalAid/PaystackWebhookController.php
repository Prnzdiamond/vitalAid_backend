<?php

namespace App\Http\Controllers\VitalAid;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\VitalAid\Donation;
use App\Models\VitalAid\DonationRequest;
use App\Models\VitalAid\WithdrawalRequest;
use App\Notifications\GeneralNotification;

class PaystackWebhookController extends Controller
{
    /**
     * Handle Paystack webhook events
     */
    public function handleWebhook(Request $request)
    {
        // Verify webhook signature
        $webhookSecret = config('paystack.secretKey');
        $signature = $request->header('x-paystack-signature');

        if (!$signature || hash_hmac('sha512', $request->getContent(), $webhookSecret) !== $signature) {
            Log::warning('Invalid Paystack webhook signature');
            return response()->json(['status' => 'Invalid signature'], 401);
        }

        $payload = $request->all();

        // Process different event types
        switch ($payload['event']) {
            case 'charge.success':
                $this->handleChargeSuccess($payload['data']);
                break;

            case 'transfer.success':
                $this->handleTransferSuccess($payload['data']);
                break;

            case 'transfer.failed':
                $this->handleTransferFailed($payload['data']);
                break;

            default:
                // Other events we're not handling
                Log::info('Unhandled Paystack webhook event: ' . $payload['event']);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle successful charge (donation payment)
     */
    private function handleChargeSuccess($data)
    {
        try {
            // Extract donation ID from metadata if it exists
            if (!isset($data['metadata']['donation_id'])) {
                Log::error('Donation ID not found in charge.success webhook metadata');
                return;
            }

            $donationId = $data['metadata']['donation_id'];
            $donation = Donation::find($donationId);

            if (!$donation) {
                Log::error('Donation not found for ID: ' . $donationId);
                return;
            }

            // If donation is already marked as success, no need to process again
            if ($donation->payment_status === 'success') {
                return;
            }

            // Update donation status
            $donation->payment_status = 'success';
            $donation->status = 'success';
            $donation->save();

            // Update donation request amount
            $donationRequest = DonationRequest::find($donation->donation_request_id);
            if ($donationRequest) {
                $donationRequest->increment('amount_received', $donation->amount);

                if ($donationRequest->amount_received >= $donationRequest->amount_needed) {
                    $donationRequest->status = 'funded';
                    $donationRequest->save();
                }

                // Send notifications
                try {
                    $donationRequest->owner->notify(new GeneralNotification([
                        'title' => 'Donation Made',
                        'body' => "Your donation request '{$donationRequest->title}' has received {$donation->amount}",
                        'type' => 'general',
                        'extra' => [
                            'route' => "/donate/{$donationRequest->id}",
                            'donation_id' => $donationRequest->id,
                        ]
                    ]));

                    $donation->user->notify(new GeneralNotification([
                        'title' => 'Donation Successful',
                        'body' => "Your donation to '{$donationRequest->title}' with amount {$donation->amount} was successful. Thank you!",
                        'type' => 'general',
                        'extra' => [
                            'route' => "/donate/{$donationRequest->id}",
                            'donation_id' => $donationRequest->id,
                        ]
                    ]));
                } catch (\Exception $e) {
                    Log::error('Failed to send donation notifications: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('Error processing charge.success webhook: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful transfer (withdrawal payout)
     */
    private function handleTransferSuccess($data)
    {
        try {
            $reference = $data['reference'];
            $withdrawal = WithdrawalRequest::where('payout_reference', $reference)->first();

            if (!$withdrawal) {
                Log::error('Withdrawal not found for reference: ' . $reference);
                return;
            }

            // If withdrawal is already marked as completed, no need to process again
            if ($withdrawal->status === 'completed') {
                return;
            }

            // Update withdrawal status
            $withdrawal->status = 'completed';
            $withdrawal->save();

            // Notify organization
            try {
                $organization = $withdrawal->organization;
                $donationRequest = $withdrawal->donationRequest;

                if ($organization && $donationRequest) {
                    $organization->notify(new GeneralNotification([
                        'title' => 'Withdrawal Completed',
                        'body' => "Your withdrawal of {$withdrawal->amount} from '{$donationRequest->title}' has been completed.",
                        'type' => 'general',
                        'extra' => [
                            'withdrawal_id' => $withdrawal->id,
                        ]
                    ]));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send withdrawal completion notification: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error processing transfer.success webhook: ' . $e->getMessage());
        }
    }

    /**
     * Handle failed transfer (withdrawal payout failure)
     */
    private function handleTransferFailed($data)
    {
        try {
            $reference = $data['reference'];
            $withdrawal = WithdrawalRequest::where('payout_reference', $reference)->first();

            if (!$withdrawal) {
                Log::error('Withdrawal not found for reference: ' . $reference);
                return;
            }

            // If withdrawal is already marked as failed, no need to process again
            if ($withdrawal->status === 'failed') {
                return;
            }

            // Update withdrawal status
            $withdrawal->status = 'failed';
            $withdrawal->save();

            // Revert the withdrawn amount in the donation request
            $donationRequest = $withdrawal->donationRequest;
            if ($donationRequest) {
                $donationRequest->withdrawn_amount = max(0, ($donationRequest->withdrawn_amount ?? 0) - $withdrawal->amount);
                $donationRequest->save();
            }

            // Notify organization
            try {
                $organization = $withdrawal->organization;

                if ($organization && $donationRequest) {
                    $organization->notify(new GeneralNotification([
                        'title' => 'Withdrawal Failed',
                        'body' => "Your withdrawal of {$withdrawal->amount} from '{$donationRequest->title}' has failed. The amount has been returned to your available balance.",
                        'type' => 'general',
                        'extra' => [
                            'withdrawal_id' => $withdrawal->id,
                        ]
                    ]));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send withdrawal failure notification: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error processing transfer.failed webhook: ' . $e->getMessage());
        }
    }
}
