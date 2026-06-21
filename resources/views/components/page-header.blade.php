@props(['title', 'subtitle' => null, 'compact' => false, 'highlight' => null])

<header {{ $attributes->merge(['class' => ($compact ? 'mb-8' : 'mb-12') . ' shrink-0 ' . (isset($actions) ? 'flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6' : '')]) }}>
    <div class="flex-1 min-w-0">
        <h2 class="text-2xl md:text-4xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight uppercase leading-tight">
            {{ $title }}
            @if($highlight)
                <span class="text-[#0B5694] dark:text-blue-400">
                    {{ $highlight }}
                </span>
            @endif
        </h2>
        @if($subtitle)
            <p class="text-[10px] md:text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-1">
                {{ $subtitle }}
            </p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex flex-wrap items-center gap-4 w-full sm:w-auto">
            {{ $actions }}
        </div>
    @endif
</header>
