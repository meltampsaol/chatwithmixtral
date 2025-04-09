<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class ChatBotControllerOrig extends Controller
{
    public function chat(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);
        $userPrompt = $request->input('prompt');


        $lastkeywords = ['latest order entry', 'latest order', 'last order entry'];
        $dbInfo = '';
        foreach ($lastkeywords as $keyword) {
            if (str_contains(strtolower($userPrompt), $keyword)) {
                $order = Order::latest()->first();
                $dbInfo = $order ?
                    "Latest order: {$order->id}, customer id is {$order->user_id}, total {$order->total_amount}." :
                    "No orders found.";
                break; // Stop checking once a match is found
            }
        }
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

        $prompt=<<<PROMPT
### Instruction:
You are a smart and conversational assistant. Your task is to understand the user's intent and respond accordingly in a friendly and helpful tone. Maintain context throughout the conversation.

Common user request patterns include:
- "Please show to me the ..."
- "Give me the ..." 
- "Show me the ..."

**Key Requirements**:
1. Carefully analyze this user input: $userPrompt
2. Use this system/database info if relevant: $dbInfo
3. Respond in 1-2 natural, conversational sentences
4. Avoid repeating the user's exact wording
5. If information is unavailable, say so politely

### Response:
PROMPT;
        try {
            $response = Http::timeout(60)->post(env('OLLAMA_API_URL'), [
                'model' => env('OLLAMA_MODEL'),
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json',
            ]);
        } catch (ConnectionException $e) {
            return response()->json(['error' => 'AI service is currently unavailable. Please try again later.'], 503);
        }


        if (!$response->successful()) {
            return response()->json([
                'error' => 'AI service unavailable',
                'details' => $response->body(),
            ], 503);
        }

        return response()->json([
            'reply' => trim($response->json()['response'])
        ]);
    }

    public function enrich(Request $request)
    {
        $request->validate(['text' => 'required|string']);
        $text = $request->input('text');

        $prompt = <<<PROMPT
         Analyze this text and return JSON with summary, keywords, and tone: "$text"
        PROMPT;
        try {
            $response = Http::timeout(60)->post(env('OLLAMA_API_URL'), [
                'model' => env('OLLAMA_MODEL'),
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json',
            ]);
        } catch (ConnectionException $e) {
            return response()->json(['error' => 'AI service is currently unavailable. Please try again later.'], 503);
        }

        if (!$response->successful()) {
            return response()->json(['error' => 'Analysis failed'], 500);
        }



        preg_match('/\{.*\}/s', $response->json()['response'], $matches);
        $json = json_decode($matches[0] ?? '{}', true);

        // Validate structure
        if (empty($json) || !isset($json['summary'], $json['keywords'], $json['tone'])) {
            return response()->json(['error' => 'Invalid analysis format'], 500);
        }

        return response()->json($json);
    }
}
