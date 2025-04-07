<?php

use Illuminate\Support\Facades\Route;



Route::post('/chat', [ChatBotController::class, 'chat']);
