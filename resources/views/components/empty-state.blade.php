@props([
    'icon' => null, 
    'title', 
    'subtitle' => null, 
    'actionLink' => null, 
    'actionText' => null,
    'compact' => false,
    'showIcon' => true
])

<div {{ $attributes->merge(['class' => "flex-1 flex flex-col items-center justify-center " . ($compact ? 'py-8' : 'py-16') . " px-6 text-center"]) }}>
    @if($showIcon)
        <div class="bg-gray-50 dark:bg-gray-800/50 {{ $compact ? 'w-16 h-16' : 'w-24 h-24' }} rounded-full flex items-center justify-center mb-6 border border-gray-100 dark:border-gray-700 shrink-0">
            @if($icon)
                <div class="text-gray-300 dark:text-gray-600">
                    {!! $icon !!}
                </div>
            @else
                <svg class="text-gray-300 dark:text-gray-600" width="{{ $compact ? '24' : '32' }}" height="{{ $compact ? '24' : '32' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
                    <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                </svg>
            @endif
        </div>
    @endif
    
    <h5 class="text-gray-400 dark:text-gray-500 font-bold uppercase {{ $compact ? 'text-[10px]' : 'text-[12px]' }} tracking-wider mb-2 leading-tight">
        {{ $title }}
    </h5>
    
    @if($subtitle)
        <p class="text-gray-400 dark:text-gray-600 {{ $compact ? 'text-[9px]' : 'text-[10px]' }} font-medium uppercase tracking-wider leading-relaxed max-w-[280px]">
            {!! $subtitle !!}
        </p>
    @endif

    @if($actionLink && $actionText)
        <a href="{{ $actionLink }}" class="mt-8 group relative px-8 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl text-[10px] font-bold uppercase tracking-wider hover:scale-105 active:scale-95 transition-all shadow-md dark:shadow-none border border-blue-400/20 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            <span class="relative flex items-center gap-3">
                {{ $actionText }}
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </span>
        </a>
    @endif
</div>
