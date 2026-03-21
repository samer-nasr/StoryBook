<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\StoryController; // Add this line

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chatbot', [ChatbotController::class, 'index']);
Route::post('/chat', [ChatbotController::class, 'chat']);

// Story Generator Routes
Route::get('/story/create', [StoryController::class, 'create'])->name('story.create');
Route::post('/story/generate', [StoryController::class, 'generate'])->name('story.generate');
