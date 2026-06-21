@props([
    'title', 
    'value', 
    'subtitle' => null, 
    'color' => 'white', 
    'icon' => null, 
    'link' => null, 
    'linkText' => 'View Details',
    'animate' => false,
    'size' => 'default'
])

@php
    $baseClasses = "rounded-[35px] md:rounded-[40px] p-8 transition-all duration-300 relative overflow-hidden group";
    
    $colorClasses = match($color) {
        'blue', 'primary' => "bg-[#0B5694] dark:bg-blue-600 text-white shadow-md dark:shadow-none border border-blue-400/20",
        'emerald', 'green' => "bg-emerald-500 text-white shadow-md dark:shadow-none border border-emerald-400/20",
        'rose', 'red' => "bg-rose-500 text-white shadow-md dark:shadow-none border border-rose-400/20",
        default => "bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm dark:shadow-none"
    };

    $titleClasses = match($color) {
        'white' => "text-gray-400 dark:text-gray-500",
        default => "opacity-60 text-white"
    };

    $valueClasses = match($color) {
        'white' => "text-gray-900 dark:text-white",
        default => "text-white"
    };

    $subtitleClasses = match($color) {
        'white' => "text-gray-400 font-bold",
        default => "opacity-80 font-bold"
    };

    $valueSizeClasses = match($size) {
        'compact' => 'text-3xl md:text-4xl',
        default => 'text-4xl md:text-5xl lg:text-6xl'
    };

    $titleSizeClasses = match($size) {
        'compact' => 'text-[9px] md:text-[10px]',
        default => 'text-[10px] md:text-[11px]'
    };

    $subtitleSizeClasses = match($size) {
        'compact' => 'text-[10px] md:text-[11px]',
        default => 'text-[11px] md:text-sm'
    };

    $spacingClasses = match($size) {
        'compact' => 'mb-3 md:mb-6',
        default => 'mb-4 md:mb-10'
    };
@endphp

<div {{ $attributes->merge(['class' => "$baseClasses $colorClasses " . ($animate ? 'hover:scale-[1.02] active:scale-[0.98]' : 'hover:shadow-md')]) }}>
    @if($icon)
        <div class="absolute -bottom-6 -right-6 w-32 h-32 md:w-40 md:h-40 opacity-10 group-hover:rotate-12 transition-transform duration-700 pointer-events-none">
            {!! $icon !!}
        </div>
    @endif

    <div class="relative z-10 flex flex-col h-full">
        <p class="{{ $titleSizeClasses }} font-bold uppercase tracking-wider {{ $spacingClasses }} {{ $titleClasses }}">{{ $title }}</p>
        
        <div class="mb-4">
            <h3 class="{{ $valueSizeClasses }} font-extrabold tracking-normal {{ $valueClasses }}">{{ $value }}</h3>
            @if($subtitle)
                <p class="{{ $subtitleSizeClasses }} uppercase tracking-wider mt-2 {{ $subtitleClasses }}">{{ $subtitle }}</p>
            @endif
        </div>

        @if($link)
            <div class="mt-auto pt-6">
                <a href="{{ $link }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-[10px] font-bold uppercase tracking-wider transition-all
                    {{ $color === 'white' ? 'bg-gray-50 dark:bg-gray-800 text-[#0B5694] dark:text-blue-400 hover:bg-gray-100' : 'bg-white/10 hover:bg-white/20 backdrop-blur-md text-white' }}">
                    {{ $linkText }}
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        @endif
    </div>
</div>
