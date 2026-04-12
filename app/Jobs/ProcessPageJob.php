<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StoryGeneration;
use App\Services\AIImageService;
use Throwable;

class ProcessPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $timeout = 600; // 10 minutes per page

    /**
     * Number of times the job may be attempted.
     */
    public $tries = 5;

    /**
     * Only fail permanently after this many actual exceptions.
     */
    public $maxExceptions = 3;

    /**
     * Exponential backoff intervals (in seconds) between retries.
     */
    public $backoff = [30, 60, 120, 240];

    protected int $storyId;
    protected int $pageIndex; // Represents the Grid Index computationally
    protected string $pageImagePath;
    protected string $characterImagePath;
    protected string $prompt;
    protected array $pageIndices;

    public function __construct(int $storyId, int $pageIndex, string $pageImagePath, string $characterImagePath, string $prompt, array $pageIndices = [])
    {
        $this->storyId = $storyId;
        $this->pageIndex = $pageIndex;
        $this->pageImagePath = $pageImagePath;
        $this->characterImagePath = $characterImagePath;
        $this->prompt = $prompt;
        $this->pageIndices = $pageIndices;
    }

    public function middleware(): array
    {
        return [
            (new ThrottlesExceptions(2, 60))->backoff(1),
        ];
    }

    public function handle(AIImageService $aiService, \App\Services\StoryGenerationService $storyService): void
    {
        // GUARD 1: Stop if the story is already finalized/completed/failed
        $story = StoryGeneration::find($this->storyId);
        if (!$story || in_array($story->status, ['finalizing', 'completed', 'completed_partial', 'failed'])) {
            Log::info("[Story #{$this->storyId}] Story already {$story?->status}. Skipping grid {$this->pageIndex}.");
            return;
        }

        // GUARD 2: Skip if batch cancelled
        if ($this->batch() && $this->batch()->cancelled()) {
            Log::info("[Story #{$this->storyId}] Batch cancelled. Skipping grid {$this->pageIndex}.");
            return;
        }

        // GUARD 3: Check source files exist
        if (!file_exists($this->pageImagePath) || !file_exists($this->characterImagePath)) {
            Log::warning("[Story #{$this->storyId}] Source files missing for grid {$this->pageIndex}. Skipping.");
            return;
        }

        try {
            Log::info("[Story #{$this->storyId}] ProcessPageJob started for grid {$this->pageIndex} (attempt {$this->attempts()}).");

            // Process the composite 1024x1024 grid
            $editedGridPath = $aiService->replaceCharacterInGrid(
                $this->pageImagePath,
                $this->characterImagePath,
                $this->storyId,
                $this->pageIndex, // Which acts as Grid Index here
                $story->config ?? []
            );

            Log::info("[Story #{$this->storyId}] Grid {$this->pageIndex} processed successfully. Splitting chunks natively...");

            // Directly run the chunk-splitter logic parsing the 1024 grid back into native 512 images mathematically tracking original page indices
            $storyService->splitGrid($editedGridPath, $this->storyId, $this->pageIndices);

            // Clean up the 1024x1024 grid since chunks are safe via automated file bounds mapping
            // @unlink($editedGridPath);

            // Check if all pages are done by counting actual files
            $this->checkAndTriggerFinalization();

        } catch (Throwable $e) {
            Log::error("[Story #{$this->storyId}] ProcessPageJob failed for page {$this->pageIndex} (attempt {$this->attempts()}): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if ALL pages exist on disk, and if so, atomically dispatch FinalizeStoryJob.
     * Uses file system as source of truth (not a counter that can double-increment).
     */
    protected function checkAndTriggerFinalization(): void
    {
        $story = StoryGeneration::find($this->storyId);

        if (!$story || $story->status !== 'processing') {
            return;
        }

        // Count actual files on disk — this is the ONLY reliable way
        $existingPages = 0;
        for ($i = 1; $i <= $story->total_pages; $i++) {
            if (file_exists(storage_path("app/generated_pages/story_{$this->storyId}_page_{$i}.png"))) {
                $existingPages++;
            }
        }

        // Update processed_pages to reflect actual count (for status API)
        if ($existingPages !== $story->processed_pages) {
            DB::table('story_generations')
                ->where('id', $this->storyId)
                ->update(['processed_pages' => $existingPages]);
        }

        if ($existingPages < $story->total_pages) {
            return;
        }

        // Atomic status transition — only ONE worker can win this race
        $updated = DB::table('story_generations')
            ->where('id', $this->storyId)
            ->where('status', 'processing')
            ->update(['status' => 'finalizing']);

        if ($updated) {
            Log::info("[Story #{$this->storyId}] All {$story->total_pages} pages confirmed on disk. Dispatching FinalizeStoryJob...");
            FinalizeStoryJob::dispatch($this->storyId, $story->name);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("[Story #{$this->storyId}] ProcessPageJob PERMANENTLY failed for page {$this->pageIndex}: " . $exception->getMessage());
    }
}
