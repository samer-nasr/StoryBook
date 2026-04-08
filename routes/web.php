<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\StoryController;

Route::get('/', function () {
    return view('home');
});

Route::get('/chatbot', [ChatbotController::class, 'index']);
Route::post('/chat', [ChatbotController::class, 'chat']);

// Story Generator Routes
Route::get('/examples', [StoryController::class, 'examples'])->name('examples');
Route::get('/story/create', [StoryController::class, 'create'])->name('story.create');
Route::post('/story/generate', [StoryController::class, 'generate'])->name('story.generate'); // Preserved existing name
Route::get('/story/{story}/processing', [StoryController::class, 'processing'])->name('story.processing');
Route::get('/story/{story}', [StoryController::class, 'show'])->name('story.show');
Route::get('/story/{story}/status', [StoryController::class, 'status'])->name('story.status');

// Admin Routes
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\StoryTemplateController;

Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::resource('templates', StoryTemplateController::class);
});
