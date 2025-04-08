<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatBotControllerTest extends TestCase
{


    public function test_chat_endpoint_with_valid_prompt()
    {
        // Simulate a valid request to the chat endpoint
        $response = $this->postJson('/chat', [
            'prompt' => 'Tell me about the latest order',
        ]);

        // Assert the response was successful
        $response->assertStatus(200);

        // Assert the response contains the expected data structure
        $response->assertJsonStructure(['reply']);
    }

    public function test_chat_endpoint_with_invalid_prompt()
    {
        // Simulate a request with a missing or invalid 'prompt'
        $response = $this->postJson('/chat', []);

        // Assert the response failed due to validation
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['prompt']);
    }

    public function test_chat_endpoint_with_mistral_api_response()
    {
        // Mock the Mixtral API response
        Http::fake([
            'http://localhost:11434/api/generate' => Http::response([
                'model' => 'mistral',
                'prompt' => 'Capital of France',
                'stream' => false,
                'format' => 'json',
                'response' => 'This is a mocked Mistral reply.',
            ], 200),
        ]);

        $response = $this->postJson('/chat', [
            'prompt' => 'Tell me about the latest order',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'reply' => 'This is a mocked Mistral reply.', // Corrected typo here
        ]);
    }

    public function test_enrich_endpoint_with_valid_text()
    {
        Http::fake([
            'http://localhost:11434/api/generate' => Http::response([
                'response' => '{"summary": "test summary", "model": "mistral, "tone": "neutral"}',
            ], 200),
        ]);

        $response = $this->postJson('/enrich', [
            'text' => 'Analyze this example text.',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['summary', 'keywords', 'tone']);
    }
}
