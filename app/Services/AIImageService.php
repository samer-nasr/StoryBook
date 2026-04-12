<?php

namespace App\Services;

use Laravel\Ai\Ai;
use Laravel\Ai\Files\LocalImage;
use Illuminate\Support\Facades\Log;

class AIImageService
{
    public function __construct(protected \App\Services\PromptService $promptService)
    {
    }

    /**
     * Generates a storybook-style cartoon character image from a child's photo.
     *
     * @param string $photoPath The path to the uploaded child's photo.
     * @param string $childName The child's name for context in the prompt.
     * @param int    $storyId   The story generation ID for file naming.
     * @return string The path to the generated character image.
     */
    public function generateCharacterImage(string $photoPath, string $childName, int $storyId, array $config = []): string
    {
        $prompt = $this->promptService->getPrompt('character_generation', $config);

        Log::info("[Story #{$storyId}] Calling AI to generate character image for {$childName}... (Seed: " . ($config['seed'] ?? 'none') . ")");
        // Log::info("Character Generation Prompt:\n{$prompt}");

        $imageParams = [
            'prompt' => $prompt, 
            'attachments' => [new LocalImage($photoPath)],
            'timeout' => 300
        ];

        // Currently bypassing direct seed injection if the SDK throws unknown parameter blocks
        // We will pass it dynamically if the SDK gets updated or build a raw endpoint call if necessary.
        $response = Ai::image(...$imageParams);
        $generatedImage = $response->firstImage();

        $outputDirectory = storage_path('app/characters');
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $filename = "character_{$storyId}.png";
        $savePath = $outputDirectory . '/' . $filename;

        file_put_contents($savePath, $generatedImage->content());

        Log::info("[Story #{$storyId}] Character image saved to: {$savePath}");

        // dd($savePath);
        return $savePath;
    }

    /**
     * Replaces a character in a page image with the generated child character.
     *
     * @param string $pageImagePath      The path to the PDF page image.
     * @param string $characterImagePath The path to the generated child character image.
     * @param string $prompt             The specific prompt for replacement.
     * @param int    $storyId            The story generation ID for file naming.
     * @param int    $pageIndex          The page index for deterministic naming.
     * @param array  $config             Configuration options including seed.
     * @return string The path to the edited page image.
     */
    public function replaceCharacterInPage(string $pageImagePath, string $characterImagePath, string $prompt, int $storyId, int $pageIndex, array $config = []): string
    {
        Log::info("[Story #{$storyId}] Calling AI to process page {$pageIndex}... (Seed: " . ($config['seed'] ?? 'none') . ")");

        $structuredPrompt = $this->promptService->getPrompt('page_generation', $config);

        // Log::info("Page Generation Prompt for page {$pageIndex}:\n{$structuredPrompt}");

        $imageParams = [
            'prompt' => $structuredPrompt, 
            'attachments' => [
                new LocalImage($pageImagePath),
                new LocalImage($characterImagePath)
            ],
            'timeout' => 300
        ];

        $response = Ai::image(...$imageParams);

        $generatedImage = $response->firstImage();

        $outputDirectory = storage_path('app/generated_pages');
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $filename = "story_{$storyId}_page_{$pageIndex}.png";
        $savePath = $outputDirectory . '/' . $filename;

        file_put_contents($savePath, $generatedImage->content());

        Log::info("[Story #{$storyId}] Processed page {$pageIndex} saved to: {$savePath}");

        return $savePath;
    }

    /**
     * Executes the identical AI replacement logic natively grouping 4 pages bound directly into a single 1024x1024 grid
     */
    public function replaceCharacterInGrid(string $gridImagePath, string $characterImagePath, int $storyId, int $gridIndex, array $config = []): string
    {
        Log::info("[Story #{$storyId}] Calling AI to process GRID {$gridIndex} (Cost Batching)... (Seed: " . ($config['seed'] ?? 'none') . ")");

        // Use the full static grid prompt from constants — no dynamic interpolation
        $gridPrompt = \App\Constants\Prompts::GRID_FACE_REPLACEMENT;

        $imageParams = [
            'prompt' => $gridPrompt, 
            'attachments' => [
                new LocalImage($gridImagePath),
                new LocalImage($characterImagePath)
            ],
            'timeout' => 300
        ];

        $response = Ai::image(...$imageParams);
        $generatedImage = $response->firstImage();

        $outputDirectory = storage_path('app/generated_pages');
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $filename = "story_{$storyId}_grid_{$gridIndex}_edited.png";
        $savePath = $outputDirectory . '/' . $filename;

        file_put_contents($savePath, $generatedImage->content());

        Log::info("[Story #{$storyId}] Processed GRID {$gridIndex} saved securely to: {$savePath}");

        return $savePath;
    }
}
