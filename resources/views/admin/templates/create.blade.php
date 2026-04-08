@extends('layouts.admin')

@section('title', 'Add Template - Admin')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Add New Template</h1>
    <a href="{{ route('templates.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium mt-1 inline-block">&larr; Back to templates</a>
</div>

<div class="bg-white shadow-sm border border-gray-200 rounded-xl p-8 max-w-3xl">
    <form action="{{ route('templates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
            <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm" value="{{ old('name') }}">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="pdf" class="block text-sm font-medium text-gray-700">PDF File</label>
            <input type="file" name="pdf" id="pdf" accept="application/pdf" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
            @error('pdf') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="pt-4 flex justify-end">
            <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-6 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">Save Template</button>
        </div>
    </form>
</div>
@endsection
