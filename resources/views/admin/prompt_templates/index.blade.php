@extends('layouts.admin')

@section('title', 'Prompts - Admin')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Prompt Templates</h1>
        <p class="mt-2 text-sm text-gray-700">Manage your dynamic AI prompt generation blocks and templates.</p>
    </div>
    <div class="mt-4 sm:mt-0">
        <a href="{{ route('prompt-templates.create') }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto transition">
            Add Prompt
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 font-medium text-sm">
    {{ session('success') }}
</div>
@endif

<div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name / Key</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($templates as $template)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-gray-900">{{ $template->name }}</div>
                        <div class="text-xs text-gray-500 mt-1 font-mono bg-gray-100 inline-block px-2 py-0.5 rounded">{{ $template->key }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">v{{ $template->version }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($template->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                            Inactive
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('prompt-templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</a>
                        <form action="{{ route('prompt-templates.destroy', $template) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this prompt template?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">No prompt templates found. <a href="{{ route('prompt-templates.create') }}" class="text-indigo-600 hover:underline">Create one</a>.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
