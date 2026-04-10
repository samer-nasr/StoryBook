<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
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
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

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
    protected int $pageIndex;
    protected string $pageImagePath;
    protected string $characterImagePath;
    protected string $prompt;

    public function __construct(int $storyId, int $pageIndex, string $pageImagePath, string $characterImagePath, string $prompt)
    {
        $this->storyId = $storyId;
        $this->pageIndex = $pageIndex;
        $this->pageImagePath = $pageImagePath;
        $this->characterImagePath = $characterImagePath;
        $this->prompt = $prompt;
    }

    public function middleware(): array
    {
        return [
            (new ThrottlesExceptions(2, 60))->backoff(1),
        ];
    }

    public function handle(AIImageService $aiService): void
    {
        // GUARD 1: Stop if the story is already finalized/completed/failed
        $story = StoryGeneration::find($this->storyId);
        if (!$story || in_array($story->status, ['finalizing', 'completed', 'completed_partial', 'failed'])) {
            Log::info("[Story #{$this->storyId}] Story already {$story?->status}. Skipping page {$this->pageIndex}.");
            return;
        }

        // GUARD 2: Skip if batch cancelled
        if ($this->batch() && $this->batch()->cancelled()) {
            Log::info("[Story #{$this->storyId}] Batch cancelled. Skipping page {$this->pageIndex}.");
            return;
        }

        // GUARD 3: Skip if this page already exists (prevents duplicate API calls)
        $outputPath = storage_path("app/generated_pages/story_{$this->storyId}_page_{$this->pageIndex}.png");
        if (file_exists($outputPath)) {
            Log::info("[Story #{$this->storyId}] Page {$this->pageIndex} already exists. Skipping.");
            $this->checkAndTriggerFinalization();
            return;
        }

        // GUARD 4: Check source files exist
        if (!file_exists($this->pageImagePath) || !file_exists($this->characterImagePath)) {
            Log::warning("[Story #{$this->storyId}] Source files missing for page {$this->pageIndex}. Skipping.");
            return;
        }

        try {
            Log::info("[Story #{$this->storyId}] ProcessPageJob started for page {$this->pageIndex} (attempt {$this->attempts()}).");

            $aiService->replaceCharacterInPage(
                $this->pageImagePath,
                $this->characterImagePath,
                $this->prompt,
                $this->storyId,
                $this->pageIndex,
                $story->config ?? []
            );

            Log::info("[Story #{$this->storyId}] Page {$this->pageIndex} processed successfully.");

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
