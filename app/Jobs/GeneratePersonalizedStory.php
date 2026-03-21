<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\AIImageService;
use App\Services\StoryGenerationService;

class GeneratePersonalizedStory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // Allow 10 minutes for generation

    protected $tempPhotoPath;
    protected $childName;
    protected $pdfPath;
    protected $prompt;

    /**
     * Create a new job instance.
     */
    public function __construct(string $tempPhotoPath, string $childName, string $pdfPath, string $prompt)
    {
        $this->tempPhotoPath = $tempPhotoPath;
        $this->childName = $childName;
        $this->pdfPath = $pdfPath;
        $this->prompt = $prompt;
    }

    /**
     * Execute the job.
     */
    public function handle(AIImageService $aiService, StoryGenerationService $storyService): void
    {
        try {
            Log::info("Starting story generation for {$this->childName}");

            // 1. Generate the Character Image
            Log::info("Generating character image...");
            $characterImagePath = $aiService->generateCharacterImage($this->tempPhotoPath, $this->childName);

            // 2. Extract PDF Pages
            Log::info("Extracting pages from original PDF...");
            $originalPages = $storyService->extractPages($this->pdfPath);

            // 3. Process Each Page
            $editedPages = [];
            foreach ($originalPages as $index => $pagePath) {
                Log::info("Processing page " . ($index + 1) . " of " . count($originalPages) . "...");
                
                $editedPath = $aiService->replaceCharacterInPage(
                    $pagePath, 
                    $characterImagePath, 
                    $this->prompt
                );
                
                $editedPages[] = $editedPath;
            }

            // 4. Rebuild the PDF
            Log::info("Rebuilding final PDF...");
            $finalPdfPath = $storyService->rebuildPdf($editedPages, $this->childName);

            Log::info("Story generation complete. Final PDF saved to: {$finalPdfPath}");

            // Clean up original extracted pages
            foreach ($originalPages as $page) {
                @unlink($page);
            }

            // Note: We might want to keep the $finalPdfPath or notify the user via a broadcast/event here
            
        } catch (\Exception $e) {
            Log::error("Story generation failed: " . $e->getMessage());
            throw $e;
        }
    }
}
