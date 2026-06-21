@props([
    'type' => 'text', // text, avatar, block, list, metric
    'rows' => 1,
    'class' => ''
])

<div class="flex flex-col gap-2 {{ $class }}">
    @if($type === 'text')
        @for($i = 0; $i < $rows; $i++)
            <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded animate-shimmer w-full"></div>
        @endfor
    @elseif($type === 'avatar')
        <div class="w-12 h-12 bg-gray-200 dark:bg-gray-800 rounded-full animate-shimmer"></div>
    @elseif($type === 'block')
        <div class="h-32 bg-gray-200 dark:bg-gray-800 rounded-3xl animate-shimmer w-full"></div>
    @elseif($type === 'metric')
        <div class="p-6 bg-white dark:bg-gray-800 rounded-[40px] border border-gray-100 dark:border-gray-700/50 space-y-4">
            <div class="h-3 w-1/3 bg-gray-200 dark:bg-gray-700 rounded animate-shimmer"></div>
            <div class="h-8 w-1/2 bg-gray-200 dark:bg-gray-700 rounded animate-shimmer"></div>
            <div class="h-2 w-full bg-gray-200 dark:bg-gray-700 rounded animate-shimmer"></div>
        </div>
    @elseif($type === 'list')
        @for($i = 0; $i < $rows; $i++)
            <div class="flex items-center gap-4 p-4 bg-gray-50/50 dark:bg-gray-800/30 rounded-3xl border border-gray-100 dark:border-gray-700/50">
                <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full animate-shimmer shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-3 w-1/4 bg-gray-200 dark:bg-gray-700 rounded animate-shimmer"></div>
                    <div class="h-2 w-1/2 bg-gray-200 dark:bg-gray-700 rounded animate-shimmer"></div>
                </div>
            </div>
        @endfor
    @endif
</div>
