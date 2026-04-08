@extends('layouts.admin')

@section('title', 'Manage Templates - Admin')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Story Templates</h1>
        <p class="mt-2 text-sm text-gray-700">A list of all PDF templates available for story generation.</p>
    </div>
    <div class="mt-4 sm:mt-0">
        <a href="{{ route('templates.create') }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto transition">
            Add Template
        </a>
    </div>
</div>

<div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($templates as $template)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $template->name }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($template->description, 50) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <a href="{{ asset('storage/' . $template->file_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-medium">View PDF</a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</a>
                    <form action="{{ route('templates.destroy', $template) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this template?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">No templates found. <a href="{{ route('templates.create') }}" class="text-indigo-600 hover:underline">Create one</a>.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
