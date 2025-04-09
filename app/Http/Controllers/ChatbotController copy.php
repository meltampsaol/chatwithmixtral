<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class ChatBotController extends Controller
{

    public function chat(Request $request)
    {
        try {
            $request->validate(['prompt' => 'required|string']);
            $userPrompt = $request->input('prompt');
    
            // Get the cacert.pem path from the environment
            $cacertPath = env('CURL_CA_BUNDLE');
    
            // Ensure the path exists before passing it to the HTTP client
            if (!file_exists($cacertPath)) {
                return response()->json(['error' => 'CA certificate file not found.'], 500);
            }
    
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('MISTRAL_API_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->withOptions([
                'verify' => $cacertPath  
            ])
            ->post('https://api.mistral.ai/v1/chat/completions', [
                'model' => 'mistral-medium-2312',
                'messages' => [
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                'max_tokens' => 150,
                'temperature' => 0.7
            ]);
    
            if ($response->successful()) {
               
                $data = $response->json();
                $botReply = $data['choices'][0]['message']['content'] ?? 'No response available';
                return response()->json(['reply' => $botReply]);
            } else {
            
                return response()->json([
                    'error' => 'AI service unavailable',
                    'details' => $response->body(),
                ], 503);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function enrich(Request $request)
    {
        $request->validate(['text' => 'required|string']);
        $text = $request->input('text');

        $prompt = <<<PROMPT
    Analyze the following text and return a JSON with the following structure:
    {
      "summary": "...",
      "keywords": ["...", "..."],
      "tone": "..."
    }
    
    Text:
    "$text"
    PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('MISTRAL_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post(env('MISTRAL_BASE_URL'), [
                'model' => 'mistral-small',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.3,
            ]);
        } catch (ConnectionException $e) {
            return response()->json(['error' => 'Mistral service is currently unavailable. Please try again later.'], 503);
        }

        if (!$response->successful()) {
            return response()->json(['error' => 'Analysis failed', 'details' => $response->body()], 500);
        }

        $message = $response->json()['choices'][0]['message']['content'] ?? '{}';

        // Try to decode Mistral's JSON output
        $parsed = json_decode($message, true);

        return response()->json([
            'summary' => $parsed['summary'] ?? 'N/A',
            'keywords' => $parsed['keywords'] ?? [],
            'tone' => $parsed['tone'] ?? 'N/A',
        ]);
    }
}
