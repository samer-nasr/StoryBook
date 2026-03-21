<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\AIImageService;
use App\Jobs\GeneratePersonalizedStory;

class StoryController extends Controller
{
    protected $aiImageService;

    public function __construct(AIImageService $aiImageService)
    {
        $this->aiImageService = $aiImageService;
    }

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

        // Store the uploaded photo temporarily in public path so the Job can access it easily if needed
        $photoName = 'child_' . time() . '.' . $request->file('photo')->getClientOriginalExtension();
        $tempPhotoPath = $request->file('photo')->storeAs('temp', $photoName, 'local');
        $fullTempPhotoPath = Storage::disk('local')->path($tempPhotoPath);

        $pdfPath = base_path('Design sans titre.pdf');
        $prompt = "Replace the baby character in this children's storybook illustration with the provided child character while keeping the original art style, colors, and background intact.";

        // Dispatch the job
        GeneratePersonalizedStory::dispatch(
            $fullTempPhotoPath, 
            $validated['name'], 
            $pdfPath, 
            $prompt
        );

        return response()->json([
            'message' => 'Your personalized story is generating! Since this takes some time, check the storage/app/public/generated_stories directory in a few minutes for the final PDF.'
        ]);
    }
}
