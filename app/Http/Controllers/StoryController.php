<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\StoryGeneration;
use App\Jobs\PrepareStoryJob;

use App\Models\StoryTemplate;

class StoryController extends Controller
{
    public function create()
    {
        $templates = StoryTemplate::all();
        return view('story.create', compact('templates'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:story_templates,id',
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:1|max:12',
            'photo' => 'required|image|max:10240', // Max 10MB
        ]);

        $template = StoryTemplate::findOrFail($request->template_id);

        // Store the uploaded photo temporarily
        $photoName = 'child_' . time() . '.' . $request->file('photo')->getClientOriginalExtension();
        $tempPhotoPath = $request->file('photo')->storeAs('temp', $photoName, 'local');
        $fullTempPhotoPath = Storage::disk('local')->path($tempPhotoPath);

        // Load correct PDF path from selected template
        $pdfPath = storage_path('app/public/' . $template->file_path);

        // $prompt = "Edit this storybook page image by REPLACING the main baby/child character with the character from the second reference image.

        //             CRITICAL - REPLACEMENT, NOT ADDITION:
        //             - REMOVE the original baby/child character completely from the scene
        //             - Place the new character (from the reference image) in the EXACT same position
        //             - The final image must contain ONLY ONE child character — the new one
        //             - Do NOT keep both the old and new characters — the old one must be gone
        //             - If there are animals, toys, or other non-human characters, KEEP them unchanged

        //             CHARACTER IDENTITY:
        //             - The replacement character must match the reference image exactly
        //             - Preserve the exact facial features, hairstyle, and proportions from the reference
        //             - Do NOT redesign or reinterpret the character

        //             SCENE PRESERVATION:
        //             - Keep the background, colors, lighting, and art style identical
        //             - Keep all animals, objects, and decorations unchanged
        //             - Keep the original illustration style exactly the same
        //             - Only the baby/child character should change — nothing else

        //             POSE AND POSITION:
        //             - The new character should adopt the same pose as the original baby character
        //             - Match the same position, angle, and scale in the scene

        //             FINAL CHECK:
        //             - Count the human characters: there should be exactly ONE child in the output
        //             - That child must be the one from the reference image, not the original";

        // $prompt = "Edit this storybook page image by replacing ONLY the face of the main baby/child character with the face from the reference image.

        //     CRITICAL - FACE REPLACEMENT ONLY:
        //     - Replace ONLY the face (eyes, nose, mouth, facial structure)
        //     - Do NOT modify the body, clothes, pose, or proportions
        //     - Do NOT move or reposition the character
        //     - The original body must remain exactly the same
        //     - Blend the new face naturally onto the existing head

        //     CHARACTER IDENTITY:
        //     - The new face must match the reference image exactly
        //     - Preserve exact facial features: eyes, nose, mouth, face shape
        //     - Maintain the same expression as much as possible
        //     - Do NOT redesign or stylize differently

        //     SCENE PRESERVATION:
        //     - Keep background, colors, lighting, and art style unchanged
        //     - Keep all objects, animals, and elements unchanged
        //     - Do NOT modify anything outside the face area

        //     BLENDING AND INTEGRATION:
        //     - Ensure smooth and natural blending between face and head
        //     - Match skin tone, lighting, and shadows to the original scene
        //     - Avoid visible seams, edges, or mismatched colors

        //     CONSTRAINTS:
        //     - Do NOT alter hair, unless needed for natural blending
        //     - Do NOT change head shape or size
        //     - Do NOT affect resolution or image quality

        //     FINAL CHECK:
        //     - Only the face should be changed
        //     - Everything else must remain identical to the original image";

        // $prompt = "Edit this storybook page image by replacing ONLY the face of the main baby/child character with the face from the reference image.

        //         CRITICAL - FACE REPLACEMENT ONLY:
        //         - Replace ONLY the face (eyes, nose, mouth, facial structure)
        //         - Do NOT modify the body, clothes, pose, or proportions
        //         - Do NOT move or reposition the character
        //         - The original body must remain exactly the same
        //         - Blend the new face naturally onto the existing head

        //         CHARACTER IDENTITY (STRICT):
        //         - The new face MUST match the reference image exactly
        //         - Preserve exact facial features: eyes, nose, mouth, face shape
        //         - Maintain the same expression as much as possible
        //         - Do NOT redesign, stylize, or reinterpret the face

        //         EYE COLOR (VERY IMPORTANT - STRICT REQUIREMENT):
        //         - The eye color MUST EXACTLY match the reference image
        //         - Extract the exact eye color from the reference and reproduce it identically
        //         - Do NOT approximate or change the eye color under any circumstance
        //         - Do NOT blend or average eye color with the original character
        //         - Eye color must remain consistent, clear, and unchanged after blending

        //         SCENE PRESERVATION:
        //         - Keep background, colors, lighting, and art style unchanged
        //         - Keep all objects, animals, and elements unchanged
        //         - Do NOT modify anything outside the face area

        //         BLENDING AND INTEGRATION:
        //         - Ensure smooth and natural blending between face and head
        //         - Match skin tone, lighting, and shadows to the original scene
        //         - Avoid visible seams, edges, or mismatched colors

        //         CONSTRAINTS:
        //         - Do NOT alter hair, unless required for natural blending
        //         - Do NOT change head shape or size
        //         - Do NOT affect resolution or image quality

        //         FINAL CHECK (STRICT):
        //         - Only the face should be changed
        //         - Eye color MUST exactly match the reference image
        //         - Everything else must remain identical to the original image";

        $prompt = "Edit this storybook page image by replacing ONLY the face of the main baby/child character with the face from the reference image.
                CRITICAL - FACE REPLACEMENT ONLY:
                -Replace ONLY the face (eyes, nose, mouth, facial structure)
                -Do NOT modify the body, clothes, pose, or proportions
                -Do NOT move or reposition the character
                -The original body must remain exactly the same
                -Blend the new face naturally onto the existing head

                CHARACTER IDENTITY (STRICT):
                -The new face MUST match the reference image exactly
                -Preserve exact facial features: eyes, nose, mouth, face shape
                -Maintain the same expression as much as possible
                -Do NOT redesign, stylize, or reinterpret the face

                EYE COLOR (NON-NEGOTIABLE):
                -The eye color MUST EXACTLY match the reference image
                -Extract the precise eye color and reproduce it identically
                -Do NOT alter, blend, or change the eye color under any circumstance
                -Eye color must remain clear and consistent

                HAIR CONSISTENCY (CRITICAL - MUST ENFORCE):
                -The hair color and hairstyle MUST EXACTLY match the reference image
                -Reproduce the exact hair color, style, and texture from the reference
                -Do NOT change, lighten, or restyle the hair in any way
                -Hair must appear identical to the reference image

                SCENE PRESERVATION:
                -Keep background, colors, lighting, and art style unchanged
                -Keep all objects, animals, and elements unchanged
                -Do NOT modify anything outside the face area

                BLENDING AND INTEGRATION:
                -Ensure smooth and natural blending between face and head
                -Match skin tone, lighting, and shadows to the original scene
                -Avoid visible seams, edges, or mismatched colors

                CONSTRAINTS:
                -Do NOT alter hair or head shape
                -Do NOT affect resolution or image quality";

        // Create a StoryGeneration record to track progress
        $story = StoryGeneration::create([
            'template_id' => $template->id,
            'name' => $validated['name'],
            'status' => 'pending',
            'total_pages' => 0,
            'processed_pages' => 0,
            'pdf_path' => $pdfPath,
            'prompt' => $prompt,
            'config' => [
                'version' => 1,
                'seed' => mt_rand(10000000, 99999999),
                'system_role' => config('constant.prompts.system_role'),
                'strict_rules' => config('constant.prompts.strict_rules'),
                'output_rules' => config('constant.prompts.output_rules'),
                
                'style_block' => config('constant.prompts.defaults.style_block'),
                'identity_block' => config('constant.prompts.defaults.identity_block'),
                
                'character_generation_task' => config('constant.prompts.character_generation.task'),
                'character_generation_constraints' => config('constant.prompts.character_generation.constraints'),
                
                'page_generation_task' => config('constant.prompts.page_generation.task'),
                'page_generation_constraints' => config('constant.prompts.page_generation.constraints'),
            ]
        ]);

        // Dispatch the PrepareStoryJob (which orchestrates the entire parallel pipeline)
        PrepareStoryJob::dispatch(
            storyId: $story->id,
            photoPath: $fullTempPhotoPath,
            childName: $validated['name'],
            pdfPath: $pdfPath,
            prompt: $prompt
        );

        // Redirect to the processing page instead of JSON API response
        return redirect()->route('story.processing', $story->id);
    }

    public function examples()
    {
        $files = Storage::disk('public')->files('stories'); // Adjusted to match where files are actually saved
        
        // Strip the folder path to just pass filenames for the examples gallery
        $files = array_map('basename', $files);

        return view('examples', compact('files'));
    }

    public function processing(StoryGeneration $story)
    {
        return view('story.processing', compact('story'));
    }

    public function show(StoryGeneration $story)
    {
        return view('story.show', compact('story'));
    }

    /**
     * Get the current progress/status of a story generation.
     */
    public function status(int $story)
    {
        $storyRecord = StoryGeneration::findOrFail($story);

        return response()->json([
            'id' => $storyRecord->id,
            'name' => $storyRecord->name,
            'status' => $storyRecord->status,
            'total_pages' => $storyRecord->total_pages,
            'processed_pages' => $storyRecord->processed_pages,
            'output_path' => $storyRecord->output_path,
            'progress' => $storyRecord->total_pages > 0
                ? round(($storyRecord->processed_pages / $storyRecord->total_pages) * 100, 1)
                : 0,
        ]);
    }
}
