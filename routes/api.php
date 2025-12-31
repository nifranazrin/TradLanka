<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatbotController;

Route::post('/chatbot', [ChatbotController::class, 'handle']);
