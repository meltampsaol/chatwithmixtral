<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class ChatBotController extends Controller
{
    public function chat(Request $request)
    {
        try {
            // Validate the user's input
            $request->validate(['prompt' => 'required|string']);
            $userPrompt = $request->input('prompt');
    
            // Initialize the database information as an empty string
            $dbInfo = '';
            $lastkeywords = ['latest order entry', 'latest order', 'last order entry'];
    
            // Check if the prompt contains keywords for "latest order"
            foreach ($lastkeywords as $keyword) {
                if (str_contains(strtolower($userPrompt), $keyword)) {
                    $order = Order::latest()->first();
                    $dbInfo = $order ? 
                        "Latest order: {$order->id}, customer id is {$order->user_id}, total {$order->total_amount}." : 
                        "No orders found.";
                    break;
                }
            }
    
            // Check if the prompt contains keywords for "first order"
            if (empty($dbInfo)) {
                $firstkeywords = ['first order', 'first order entry'];
                foreach ($firstkeywords as $fkeyword) {
                    if (str_contains(strtolower($userPrompt), $fkeyword)) {
                        $order = Order::first();
                        $dbInfo = $order ?
                            "First record order: {$order->id}, customer id is {$order->user_id}, total {$order->total_amount}." :
                            "No orders found.";
                        break;
                    }
                }
            }
    
            // Check if the prompt asks for the "top X employees"
            if (empty($dbInfo)) {
                if (preg_match('/top (\d+) employees/i', $userPrompt, $matches)) {
                    $topCount = intval($matches[1]);
                    $employees = Employee::orderBy('id', 'desc')
                        ->take($topCount)
                        ->get();
                    if ($employees->isNotEmpty()) {
                        $dbInfo = "List of Employees:\n";
                        foreach ($employees as $employee) {
                            $dbInfo .= "- {$employee->name} (ID: {$employee->id})\n";
                        }
                    } else {
                        $dbInfo = "No employees found.";
                    }
                }
            }
    
            // Check if the prompt asks for the "top X orders"
            if (empty($dbInfo)) {
                if (preg_match('/top (\d+) orders/i', $userPrompt, $ordmatch)) {
                    $topCnt = intval($ordmatch[1]);
                    $orders = Order::orderBy('total_amount', 'desc')
                        ->take($topCnt)
                        ->get();
                    if ($orders->isNotEmpty()) {
                        $dbInfo = "List of Orders:\n";
                        foreach ($orders as $order) {
                            $dbInfo .= "- {$order->total_amount} (ID: {$order->order_number})\n";
                        }
                    } else {
                        $dbInfo = "No orders found.";
                    }
                }
            }
    
            // Formulate the prompt to be sent to Mistral API
            $prompt = <<<PROMPT
    ### Instruction:
    You are a smart and conversational assistant. Your task is to understand the user's intent and respond accordingly in a friendly and helpful tone. Maintain context throughout the conversation.
    
    Common user request patterns include:
    - "Please show to me the ..."
    - "Give me the ..." 
    - "Show me the ..."
    
    **Key Requirements**:
    1. Carefully analyze this user input: "$userPrompt"
    2. Use this system/database info if relevant: "$dbInfo"
    3. Respond in 1-2 natural, conversational sentences
    4. Avoid repeating the user's exact wording
    5. If information is unavailable, say so politely
    
    ### Response:
    PROMPT;
    
            // Send the request to the Mistral API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('MISTRAL_API_KEY'),
                'Content-Type' => 'application/json',
            ])->withOptions([
                'verify' => env('CURL_CA_BUNDLE')
            ])->post('https://api.mistral.ai/v1/chat/completions', [
                'model' => 'mistral-medium-2312',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 150,
                'temperature' => 0.7
            ]);
    
            // Handle the response
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
            return response()->json([
                'error' => 'Server exception',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
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
