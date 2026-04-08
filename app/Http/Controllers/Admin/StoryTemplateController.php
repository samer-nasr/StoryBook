<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoryTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoryTemplateController extends Controller
{
    public function index()
    {
        $templates = StoryTemplate::latest()->get();
        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pdf' => 'required|mimes:pdf|max:20480',
        ]);

        $file = $request->file('pdf');
        $path = $file->store('stories', 'public');

        StoryTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'file_path' => $path,
        ]);

        return redirect()->route('templates.index')->with('success', 'Template created successfully.');
    }

    public function edit(StoryTemplate $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    public function update(Request $request, StoryTemplate $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pdf' => 'nullable|mimes:pdf|max:20480',
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
        ];

        if ($request->hasFile('pdf')) {
            if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }
            $data['file_path'] = $request->file('pdf')->store('stories', 'public');
        }

        $template->update($data);

        return redirect()->route('templates.index')->with('success', 'Template updated successfully.');
    }

    public function destroy(StoryTemplate $template)
    {
        if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
            Storage::disk('public')->delete($template->file_path);
        }
        
        $template->delete();

        return redirect()->route('templates.index')->with('success', 'Template deleted successfully.');
    }
}
