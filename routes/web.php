<?php

use App\Http\Controllers\ChatBotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('chat');
});

Route::post('/chat', [ChatBotController::class, 'chat']);
Route::post('/enrich', [ChatBotController::class, 'enrich']);
