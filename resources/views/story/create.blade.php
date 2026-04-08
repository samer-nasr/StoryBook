@extends('layouts.app')

@section('title', 'Create Your Story - StoryBook')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-12">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Personalize Your Story</h1>
            <p class="mt-2 text-gray-500 text-lg">Fill in the details below to generate a magical book.</p>
        </div>

        <form action="{{ route('story.generate') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- Name and Age Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Child's Name</label>
                    <input type="text" name="name" id="name" class="block w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 shadow-sm" required placeholder="e.g. Liam" value="{{ old('name') }}">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="age" class="block text-sm font-medium text-gray-700 mb-1">Child's Age (1-12)</label>
                    <input type="number" name="age" id="age" class="block w-full rounded-xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 shadow-sm" required min="1" max="12" placeholder="e.g. 5" value="{{ old('age') }}">
                    @error('age')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Enhanced Upload Box -->
            <div>
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Child's Photo (Clear face required)</label>
                
                <div class="mt-1 flex justify-center px-6 pt-10 pb-10 border-2 border-dashed border-gray-300 rounded-2xl hover:bg-gray-50 transition-colors relative" id="drop-zone">
                    <div class="space-y-2 text-center" id="upload-content">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center items-center">
                            <label for="photo" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                <span>Upload a file</span>
                                <input id="photo" name="photo" type="file" class="sr-only" required accept="image/*" onchange="previewImage(event)">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 10MB</p>
                    </div>
                    
                    <!-- Image Preview Container (Hidden initially) -->
                    <div id="image-preview-container" class="hidden absolute inset-0 bg-white rounded-2xl flex flex-col items-center justify-center p-2">
                        <img id="image-preview" src="#" alt="Preview" class="h-32 w-auto object-contain rounded-lg shadow-sm border border-gray-200 mb-2">
                        <button type="button" onclick="resetImage()" class="text-sm text-red-500 font-medium hover:text-red-700">Remove Photo</button>
                    </div>
                </div>
                
                @error('photo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    Generate Magic Story
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.getElementById('image-preview-container');
                const previewImage = document.getElementById('image-preview');
                const uploadContent = document.getElementById('upload-content');
                
                previewImage.src = e.target.result;
                previewContainer.classList.remove('hidden');
                uploadContent.classList.add('opacity-0'); 
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function resetImage() {
        document.getElementById('photo').value = "";
        document.getElementById('image-preview-container').classList.add('hidden');
        document.getElementById('upload-content').classList.remove('opacity-0');
        document.getElementById('image-preview').src = "#";
    }
</script>
@endsection