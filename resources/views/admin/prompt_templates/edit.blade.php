@extends('layouts.admin')

@section('title', 'Edit Prompt Template - Admin')

@section('content')
<div class="mb-8">
    <a href="{{ route('prompt-templates.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">&larr; Back to Prompt Templates</a>
    <h1 class="mt-2 text-2xl font-bold text-gray-900">Edit Prompt Template</h1>
</div>

<div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
    <form action="{{ route('prompt-templates.update', $promptTemplate) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="p-6 md:p-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ old('name', $promptTemplate->name) }}">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="key" class="block text-sm font-medium text-gray-700">Key</label>
                    <input type="text" id="key" disabled readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm font-mono text-gray-500" value="{{ $promptTemplate->key }}">
                    <p class="mt-1 text-xs text-gray-500">Key cannot be changed after creation.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="version" class="block text-sm font-medium text-gray-700">Version</label>
                    <input type="number" name="version" id="version" required min="1" value="{{ old('version', $promptTemplate->version) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('version')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center pt-6">
                    <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $promptTemplate->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">Active (Disables other versions of this key)</label>
                </div>
            </div>

            <hr class="border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Prompt Blocks</h3>
            <p class="text-sm text-gray-500">Modify the blocks necessary for this key's context.</p>

            <div class="space-y-6">
                @foreach (['system_role' => 'System Role', 'strict_rules' => 'Strict Rules', 'identity_block' => 'Identity Block', 'style_block' => 'Style Block', 'task' => 'Task', 'constraints' => 'Constraints', 'output_rules' => 'Output Rules'] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                    <textarea id="{{ $field }}" name="{{ $field }}" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono">{{ old($field, $promptTemplate->$field) }}</textarea>
                    @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end rounded-b-xl border-t border-gray-200">
            <a href="{{ route('prompt-templates.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 mr-4">Cancel</a>
            <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Update Prompt Template
            </button>
        </div>
    </form>
</div>
@endsection
