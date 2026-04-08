@extends('layouts.app')

@section('title', 'Your Magical Story - StoryBook')

@section('content')
<div class="bg-gray-50 min-h-[calc(100vh-130px)] py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-8">
            <div class="max-w-2xl">
                <span class="text-indigo-600 font-semibold tracking-wide uppercase text-sm">Generation Complete</span>
                <h1 class="mt-2 text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">Here is Your Story!</h1>
                <p class="mt-3 text-lg text-gray-500">Your personalized magical adventure is ready to be explored and shared.</p>
            </div>
            
            <div class="mt-6 md:mt-0 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <!-- Action Buttons -->
                <a href="{{ asset('storage/generated_stories/' . basename($story->output_path)) }}" download class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-xl shadow-sm text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-no mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Print PDF
                </a>
                <a href="/story/create" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-xl shadow-sm text-base font-semibold text-gray-700 bg-white hover:bg-gray-50 transition">
                    Create Another
                </a>
            </div>
        </div>

        <!-- PDF Reader Wrapper -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 relative">
            <div class="bg-gray-100 border-b border-gray-200 px-6 py-4 flex items-center justify-start sm:justify-center">
                <div class="flex space-x-2 absolute left-6 hidden sm:flex">
                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                </div>
                <span class="text-sm font-semibold text-gray-700 uppercase tracking-widest text-center">{{ $story->name }}'s Adventure</span>
            </div>
            
            <!-- Standard Desktop PDF Height -->
            <div class="h-[75vh] min-h-[600px] bg-gray-50 w-full relative">
                <!-- Iframe for modern browsers / desktops -->
                <iframe src="{{ asset('storage/generated_stories/' . basename($story->output_path)) }}#toolbar=0" class="w-full h-full border-0 shadow-inner" allowfullscreen></iframe>
                
                <!-- Fallback overlay for mobile devices that might not render iframes natively -->
                <div class="absolute inset-0 flex items-center justify-center bg-gray-50 md:hidden pointer-events-none">
                    <div class="text-center p-8 bg-white bg-opacity-95 rounded-2xl shadow border border-gray-100 max-w-sm pointer-events-auto m-6">
                        <svg class="mx-auto h-16 w-16 text-indigo-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <h3 class="text-xl font-bold text-gray-900">PDF Reader</h3>
                        <p class="text-sm text-gray-500 mt-2 mb-6">Mobile browsers sometimes struggle with embedded PDFs. Tap below to read natively.</p>
                        <a href="{{ asset('storage/generated_stories/' . basename($story->output_path)) }}" target="_blank" class="inline-block w-full py-3 px-4 shadow-sm border border-transparent rounded-xl text-base font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 tracking-wide transition">
                            Open PDF ↗
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
