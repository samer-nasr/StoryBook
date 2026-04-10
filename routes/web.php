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

// Test Route for Prompts
Route::get('/test-prompts', function (\App\Services\PromptService $promptService) {
    if (!\Illuminate\Support\Facades\Schema::hasTable('prompt_templates')) {
        return "Please run 'php artisan migrate' and 'php artisan db:seed --class=PromptTemplateSeeder' first.";
    }

    $prompts = $promptService->getActivePrompts();
    $globalPrompt = $prompts->get('global');

    if (!$globalPrompt) {
        return "Global prompt is currently missing or inactive in the database!";
    }

    // This simulates EXACTLY what StoryController::generate() builds and passes to AIImageService
    $config = [
        'version' => $globalPrompt->version,
        'seed' => mt_rand(10000000, 99999999),
        'style_block' => $globalPrompt->style_block,
        'identity_block' => $globalPrompt->identity_block,
    ];
    
    $charPrompt = $promptService->getPrompt('character_generation', $config);
    $pagePrompt = $promptService->getPrompt('page_generation', $config);

    echo "<h1>Character Generation Prompt</h1>";
    echo "<pre style='background:#f4f4f4; padding:15px; border:1px solid #ccc; font-family:monospace; white-space:pre-wrap;'>" . htmlspecialchars($charPrompt) . "</pre>";

    echo "<h1>Page Generation Prompt</h1>";
    echo "<pre style='background:#f4f4f4; padding:15px; border:1px solid #ccc; font-family:monospace; white-space:pre-wrap;'>" . htmlspecialchars($pagePrompt) . "</pre>";
});

// Admin Routes
use App\Http\Controllers\Admin\PromptTemplateController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\StoryTemplateController;

Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::resource('templates', StoryTemplateController::class);
    Route::resource('prompt-templates', PromptTemplateController::class);
});
