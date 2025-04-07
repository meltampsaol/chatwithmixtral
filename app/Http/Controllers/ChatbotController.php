<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order; // example model
use Symfony\Component\Process\Process;


class ChatBotController extends Controller
{
    public function chat(Request $request)
    {
        $userPrompt = $request->input('prompt');

        // --- Your logic here ---
        $dbInfo = '';
        if (str_contains(strtolower($userPrompt), 'latest order')) {
            $order = Order::latest()->first();
            if ($order) {
                $dbInfo = "Latest order placed: {$order->id}, by {$order->customer_name}, total of {$order->total}.";
            } else {
                $dbInfo = "There are no orders yet.";
            }
        }

        // --- Construct Mixtral prompt ---
        $prompt = <<<PROMPT
You're a helpful assistant. User said: "$userPrompt"

Here is some data from the system:
$dbInfo

Respond in a friendly, conversational tone.
PROMPT;

        $process = new Process(['ollama', 'run', 'mixtral']);
        $process->setInput($prompt . "\n");
        $process->run();

        if (!$process->isSuccessful()) {
            return response()->json(['error' => 'Mixtral failed']);
        }

        return response()->json([
            'reply' => trim($process->getOutput())
        ]);
    }

    public function enrich(Request $request)
    {
        $text = $request->input('text');

        if (!$text) {
            return response()->json(['error' => 'Text input required.'], 400);
        }

        $prompt = <<<PROMPT
You are a helpful assistant. Read the following content and return a JSON response with:

{
  "summary": "A short, clear summary",
  "keywords": ["important", "terms", "from", "content"],
  "tone": "overall tone of writing"
}

Content:
"$text"

ONLY return raw JSON. Do not include commentary or explanations.
PROMPT;

        $process = new Process(['ollama', 'run', 'mixtral']);
        $process->setInput($prompt . "\n");
        $process->run();

        if (!$process->isSuccessful()) {
            return response()->json(['error' => 'Mixtral process failed.'], 500);
        }

        $output = trim($process->getOutput());

        // Try to decode the JSON
        $json = json_decode($output, true);

        if ($json === null) {
            return response()->json([
                'error' => 'Invalid JSON from Mixtral',
                'raw' => $output,
            ]);
        }

        return response()->json($json);
    }
}
