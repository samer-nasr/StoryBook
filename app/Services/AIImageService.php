<?php

namespace App\Services;

use Laravel\Ai\Ai;
use Laravel\Ai\Files\LocalImage;
use Illuminate\Support\Str;

class AIImageService
{
    /**
     * Generates a storybook-style cartoon character image from a child's photo.
     *
     * @param string $photoPath The path to the uploaded child's photo.
     * @param string $childName The child's name for context in the prompt.
     * @return string The URL or path to the generated character image.
     */
    public function generateCharacterImage(string $photoPath, string $childName): string
    {
        $prompt = "A cute illustrated baby character in children's storybook style based on this child photo, soft colors, friendly face, cartoon illustration suitable for a kids book. The character represents a child named {$childName}.";

        $response = Ai::image($prompt, [new LocalImage($photoPath)]);
        $generatedImage = $response->firstImage();

        $filename = 'character_' . Str::random(10) . '.png';
        $savePath = storage_path('app/generated_characters/' . $filename);
        
        file_put_contents($savePath, $generatedImage->content());

        return $savePath;
    }

    /**
     * Replaces a character in a page image with the generated child character.
     *
     * @param string $pageImagePath The path to the PDF page image.
     * @param string $characterImagePath The path to the generated child character image.
     * @param string $prompt The specific prompt for replacement.
     * @return string The URL or path to the edited page image.
     */
    public function replaceCharacterInPage(string $pageImagePath, string $characterImagePath, string $prompt): string
    {
        $response = Ai::image($prompt, [
            new LocalImage($pageImagePath),
            new LocalImage($characterImagePath)
        ]);
        
        $generatedImage = $response->firstImage();

        $outputDirectory = storage_path('app/generated_pages');
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $filename = 'page_' . Str::random(10) . '.png';
        $savePath = $outputDirectory . '/' . $filename;
        
        file_put_contents($savePath, $generatedImage->content());

        return $savePath;
    }
}
