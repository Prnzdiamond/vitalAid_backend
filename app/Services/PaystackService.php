<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    protected $baseUrl;
    protected $secretKey;
    protected $publicKey;
    protected $client;

    protected $isLocal;


    public function __construct()
    {
        $this->isLocal = app()->environment('local');
        $this->baseUrl = config('paystack.baseUrl', 'https://api.paystack.co');
        $this->secretKey = config('paystack.secretKey');
        $this->publicKey = config('paystack.publicKey');

        Log::info($this->secretKey);

        // Initialize Guzzle client
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'verify' => !$this->isLocal, // Disable SSL verification in local, true in prod
        ]);
    }

    /**
     * Initialize a donation payment transaction
     *
     * @param string $email
     * @param float $amount
     * @param string $callback_url
     * @param array $metadata
     * @return array
     */
    public function initializeDonationPayment($email, $amount, $callback_url, $metadata = [])
    {
        try {
            $amountInKobo = $amount * 100; // Convert to kobo (Paystack uses the smallest currency unit)

            $response = $this->client->post('/transaction/initialize', [
                'json' => [
                    'email' => $email,
                    'amount' => $amountInKobo,
                    'callback_url' => $callback_url,
                    'metadata' => $metadata,
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            Log::error('Paystack payment initialization failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify a payment transaction
     *
     * @param string $reference
     * @return array
     */
    public function verifyPayment($reference)
    {
        try {
            $response = $this->client->get('/transaction/verify/' . $reference);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            Log::error('Paystack payment verification failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a transfer recipient for payouts
     *
     * @param string $name
     * @param string $account_number
     * @param string $bank_code
     * @return array
     */
    public function createTransferRecipient($name, $account_number, $bank_code)
    {
        try {
            $response = $this->client->post('/transferrecipient', [
                'json' => [
                    'type' => 'nuban',
                    'name' => $name,
                    'account_number' => $account_number,
                    'bank_code' => $bank_code,
                    'currency' => 'NGN',
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            Log::error('Paystack recipient creation failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Recipient creation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Initiate a transfer to a recipient
     *
     * @param float $amount
     * @param string $recipient_code
     * @param string $reason
     * @return array
     */
    public function initiateTransfer($amount, $recipient_code, $reason = null)
    {
        try {
            $amountInKobo = $amount * 100; // Convert to kobo

            $response = $this->client->post('/transfer', [
                'json' => [
                    'source' => 'balance',
                    'amount' => $amountInKobo,
                    'recipient' => $recipient_code,
                    'reason' => $reason,
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            Log::error('Paystack transfer initiation failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Transfer initiation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify a transfer status
     *
     * @param string $reference
     * @return array
     */
    public function verifyTransfer($reference)
    {
        try {
            $response = $this->client->get('/transfer/verify/' . $reference);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            Log::error('Paystack transfer verification failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Transfer verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * List available banks
     *
     * @return array
     */
    public function listBanks()
    {
        try {
            $response = $this->client->get('/bank', [
                'query' => ['currency' => 'NGN']
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            Log::error('Paystack bank listing failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Bank listing failed: ' . $e->getMessage()
            ];
        }
    }
}
