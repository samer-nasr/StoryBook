<?php

namespace App\Services;

use Laravel\Ai\Ai;
use Laravel\Ai\Files\LocalImage;
use Illuminate\Support\Facades\Log;

class AIImageService
{
    /**
     * Generates a storybook-style cartoon character image from a child's photo.
     *
     * @param string $photoPath The path to the uploaded child's photo.
     * @param string $childName The child's name for context in the prompt.
     * @param int    $storyId   The story generation ID for file naming.
     * @return string The path to the generated character image.
     */
    public function generateCharacterImage(string $photoPath, string $childName, int $storyId): string
    {
        // $prompt = "Create a children's storybook illustration of this child.

        //     STRICT REQUIREMENTS:
        //     - Preserve the exact facial features (eyes, nose, mouth, face shape)
        //     - Keep the child's identity clearly recognizable
        //     - Do NOT change age or facial proportions
        //     - Keep hairstyle and hair color accurate
        //     - Maintain natural expression from the original photo

        //     STYLE:
        //     - Soft children's book illustration
        //     - Warm colors, watercolor style
        //     - Clean, simple cartoon rendering

        //     OUTPUT:
        //     - Full body character
        //     - Neutral standing pose
        //     - Centered composition
        //     - Plain or soft background

        //     This character will be reused across multiple story pages, so consistency is critical.";

        // $prompt = "Create a Disney-style animated illustration of this child.
        //     STRICT REQUIREMENTS:
        //     - Preserve the exact facial identity (eyes, nose, mouth, face structure)
        //     - Keep the child clearly recognizable
        //     - Maintain age and natural proportions (do not distort anatomy excessively)
        //     - Keep hairstyle, hair color, and expression accurate
        //     - Do not change identity or facial structure

        //     STYLE:
        //     - High-quality Disney / Pixar-inspired semi-realistic cartoon
        //     - Smooth, clean skin with soft natural shading (no realistic pores)
        //     - Soft, cinematic lighting with gentle highlights and depth
        //     - Rich, vibrant but balanced colors
        //     - Polished digital painting finish
        //     - Subtle stylization: slightly larger eyes, softer cheeks, smoother features
        //     - Optional: slightly larger head proportion (Disney-like) while keeping realism
        //     - Detailed but stylized hair (clean strands, not photorealistic)
        //     - Clothing simplified but still realistic and textured

        //     OUTPUT:
        //     - Full body character
        //     - Neutral standing pose
        //     - Centered composition
        //     - Soft, storybook-style background with depth (not plain, slightly magical/natural)

        //     IMPORTANT:
        //     - Balance between realism and cartoon (do NOT make it too childish or too flat)
        //     - Keep a believable, lifelike feel while clearly being animated
        //     - Ensure consistency for reuse across multiple story pages

        //     GOAL:
        //     Create a realistic animated character that looks like it belongs in a modern Disney or Pixar film.";

        // $prompt = "Create a semi-realistic cartoon illustration of this child.

        //         STRICT REQUIREMENTS:
        //         - Preserve the exact facial features (eyes, nose, mouth, face shape)
        //         - Keep the child’s identity clearly recognizable
        //         - Do NOT change age or core facial proportions
        //         - Keep hairstyle and hair color accurate
        //         - Maintain the natural expression from the original photo

        //         STYLE:
        //         - Semi-realistic cartoon (not Disney, not exaggerated animation)
        //         - Smooth, clean skin with soft natural shading (no pores, no hyper-realism)
        //         - Realistic proportions with only very subtle stylization
        //         - Soft, balanced lighting (natural and slightly warm)
        //         - Clean digital illustration finish (not painterly, not flat)
        //         - Slight simplification of details while keeping depth and volume
        //         - Hair and clothing remain detailed but gently stylized
        //         - No oversized eyes, no caricature exaggeration

        //         OUTPUT:
        //         - Full body character
        //         - Neutral standing pose
        //         - Centered composition
        //         - Soft, minimal background (light gradient or subtle environment)

        //         IMPORTANT:
        //         - Keep the balance closer to realism than cartoon
        //         - The result should feel like a real person rendered in a clean illustrated style
        //         - Avoid any “animated movie” or childish look

        //         CONSISTENCY REQUIREMENTS:
        //         - Ensure the character remains identical across multiple generations
        //         - Maintain the same facial structure, proportions, hairstyle, and colors
        //         - Keep lighting style, rendering quality, and proportions consistent
        //         - Avoid random variations in features, age, or design between outputs

        //         GOAL:
        //         Create a realistic cartoon version of the subject that looks natural, refined, and reusable across multiple story pages with strong visual consistency.";

        // $prompt = "Create a semi-realistic cartoon illustration of this child.

        //     STRICT IDENTITY LOCK (VERY IMPORTANT):
        //     - The face MUST match the original photo exactly
        //     - Preserve exact eye shape, eye size, and spacing (DO NOT enlarge or stylize eyes)
        //     - Preserve exact nose shape and size (do not simplify)
        //     - Preserve exact mouth shape and smile
        //     - Preserve exact face shape (no rounding or slimming)
        //     - Preserve ear shape and position
        //     - Keep head-to-body proportions identical (no oversized head)
        //     - Maintain the exact hairstyle, hairline, and hair color

        //     STYLE:
        //     - Semi-realistic cartoon (minimal stylization)
        //     - Very light smoothing of skin only (keep natural structure and shading)
        //     - Keep realistic facial anatomy and proportions
        //     - Clean digital illustration finish with subtle softness
        //     - Natural colors (avoid overly saturated or “cute” tones)
        //     - Lighting should remain realistic and close to the original photo

        //     BODY & DETAILS:
        //     - Keep clothing exactly the same (colors, folds, proportions)
        //     - Keep posture and pose identical to the original image
        //     - Do NOT simplify hands or body structure

        //     OUTPUT:
        //     - Full body character
        //     - Same pose as original image (NOT neutral if original is different)
        //     - Centered composition
        //     - Very soft, minimal background (no strong stylization)

        //     CONSISTENCY REQUIREMENTS:
        //     - Ensure identical character reproduction across multiple generations
        //     - No variation in facial features, proportions, or identity
        //     - Use the original image as a strict reference for all future outputs

        //     AVOID:
        //     - Big eyes, rounded baby face, or “cute” exaggeration
        //     - Oversized head or cartoon proportions
        //     - Changing expression or emotion
        //     - Simplifying facial features
        //     - Disney / Pixar / anime style

        //     GOAL:
        //     Create a clean, semi-realistic cartoon version that looks like the SAME child, not a reinterpretation.
        //     It should feel like a lightly illustrated version of the real photo, not a redesigned character.";

//         $prompt = "Create a children's storybook illustration based on the provided photo.

// STRICT IDENTITY PRESERVATION (HIGHEST PRIORITY):
// - Carefully analyze the original image before generating
// - The result must be a stylized version of the SAME person, not a different or generic character
// - Preserve exact facial structure: face shape, cheeks, jawline, chin, and proportions
// - Maintain the original head-to-body ratio (do NOT exaggerate head size unless it exists in the original)

// EYES (CRITICAL — PRIMARY IDENTITY FEATURE):
// - Replicate the exact eye shape, size, spacing, and alignment
// - Preserve eyelid structure (upper lid fold, lower lid softness)
// - Maintain the same gaze direction and natural expression
// - Keep iris size, pupil position, and light reflections consistent
// - Preserve natural asymmetry (do NOT over-symmetrize)
// - Do NOT oversimplify, enlarge, or stylize eyes excessively
// - Eyes must retain depth, realism, and emotional presence from the original photo

// FACIAL DETAILS:
// - Keep exact nose shape, proportions, and structure
// - Preserve mouth shape, smile curve, and lip proportions
// - Maintain original expression without exaggeration
// - Retain natural facial volume and softness (no flattening or reshaping)

// HAIR:
// - Match hairstyle, hairline, density, and direction exactly
// - Preserve original color and texture

// STYLE:
// - Children's book illustration
// - Soft watercolor or clean cartoon rendering
// - Minimal stylization — identity must dominate over style
// - Avoid generic cartoon faces

// OUTPUT:
// - Full body character
// - Neutral or natural pose matching the original energy
// - Centered composition
// - Soft or plain background

// CONSISTENCY REQUIREMENT:
// - This character may be reused across multiple illustrations
// - Ensure consistent face, proportions, and especially eye structure across generations

// FINAL RULE:
// - If the result looks like a different person, it is incorrect
// - Identity accuracy (especially eyes and face) overrides artistic style.";

$prompt = "Create a personalized children's storybook character from the provided real photo.

STRICT IDENTITY PRESERVATION (HIGHEST PRIORITY):
- The generated character must look EXACTLY like the real person in the photo
- Do NOT create a generic or different face — this must be the SAME child transformed into a cartoon
- Preserve exact facial structure: face shape, cheeks, jawline, chin, forehead proportions
- Maintain accurate head size and proportions relative to the body

EYES (MOST IMPORTANT FEATURE):
- Precisely replicate eye shape, size, spacing, and alignment
- Preserve eyelids, gaze direction, and natural expression
- Maintain iris size, pupil placement, and light reflections
- Keep natural asymmetry — do NOT over-perfect or stylize
- Do NOT enlarge or oversimplify the eyes

FACIAL DETAILS:
- Keep exact nose structure and proportions
- Preserve mouth shape and expression
- Maintain the SAME personality and emotion

HAIR:
- Match hairstyle, hairline, density, and direction exactly
- Keep original color and texture

STYLE:
- High-quality 3D cartoon / Pixar-like rendering
- Soft cinematic lighting
- Smooth shading with realistic depth
- Slight stylization but strong identity accuracy

COMPOSITION (UPDATED):
- Full body character
- Neutral standing pose
- Centered in frame
- Clean edges and fully visible silhouette

BACKGROUND (VERY IMPORTANT):
- NO background scene
- NO environment
- NO objects
- The character must be isolated

OUTPUT REQUIREMENT (CRITICAL):
- Transparent background (PNG with alpha channel)
- The character must be cleanly cut out
- No shadows, no gradients, no floor
- No colored or blurred background
- Only the character visible

CONSISTENCY REQUIREMENT:
- Character must remain identical across multiple generations

FINAL RULE:
- Output must be a clean, isolated character ready to be placed into other images.";

        Log::info("[Story #{$storyId}] Calling AI to generate character image for {$childName}...");

        $response = Ai::image($prompt, [new LocalImage($photoPath)]);
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
     * @return string The path to the edited page image.
     */
    public function replaceCharacterInPage(string $pageImagePath, string $characterImagePath, string $prompt, int $storyId, int $pageIndex): string
    {
        Log::info("[Story #{$storyId}] Calling AI to process page {$pageIndex}...");

        $response = Ai::image($prompt, [
            new LocalImage($pageImagePath),
            new LocalImage($characterImagePath)
        ]);

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
