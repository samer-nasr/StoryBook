@extends('layouts.app')

@section('title', 'StoryBook - Turn Your Child Into the Hero of Their Own Story')

@section('content')
<div class="bg-white overflow-hidden">
    <!-- Hero Section -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 text-center">
        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
            Turn Your Child Into <span class="text-indigo-600">the Hero</span> <br class="hidden sm:block">of Their Own Story
        </h1>
        <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">
            Upload a single photo, and our AI will weave a magical, personalized children's book featuring your child as the star character.
        </p>
        <div class="mt-10 flex justify-center">
            <a href="/story/create" class="px-8 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg shadow-lg hover:shadow-xl transition-all">
                Create Your Story Now
            </a>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="bg-gray-50 py-20 border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-extrabold text-gray-900">How It Works</h2>
            <p class="mt-4 text-lg text-gray-500">Three simple steps to a magical read.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Step 1 -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 text-center hover:shadow-md transition-shadow">
                <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-indigo-600">1</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Upload Photo</h3>
                <p class="text-gray-500">Provide a clear picture of your child along with their name and age.</p>
            </div>
            <!-- Step 2 -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 text-center hover:shadow-md transition-shadow">
                <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-indigo-600">2</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">AI Generates Character</h3>
                <p class="text-gray-500">Our AI transforms the photo into a beautiful, storybook-style illustration.</p>
            </div>
            <!-- Step 3 -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 text-center hover:shadow-md transition-shadow">
                <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-indigo-600">3</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Download Story</h3>
                <p class="text-gray-500">Download the complete PDF and share the magic with your family.</p>
            </div>
        </div>
    </div>
</div>

<!-- Examples Preview Section -->
<div class="bg-white py-20 pb-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-10">See What's Possible</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <div class="aspect-[4/3] bg-gray-50 rounded-2xl overflow-hidden border border-gray-200">
                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span>Sample Storybook Area</span>
                </div>
            </div>
             <div class="aspect-[4/3] bg-gray-50 rounded-2xl overflow-hidden border border-gray-200">
                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span>Sample Storybook Area</span>
                </div>
            </div>
        </div>
        <a href="/examples" class="text-indigo-600 font-medium hover:text-indigo-800 transition-colors">View all examples &rarr;</a>
    </div>
</div>
@endsection
