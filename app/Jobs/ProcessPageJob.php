<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\StoryGeneration;
use App\Services\AIImageService;
use Throwable;

class ProcessPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    public $timeout = 600; // 10 minutes per page

    protected int $storyId;
    protected int $pageIndex;
    protected string $pageImagePath;
    protected string $characterImagePath;
    protected string $prompt;

    /**
     * Create a new job instance.
     */
    public function __construct(int $storyId, int $pageIndex, string $pageImagePath, string $characterImagePath, string $prompt)
    {
        $this->storyId = $storyId;
        $this->pageIndex = $pageIndex;
        $this->pageImagePath = $pageImagePath;
        $this->characterImagePath = $characterImagePath;
        $this->prompt = $prompt;
    }

    /**
     * Execute the job.
     */
    public function handle(AIImageService $aiService): void
    {
        // Check if the batch has been cancelled
        if ($this->batch() && $this->batch()->cancelled()) {
            Log::info("[Story #{$this->storyId}] Batch cancelled. Skipping page {$this->pageIndex}.");
            return;
        }

        try {
            Log::info("[Story #{$this->storyId}] ProcessPageJob started for page {$this->pageIndex}.");

            // Call AI to replace the character in this page
            $aiService->replaceCharacterInPage(
                $this->pageImagePath,
                $this->characterImagePath,
                $this->prompt,
                $this->storyId,
                $this->pageIndex
            );

            // Atomically increment processed pages (safe for concurrent workers)
            $story = StoryGeneration::findOrFail($this->storyId);
            $story->incrementProcessedPages();

            Log::info("[Story #{$this->storyId}] Page {$this->pageIndex} processed successfully.");

        } catch (Throwable $e) {
            Log::error("[Story #{$this->storyId}] ProcessPageJob failed for page {$this->pageIndex}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("[Story #{$this->storyId}] ProcessPageJob failed permanently for page {$this->pageIndex}: " . $exception->getMessage());
    }
}
