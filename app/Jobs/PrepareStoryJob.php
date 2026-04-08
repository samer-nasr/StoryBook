<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use App\Models\StoryGeneration;
use App\Services\AIImageService;
use App\Services\StoryGenerationService;
use Throwable;

class PrepareStoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    public $timeout = 1200; // 20 minutes for character generation + PDF extraction

    /**
     * Number of times the job may be attempted.
     * Allows retries on safety filter or rate limit errors.
     */
    public $tries = 3;

    /**
     * Exponential backoff intervals (in seconds) between retries.
     */
    public $backoff = [30, 60, 120];

    protected int $storyId;
    protected string $photoPath;
    protected string $childName;
    protected string $pdfPath;
    protected string $prompt;

    /**
     * Middleware: throttle exceptions to handle transient errors.
     */
    public function middleware(): array
    {
        return [
            (new ThrottlesExceptions(2, 60))->backoff(1),
        ];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(int $storyId, string $photoPath, string $childName, string $pdfPath, string $prompt)
    {
        $this->storyId = $storyId;
        $this->photoPath = $photoPath;
        $this->childName = $childName;
        $this->pdfPath = $pdfPath;
        $this->prompt = $prompt;
    }

    /**
     * Execute the job.
     */
    public function handle(AIImageService $aiService, StoryGenerationService $storyService): void
    {
        $story = StoryGeneration::findOrFail($this->storyId);

        try {
            // 1. Update status to processing
            $story->update(['status' => 'processing']);
            Log::info("[Story #{$this->storyId}] PrepareStoryJob started for '{$this->childName}' (attempt {$this->attempts()}).");

            // 2. Generate the character image
            Log::info("[Story #{$this->storyId}] Generating character image...");
            $characterImagePath = $aiService->generateCharacterImage(
                $this->photoPath,
                $this->childName,
                $this->storyId
            );
            $story->update(['character_image_path' => $characterImagePath]);
            Log::info("[Story #{$this->storyId}] Character image saved to: {$characterImagePath}");

            // 3. Extract PDF pages
            Log::info("[Story #{$this->storyId}] Extracting pages from PDF...");
            $pageImages = $storyService->extractPages($this->pdfPath, $this->storyId);
            $totalPages = count($pageImages);
            $story->update(['total_pages' => $totalPages]);
            Log::info("[Story #{$this->storyId}] Extracted {$totalPages} pages.");

            // 4. Build array of ProcessPageJob instances (one per page)
            $jobs = [];
            foreach ($pageImages as $index => $pageImagePath) {
                $jobs[] = new ProcessPageJob(
                    storyId: $this->storyId,
                    pageIndex: $index + 1, // 1-based index
                    pageImagePath: $pageImagePath,
                    characterImagePath: $characterImagePath,
                    prompt: $this->prompt
                );
            }

            // 5. Dispatch Bus batch with all page processing jobs
            // IMPORTANT: Use $storyId variable (not $this) so the closure serializes correctly
            $storyId = $this->storyId;
            $childName = $this->childName;

            $batch = Bus::batch($jobs)
                ->then(function () use ($storyId) {
                    Log::info("[Story #{$storyId}] Batch completed.");
                })
                ->catch(function ($batch, Throwable $e) use ($storyId) {
                    Log::warning("[Story #{$storyId}] Some pages failed in batch: " . $e->getMessage());
                })
                ->finally(function () use ($storyId) {
                    Log::info("[Story #{$storyId}] Batch finished (all jobs attempted).");
                })
                ->allowFailures()  // Don't cancel remaining jobs when one fails
                ->name("Story #{$storyId} - Page Processing")
                ->dispatch();

            // 6. Save batch ID on the story record
            $story->update(['batch_id' => $batch->id]);
            Log::info("[Story #{$this->storyId}] Batch dispatched with ID: {$batch->id}");

        } catch (Throwable $e) {
            Log::error("[Story #{$this->storyId}] PrepareStoryJob failed: " . $e->getMessage());
            $story->update(['status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("[Story #{$this->storyId}] PrepareStoryJob failed permanently: " . $exception->getMessage());
        StoryGeneration::where('id', $this->storyId)->update(['status' => 'failed']);
    }
}
