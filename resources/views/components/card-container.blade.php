@props([
    'title' => null, 
    'headerAction' => null, 
    'headerActionLink' => null, 
    'headerActionText' => 'View All',
    'padding' => 'p-8 md:p-10',
    'overflow' => 'overflow-hidden'
])

<div {{ $attributes->merge(['class' => "bg-white dark:bg-gray-900 rounded-[40px] shadow-sm dark:shadow-none border border-gray-100 dark:border-gray-800 flex flex-col transition-all duration-300 hover:shadow-md dark:hover:shadow-none hover:border-gray-200 dark:hover:border-gray-700 " . $overflow]) }}>
    @if($title || $headerAction)
        <div class="px-8 min-h-[76px] py-3 border-b border-gray-50 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/30 shrink-0">
            @if($title)
                <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $title }}</h4>
            @endif
            
            @if($headerAction)
                {{ $headerAction }}
            @elseif($headerActionLink)
                <a href="{{ $headerActionLink }}" class="text-[9px] font-bold text-[#0B5694] dark:text-blue-400 uppercase hover:underline tracking-wider bg-blue-50 dark:bg-blue-900/20 px-4 py-2 rounded-full border border-blue-100 dark:border-blue-800">
                    {{ $headerActionText }}
                </a>
            @endif
        </div>
    @endif

    <div class="{{ $padding }} flex-1 flex flex-col min-h-0">
        {{ $slot }}
    </div>
</div>
