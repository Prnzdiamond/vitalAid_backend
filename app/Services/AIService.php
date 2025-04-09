<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\VitalAid\Consultation;
use GuzzleHttp\Exception\RequestException;

class AIService
{

    public static function summarizeChat($consultationId)
    {
        $consultation = Consultation::findOrFail($consultationId);
        $messages = $consultation->messages ?? [];

        if (count($messages) < 5) {
            return null; // No need to summarize yet
        }

        // Extract last 5 messages
        $lastFiveMessages = array_slice($messages, -5);
        $summaryPrompt = "Summarize this conversation briefly: " . json_encode($lastFiveMessages);

        $client = new Client([
            'timeout' => 120, // Total request timeout in seconds
            'connect_timeout' => 120, // Connection timeout

        ]);
        $apiKey = env('MISTRAL_API_KEY');

        try {
            $response = $client->post('https://api.mistral.ai/v1/agents/completions', [
                'headers' => [
                    'Authorization' => "Bearer UPMkMlZ2TgwhzCNUlMjJNH7zhAobeEgv",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'agent_id' => "ag:f607bbe4:20250324:vital-aid-health-assistant:673f2f2c",
                    'messages' => [['role' => 'user', 'content' => $summaryPrompt]],
                ],
                'timeout' => 120,
                'verify' => false,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $summary = $data['choices'][0]['message']['content'] ?? null;

            if ($summary) {
                $consultation->update(['memory' => $summary]); // Store the summary
            }

            return $summary;
        } catch (RequestException $e) {
            Log::error("Mistral Summarization Error: " . $e->getMessage());
            return null;
        }
    }

    public static function generateResponse($consultationId, $userMessage)
    {
        $consultation = Consultation::findOrFail($consultationId);
        $memory = $consultation->memory; // Retrieve stored summary
        $messages = $consultation->messages ?? [];

        // Ensure messages follow Mistral's expected format
        $formattedMessages = [];

        // Add memory summary as context (if available)
        if ($memory) {
            $formattedMessages[] = ['role' => 'system', 'content' => "Previous summary: {$memory}"];
        }

        // Format messages correctly
        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'role' => ($msg['sender'] === 'AI') ? 'assistant' : 'user',
                'content' => $msg['message'],
            ];
        }

        // Add new user message
        $formattedMessages[] = ['role' => 'user', 'content' => $userMessage];

        $client = new Client([
            'timeout' => 120, // Total request timeout in seconds
            'connect_timeout' => 120, // Connection timeout

        ]);
        $apiKey = env('MISTRAL_API_KEY');

        try {
            $response = $client->post('https://api.mistral.ai/v1/agents/completions', [
                'headers' => [
                    'Authorization' => "Bearer UPMkMlZ2TgwhzCNUlMjJNH7zhAobeEgv",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'agent_id' => "ag:f607bbe4:20250324:vital-aid-health-assistant:673f2f2c",
                    'messages' => array_slice($formattedMessages, -5), // Only last 5 messages

                ],
                'timeout' => 120,
                'verify' => false,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $aiResponse = $data['choices'][0]['message']['content'] ?? "I'm sorry, but I couldn't process your request.";

            // Store full chat history
            $messages[] = ['sender' => 'AI', 'message' => $aiResponse, 'timestamp' => now()];
            $consultation->update(['messages' => $messages]);

            return $aiResponse;
        } catch (RequestException $e) {
            Log::error("Mistral AI Error: " . $e->getMessage());
            return "AI service is currently unavailable.";
        }
    }


    // public static function generateResponse($userMessage)
    // {
    //     $apiKey = env('DEEP_SEEK_AI');
    //     Log::info($apiKey);

    //     $client = new Client();

    //     try {
    //         $response = $client->post('https://api.deepseek.com/v1/chat/completions', [
    //             'headers' => [
    //                 'Authorization' => "Bearer sk-53731453690a45958a90d1cb0be10dbb",
    //                 'Content-Type' => 'application/json',
    //             ],
    //             'json' => [
    //                 'model' => 'deepseek-chat',
    //                 'messages' => [
    //                     ['role' => 'system', 'content' => 'You are a helpful medical assistant.'],
    //                     ['role' => 'user', 'content' => $userMessage]
    //                 ],
    //                 'max_tokens' => 100,
    //             ],
    //             'timeout' => 10,
    //             'verify' => false, // Disable SSL verification if necessary
    //         ]);

    //         $data = json_decode($response->getBody()->getContents(), true);

    //         return $data['choices'][0]['message']['content'] ?? "AI could not process your request.";
    //     } catch (RequestException $e) {
    //         Log::error('DeepSeek API Error: ' . $e->getMessage());
    //         return "AI service is currently unavailable.";
    //     }
    // }



    // public static function generateResponse($userMessage)
    // {
    //     $apiKey =  // Ensure this is set in .env

    //     $client = new Client();
    //     Log::info($apiKey);

    //     try {
    //         $response = $client->post('https://api.openai.com/v1/chat/completions', [
    //             'headers' => [
    //                 'Authorization' => "Bearer {$apiKey}",
    //                 'Content-Type' => 'application/json',
    //             ],
    //             'json' => [
    //                 'model' => 'gpt-4o',
    //                 'messages' => [
    //                     ['role' => 'system', 'content' => 'You are a helpful medical assistant.'],
    //                     ['role' => 'user', 'content' => $userMessage]
    //                 ],
    //                 'max_tokens' => 100,
    //             ],
    //             'verify' => false, // Disable SSL verification if necessary
    //             'timeout' => 10, // Set a timeout for the request
    //         ]);

    //         $data = json_decode($response->getBody()->getContents(), true);



    //         return $data['choices'][0]['message']['content'] ?? "I'm sorry, but I couldn't process your request.";
    //     } catch (RequestException $e) {
    //         Log::info($e->getMessage());
    //         return "AI service is currently unavailable.";
    //     }
    // }
}