<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\StoryGeneration;
use App\Services\StoryGenerationService;
use Throwable;

class FinalizeStoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $timeout = 300; // 5 minutes for PDF rebuild

    protected int $storyId;
    protected string $childName;

    /**
     * Create a new job instance.
     */
    public function __construct(int $storyId, string $childName)
    {
        $this->storyId = $storyId;
        $this->childName = $childName;
    }

    /**
     * Execute the job.
     */
    public function handle(StoryGenerationService $storyService): void
    {
        try {
            $story = StoryGeneration::findOrFail($this->storyId);

            Log::info("[Story #{$this->storyId}] FinalizeStoryJob started. Collecting generated pages...");

            // 1. Collect all generated page images (sorted by index)
            $generatedPages = $storyService->collectGeneratedPages($this->storyId, $story->total_pages);
            $pagesFound = count($generatedPages);

            Log::info("[Story #{$this->storyId}] Found {$pagesFound} of {$story->total_pages} generated pages.");

            // 2. Handle based on how many pages succeeded
            if ($pagesFound === 0) {
                // Total failure — no pages were processed
                Log::error("[Story #{$this->storyId}] No pages were generated. Marking as failed.");
                $story->update(['status' => 'failed']);
                $storyService->cleanupTempFiles($this->storyId, $story->total_pages);
                return;
            }

            // 3. Rebuild the final PDF with whatever pages we have
            Log::info("[Story #{$this->storyId}] Rebuilding PDF with {$pagesFound} pages...");
            $finalPdfPath = $storyService->rebuildPdf($generatedPages, $this->childName);

            // 4. Determine final status
            $status = ($pagesFound === $story->total_pages) ? 'completed' : 'completed_partial';

            $story->update([
                'output_path' => $finalPdfPath,
                'status' => $status,
            ]);

            if ($status === 'completed_partial') {
                Log::warning("[Story #{$this->storyId}] Story partially completed ({$pagesFound}/{$story->total_pages} pages). PDF saved to: {$finalPdfPath}");
            } else {
                Log::info("[Story #{$this->storyId}] Story generation completed! PDF saved to: {$finalPdfPath}");
            }

            // 5. Delay cleanup to allow any in-flight retrying jobs to finish first
            //    This prevents "file not found" errors on jobs still retrying
            $storyId = $this->storyId;
            $totalPages = $story->total_pages;
            dispatch(function () use ($storyId, $totalPages, $storyService) {
                $storyService->cleanupTempFiles($storyId, $totalPages);
                Log::info("[Story #{$storyId}] Delayed cleanup completed.");
            })->delay(now()->addMinutes(10));

        } catch (Throwable $e) {
            Log::error("[Story #{$this->storyId}] FinalizeStoryJob failed: " . $e->getMessage());
            StoryGeneration::where('id', $this->storyId)->update(['status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("[Story #{$this->storyId}] FinalizeStoryJob failed permanently: " . $exception->getMessage());
        StoryGeneration::where('id', $this->storyId)->update(['status' => 'failed']);
    }
}
