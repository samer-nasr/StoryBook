<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PromptTemplateController extends Controller
{
    public function index()
    {
        $templates = PromptTemplate::orderBy('key')->orderByDesc('version')->get();
        return view('admin.prompt_templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.prompt_templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:prompt_templates,key',
            'version' => 'required|integer|min:1',
            'system_role' => 'nullable|string',
            'strict_rules' => 'nullable|string',
            'identity_block' => 'nullable|string',
            'style_block' => 'nullable|string',
            'task' => 'nullable|string',
            'constraints' => 'nullable|string',
            'output_rules' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Handle Active Toggle Logic
        if ($validated['is_active']) {
            PromptTemplate::where('key', $validated['key'])->update(['is_active' => false]);
        }

        PromptTemplate::create($validated);

        Cache::forget('prompt_templates');

        return redirect()->route('prompt-templates.index')->with('success', 'Prompt template created successfully.');
    }

    public function edit(PromptTemplate $promptTemplate)
    {
        return view('admin.prompt_templates.edit', compact('promptTemplate'));
    }

    public function update(Request $request, PromptTemplate $promptTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'required|integer|min:1',
            'system_role' => 'nullable|string',
            'strict_rules' => 'nullable|string',
            'identity_block' => 'nullable|string',
            'style_block' => 'nullable|string',
            'task' => 'nullable|string',
            'constraints' => 'nullable|string',
            'output_rules' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Handle Active Toggle Logic
        if ($validated['is_active'] && !$promptTemplate->is_active) {
            PromptTemplate::where('key', $promptTemplate->key)->update(['is_active' => false]);
        }

        $promptTemplate->update($validated);

        Cache::forget('prompt_templates');

        return redirect()->route('prompt-templates.index')->with('success', 'Prompt template updated successfully.');
    }

    public function destroy(PromptTemplate $promptTemplate)
    {
        $promptTemplate->update(['is_active' => false]);
        // Softly disable it instead of strict deletion, or we can optionally delete. 
        // Instruction: destroy() (optional: disable instead of delete)
        // Let's actually delete just in case they have hundreds of tests.
        $promptTemplate->delete();

        Cache::forget('prompt_templates');

        return redirect()->route('prompt-templates.index')->with('success', 'Prompt template removed successfully.');
    }
}
