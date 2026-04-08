@extends('layouts.app')

@section('title', 'Generating Story - StoryBook')

@section('content')
<div class="min-h-[calc(100vh-130px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center space-y-8">
        <div>
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-50 border border-indigo-100 mb-6 relative">
                <svg class="h-8 w-8 text-indigo-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Weaving Magic...</h2>
            <p class="mt-2 text-sm text-gray-500" id="status-text">Our AI illustrators are painting your story. This might take a couple of minutes.</p>
        </div>

        <!-- Progress Bar -->
        <div class="relative pt-1">
            <div class="overflow-hidden h-3 mb-4 text-xs flex rounded-full bg-indigo-50 border border-indigo-100">
                <div id="progress-bar" style="width: 5%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 transition-all duration-1000 ease-out"></div>
            </div>
            <div class="flex justify-between text-xs text-indigo-600 font-semibold" id="progress-text">
                <span>0 Pages rendering</span>
                <span>Wait tightly!</span>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const storyId = {{ $story->id }};
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const statusText = document.getElementById('status-text');
        
        let interval = setInterval(() => {
            fetch(`/story/${storyId}/status`)
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        const total = data.total_pages || 13;
                        const processed = data.processed_pages || 0;
                        const percentage = Math.max(5, Math.floor((processed / total) * 100));
                        
                        progressBar.style.width = percentage + '%';
                        progressText.children[0].innerText = `${processed} / ${total} pages mapped`;
                        
                        // Status textual mapping
                        if(data.status === 'processing') statusText.innerText = "Designing character and rendering high-quality illustrations...";
                        if(data.status === 'finalizing') statusText.innerText = "Polishing and binding the PDF together...";
                        
                        if (data.status === 'completed' || data.status === 'completed_partial') {
                            clearInterval(interval);
                            progressBar.style.width = '100%';
                            statusText.innerText = "All done! Redirecting you to the magic...";
                            setTimeout(() => {
                                window.location.href = `/story/${storyId}`;
                            }, 1500);
                        } else if (data.status === 'failed') {
                            clearInterval(interval);
                            progressBar.classList.remove('bg-indigo-500');
                            progressBar.classList.add('bg-red-500');
                            statusText.innerText = "Something went wrong. Please try again.";
                            progressText.innerHTML = `<span class="text-red-600">Failed</span>`;
                        }
                    }
                })
                // Fallback catch (network issues)
                .catch(error => console.error('Error fetching status:', error));
        }, 3000); // Polling every 3 seconds to avoid stressing the server
    });
</script>
@endsection
