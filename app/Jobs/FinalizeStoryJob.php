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

            Log::info("[Story #{$this->storyId}] FinalizeStoryJob started. Rebuilding PDF...");

            // 1. Collect all generated page images (sorted by index)
            $generatedPages = $storyService->collectGeneratedPages($this->storyId, $story->total_pages);

            if (count($generatedPages) !== $story->total_pages) {
                Log::warning("[Story #{$this->storyId}] Expected {$story->total_pages} pages but found " . count($generatedPages) . ". Proceeding with available pages.");
            }

            // 2. Rebuild the final PDF
            $finalPdfPath = $storyService->rebuildPdf($generatedPages, $this->childName);

            // 3. Update story record with output path and mark as completed
            $story->update([
                'output_path' => $finalPdfPath,
                'status' => 'completed',
            ]);

            Log::info("[Story #{$this->storyId}] Story generation completed! PDF saved to: {$finalPdfPath}");

            // 4. Clean up temporary files (extracted PDF pages + generated page images)
            $storyService->cleanupTempFiles($this->storyId, $story->total_pages);

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
