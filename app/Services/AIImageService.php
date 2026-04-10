<?php

namespace App\Services;

use Laravel\Ai\Ai;
use Laravel\Ai\Files\LocalImage;
use Illuminate\Support\Facades\Log;

class AIImageService
{
    private function buildStructuredPrompt(array $config): string
    {
        $systemRole = $config['system_role'] ?? config('constant.prompts.system_role');
        $strictRules = $config['strict_rules'] ?? config('constant.prompts.strict_rules');
        $identityBlock = $config['identity_block'] ?? '';
        $styleBlock = $config['style_block'] ?? '';
        $task = $config['task'] ?? '';
        $constraints = $config['constraints'] ?? '';
        $outputRules = $config['output_rules'] ?? config('constant.prompts.output_rules');

        return <<<PROMPT
[SYSTEM ROLE]
{$systemRole}

[STRICT RULES]
{$strictRules}

[IDENTITY BLOCK]
{$identityBlock}

[STYLE BLOCK]
{$styleBlock}

[TASK]
{$task}

[CONSTRAINTS]
{$constraints}

[OUTPUT RULES]
{$outputRules}
PROMPT;
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
        $config['task'] = $config['character_generation_task'] ?? config('constant.prompts.character_generation.task');
        $config['constraints'] = $config['character_generation_constraints'] ?? config('constant.prompts.character_generation.constraints');

        $prompt = $this->buildStructuredPrompt($config);

        Log::info("[Story #{$storyId}] Calling AI to generate character image for {$childName}... (Seed: " . ($config['seed'] ?? 'none') . ")");
        Log::info("Character Generation Prompt:\n{$prompt}");

        // Note: seed parameter mapping for SDK overrides
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

        $config['task'] = $config['page_generation_task'] ?? config('constant.prompts.page_generation.task');
        $config['constraints'] = $config['page_generation_constraints'] ?? config('constant.prompts.page_generation.constraints');

        $structuredPrompt = $this->buildStructuredPrompt($config);

        Log::info("Page Generation Prompt for page {$pageIndex}:\n{$structuredPrompt}");

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
}
