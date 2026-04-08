@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="bg-white shadow-sm border border-gray-200 rounded-2xl p-8">
    <div class="flex items-center space-x-4 mb-6">
        <div class="bg-indigo-100 p-3 rounded-xl">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">System Dashboard</h1>
            <p class="text-sm text-gray-500">Overview of your Personalized Storybook engine.</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <a href="{{ route('templates.index') }}" class="block p-6 bg-gray-50 border border-gray-200 rounded-xl hover:bg-gray-100 transition">
            <h3 class="text-lg font-bold text-gray-900">Manage Templates</h3>
            <p class="text-sm text-gray-500 mt-1">Upload and configure PDF book templates for generation.</p>
        </a>
    </div>
</div>
@endsection
