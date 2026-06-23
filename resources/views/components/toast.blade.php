@props(['message', 'type' => 'success'])

@php
    $colors = match($type) {
        'success' => 'bg-emerald-50/80 dark:bg-emerald-950/60 border-emerald-200 dark:border-emerald-800/80 text-emerald-700 dark:text-emerald-300 shadow-emerald-500/10',
        'error'   => 'bg-rose-50/80 dark:bg-rose-950/60 border-rose-200 dark:border-rose-800/80 text-rose-700 dark:text-rose-300 shadow-rose-500/10',
        'info'    => 'bg-blue-50/80 dark:bg-blue-950/60 border-blue-200 dark:border-blue-800/80 text-[#0B5694] dark:text-blue-300 shadow-blue-500/10',
        default   => 'bg-gray-50/80 dark:bg-gray-900/60 border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 shadow-sm',
    };

    $category = match($type) {
        'success' => 'Confirmed',
        'error'   => 'Attention',
        'info'    => 'Notice',
        default   => 'System',
    };

    $iconBg = match($type) {
        'success' => 'bg-emerald-100/50 dark:bg-emerald-900/50',
        'error'   => 'bg-rose-100/50 dark:bg-rose-900/50',
        'info'    => 'bg-blue-100/50 dark:bg-blue-900/50',
        default   => 'bg-gray-100/50 dark:bg-gray-800/50',
    };

    $icons = [
        'success' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
        'error'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        'info'    => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
    ];
@endphp

<div x-data="uiNotification(5000)"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-x-8"
     x-transition:enter-end="opacity-100 transform translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-x-0"
     x-transition:leave-end="opacity-0 transform translate-x-8"
     class="fixed bottom-4 sm:bottom-6 right-4 sm:right-6 left-4 sm:left-auto z-[250] flex items-center gap-3 sm:gap-4 px-4 sm:px-6 py-3 sm:py-4 rounded-[20px] border shadow-2xl backdrop-blur-xl min-w-0 sm:min-w-[280px] max-w-[calc(100vw-2rem)] sm:max-w-md {{ $colors }}"
     x-cloak>
    
    <div class="shrink-0 flex items-center justify-center w-10 h-10 rounded-xl {{ $iconBg }}">
        {!! $icons[$type] ?? $icons['success'] !!}
    </div>

    <div class="flex-1">
        <p class="text-[11px] font-bold uppercase tracking-widest opacity-60 mb-0.5">{{ $category }}</p>
        <p class="text-sm font-bold tracking-normal leading-tight">{{ $message }}</p>
    </div>
</div>
