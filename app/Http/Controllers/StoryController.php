<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\StoryGeneration;
use App\Jobs\PrepareStoryJob;

class StoryController extends Controller
{
    public function create()
    {
        return view('story.create');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:1|max:12',
            'photo' => 'required|image|max:10240', // Max 10MB
        ]);

        // Store the uploaded photo temporarily
        $photoName = 'child_' . time() . '.' . $request->file('photo')->getClientOriginalExtension();
        $tempPhotoPath = $request->file('photo')->storeAs('temp', $photoName, 'local');
        $fullTempPhotoPath = Storage::disk('local')->path($tempPhotoPath);

        // $pdfPath = base_path('Design sans titre.pdf');
        $pdfPath = base_path('t1.pdf');

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

        $prompt = "Edit this storybook page image by replacing ONLY the face of the main baby/child character with the face from the reference image.

            CRITICAL - FACE REPLACEMENT ONLY:
            - Replace ONLY the face (eyes, nose, mouth, facial structure)
            - Do NOT modify the body, clothes, pose, or proportions
            - Do NOT move or reposition the character
            - The original body must remain exactly the same
            - Blend the new face naturally onto the existing head

            CHARACTER IDENTITY:
            - The new face must match the reference image exactly
            - Preserve exact facial features: eyes, nose, mouth, face shape
            - Maintain the same expression as much as possible
            - Do NOT redesign or stylize differently

            SCENE PRESERVATION:
            - Keep background, colors, lighting, and art style unchanged
            - Keep all objects, animals, and elements unchanged
            - Do NOT modify anything outside the face area

            BLENDING AND INTEGRATION:
            - Ensure smooth and natural blending between face and head
            - Match skin tone, lighting, and shadows to the original scene
            - Avoid visible seams, edges, or mismatched colors

            CONSTRAINTS:
            - Do NOT alter hair, unless needed for natural blending
            - Do NOT change head shape or size
            - Do NOT affect resolution or image quality

            FINAL CHECK:
            - Only the face should be changed
            - Everything else must remain identical to the original image";

        // Create a StoryGeneration record to track progress
        $story = StoryGeneration::create([
            'name' => $validated['name'],
            'status' => 'pending',
            'total_pages' => 0,
            'processed_pages' => 0,
            'pdf_path' => $pdfPath,
            'prompt' => $prompt,
        ]);

        // Dispatch the PrepareStoryJob (which orchestrates the entire parallel pipeline)
        PrepareStoryJob::dispatch(
            storyId: $story->id,
            photoPath: $fullTempPhotoPath,
            childName: $validated['name'],
            pdfPath: $pdfPath,
            prompt: $prompt
        );

        return response()->json([
            'message' => 'Your personalized story is being generated! Track progress using the story ID.',
            'story_id' => $story->id,
            'status_url' => route('story.status', $story->id),
        ]);
    }

    /**
     * Get the current progress/status of a story generation.
     */
    public function status(int $id)
    {
        $story = StoryGeneration::findOrFail($id);

        return response()->json([
            'id' => $story->id,
            'name' => $story->name,
            'status' => $story->status,
            'total_pages' => $story->total_pages,
            'processed_pages' => $story->processed_pages,
            'output_path' => $story->output_path,
            'progress' => $story->total_pages > 0
                ? round(($story->processed_pages / $story->total_pages) * 100, 1)
                : 0,
        ]);
    }
}
