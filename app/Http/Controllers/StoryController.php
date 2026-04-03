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

        $pdfPath = base_path('Design sans titre.pdf');

        $prompt = "Replace ONLY the baby character in this image with the provided child character.
                    STRICT RULES:
                    - Use the provided character as the ONLY reference for the face and identity
                    - Preserve the exact same facial features, hairstyle, and proportions
                    - Do NOT redesign or reinterpret the character
                    - Keep identity 100% consistent with the reference image

                    SCENE RULES:
                    - Do NOT change the background
                    - Do NOT change colors, lighting, or style
                    - Do NOT modify any other objects or elements
                    - Keep the original illustration style exactly the same

                    POSE:
                    - Match the pose and position of the original baby character
                    - Adapt the provided character to fit the same pose naturally

                    IMPORTANT:
                    Only replace the baby character. Everything else must remain unchanged.";

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
