@props(['title' => null, 'subtitle' => null, 'headerAction' => null])

<div class="bg-white dark:bg-gray-900 rounded-[25px] sm:rounded-[30px] shadow-sm dark:shadow-none border border-gray-100 dark:border-gray-800 flex flex-col overflow-hidden">
    @if($title || $headerAction)
        <div class="p-6 sm:p-8 lg:p-10 border-b border-gray-50 dark:border-gray-800 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-6">
            <div>
                @if($title)
                    <h3 class="text-lg sm:text-xl font-extrabold text-gray-800 dark:text-gray-100 uppercase tracking-normal">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 mt-1 font-medium italic">{{ $subtitle }}</p>
                @endif
            </div>
            @if($headerAction)
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                    {{ $headerAction }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-4 sm:p-6 lg:p-10 flex-1 flex flex-col min-h-0">
        {{ $slot }}
    </div>
</div>
