@extends('layouts.app')

@section('title', 'Loading...')
@section('is_skeleton_loading', true)

@section('content')
<div class="flex min-h-screen bg-gray-50 dark:bg-gray-950 font-sans text-[14px] overflow-hidden">
    
    {{-- SIDEBAR SKELETON --}}
    <aside class="fixed left-0 top-0 h-screen w-64 bg-white dark:bg-gray-900 border-r border-gray-100 dark:border-gray-800 hidden lg:flex flex-col p-6 z-30">
        <div class="w-32 h-8 bg-gray-200 dark:bg-gray-800 rounded-lg animate-shimmer mb-10"></div>
        <div class="space-y-6">
            @foreach(range(1, 6) as $i)
            <div class="flex items-center space-x-4">
                <div class="w-6 h-6 bg-gray-100 dark:bg-gray-800 rounded-md animate-shimmer"></div>
                <div class="w-24 h-4 bg-gray-100 dark:bg-gray-800 rounded-md animate-shimmer"></div>
            </div>
            @endforeach
        </div>
        <div class="mt-auto pt-10 border-t border-gray-100 dark:border-gray-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gray-200 dark:bg-gray-800 rounded-full animate-shimmer"></div>
                <div class="space-y-2">
                    <div class="w-20 h-3 bg-gray-200 dark:bg-gray-800 rounded-md animate-shimmer"></div>
                    <div class="w-12 h-2 bg-gray-100 dark:bg-gray-800 rounded-md animate-shimmer"></div>
                </div>
            </div>
        </div>
    </aside>

    <main class="lg:ml-64 lg:mr-80 flex-1 p-4 sm:p-6 lg:p-10 text-left">
        {{-- Header Skeleton --}}
        <header class="mb-12">
            <x-skeleton type="text" class="h-10 w-48 mb-3" />
            <x-skeleton type="text" class="h-4 w-72" />
        </header>

        {{-- STATS GRID SKELETON --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
            @foreach(range(1, 4) as $i)
            <x-skeleton type="metric" />
            @endforeach
        </div>

        {{-- MAIN CONTENT AREA SKELETON --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            @foreach(range(1, 2) as $i)
            <x-card-container padding="p-10" class="rounded-[35px]">
                <x-skeleton type="text" class="w-40 h-6 mb-3" />
                <x-skeleton type="text" class="w-56 h-3 mb-10" />
                <x-skeleton type="list" rows="4" />
            </x-card-container>
            @endforeach
        </div>
    </main>

    {{-- NOTIFICATION SIDEBAR SKELETON --}}
    <aside class="fixed right-0 top-0 h-screen w-80 bg-white dark:bg-gray-900 border-l border-gray-100 dark:border-gray-800 hidden xl:flex flex-col p-8 z-30">
        <x-skeleton type="text" class="w-40 h-6 mb-10" />
        <div class="space-y-10">
            @foreach(range(1, 5) as $i)
            <div class="flex space-x-4">
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-800 rounded-full animate-shimmer flex-shrink-0"></div>
                <div class="space-y-3 flex-1">
                    <div class="w-full h-3 bg-gray-100 dark:bg-gray-800 rounded-md animate-shimmer"></div>
                    <div class="w-2/3 h-3 bg-gray-50 dark:bg-gray-800 rounded-md animate-shimmer"></div>
                    <div class="w-16 h-2 bg-gray-50 dark:bg-gray-800 rounded-md animate-shimmer"></div>
                </div>
            </div>
            @endforeach
        </div>
    </aside>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Trigger the actual analysis
        fetch("{{ route('security.run') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Once finished, reload the current page
            // The Middleware will now let it through because the session flag is set
            window.location.reload();
        })
        .catch(error => {
            console.error('Security analysis failed:', error);
            // Fallback: reload anyway after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        });
    });
</script>
@endsection
