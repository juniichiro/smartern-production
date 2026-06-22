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
    $colorMap = [
        'blue' => 'primary', 'primary' => 'primary', 'emerald' => 'emerald', 'green' => 'emerald',
        'rose' => 'rose', 'red' => 'rose',
    ];
    $resolved = $colorMap[$color] ?? 'white';

    $valueColor = match($resolved) {
        'primary' => 'text-white',
        'emerald' => 'text-white',
        'rose' => 'text-white',
        default => 'text-gray-900 dark:text-white'
    };

    $accentBorder = match($resolved) {
        'primary' => 'border-[#0B5694] dark:border-blue-500',
        'emerald' => 'border-emerald-500',
        'rose' => 'border-rose-500',
        default => 'border-gray-200 dark:border-gray-700'
    };

    $accentBg = match($resolved) {
        'primary' => 'bg-[#0B5694] dark:bg-blue-600',
        'emerald' => 'bg-emerald-500',
        'rose' => 'bg-rose-500',
        default => 'bg-gray-100 dark:bg-gray-800'
    };

    $accentText = match($resolved) {
        'primary' => 'text-[#0B5694] dark:text-blue-400',
        'emerald' => 'text-emerald-500',
        'rose' => 'text-rose-500',
        default => 'text-gray-400 dark:text-gray-500'
    };

    $accentBgSoft = match($resolved) {
        'primary' => 'bg-[#0B5694]/10 dark:bg-blue-500/10',
        'emerald' => 'bg-emerald-50 dark:bg-emerald-500/10',
        'rose' => 'bg-rose-50 dark:bg-rose-500/10',
        default => 'bg-gray-50 dark:bg-gray-800'
    };

    $accentBgStrong = match($resolved) {
        'primary' => 'bg-[#0B5694] dark:bg-blue-600',
        'emerald' => 'bg-emerald-500',
        'rose' => 'bg-rose-500',
        default => 'bg-gray-200 dark:bg-gray-700'
    };
@endphp

<div {{ $attributes->merge(['class' => 'relative transition-all duration-300 group' . ($animate ? ' hover:scale-[1.02] active:scale-[0.98]' : '')]) }}>
    {{-- ============================================================ --}}
    {{-- DESKTOP LAYOUT (lg+) — Full card with decorative background  --}}
    {{-- ============================================================ --}}
    <div class="hidden lg:flex flex-col h-full rounded-[40px] p-8 relative overflow-hidden shadow-sm dark:shadow-none border {{ $resolved === 'white' ? 'bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800' : "$accentBg text-white border-transparent" }}">
        @if($icon)
            <div class="absolute -bottom-6 -right-6 w-40 h-40 opacity-10 group-hover:rotate-12 transition-transform duration-700 pointer-events-none">
                {!! $icon !!}
            </div>
        @endif

        <div class="relative z-10 flex flex-col h-full">
            <p class="text-[11px] font-bold uppercase tracking-wider mb-10 {{ $resolved === 'white' ? 'text-gray-400 dark:text-gray-500' : 'text-white/60' }}">{{ $title }}</p>

            <div class="mb-4">
                <h3 class="{{ $size === 'compact' ? 'text-4xl' : 'text-5xl lg:text-6xl' }} font-extrabold tracking-normal {{ $valueColor }}">{{ $value }}</h3>
                @if($subtitle)
                    <p class="text-sm uppercase tracking-wider mt-2 font-bold {{ $resolved === 'white' ? 'text-gray-400' : 'text-white/80' }}">{{ $subtitle }}</p>
                @endif
            </div>

            @if($link)
                <div class="mt-auto pt-6">
                    <a href="{{ $link }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-[10px] font-bold uppercase tracking-wider transition-all shadow-md
                        {{ $resolved === 'white' ? 'bg-gray-50 dark:bg-gray-800 text-[#0B5694] dark:text-blue-400 hover:bg-gray-100' : 'bg-white/10 hover:bg-white/20 backdrop-blur-md text-white' }}">
                        {{ $linkText }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TABLET LAYOUT (md-lg) — Horizontal with color accent bar   --}}
    {{-- ============================================================ --}}
    <div class="hidden md:flex lg:hidden items-stretch h-full rounded-[32px] overflow-hidden shadow-sm dark:shadow-none border {{ $resolved === 'white' ? 'bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800' : 'border-transparent' }}">
        <div class="w-2 shrink-0 {{ $resolved === 'white' ? $accentBgStrong : '' }} {{ $resolved !== 'white' ? "$accentBg" : '' }}"></div>

        <div class="flex-1 flex items-center gap-6 p-6 {{ $resolved !== 'white' ? "$accentBg text-white" : '' }}">
            @if($icon)
                <div class="shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center {{ $resolved === 'white' ? $accentBgSoft : 'bg-white/15' }}">
                    <div class="w-6 h-6 {{ $resolved === 'white' ? $accentText : 'text-white' }}">
                        {!! $icon !!}
                    </div>
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex items-baseline gap-3">
                    <h3 class="{{ $size === 'compact' ? 'text-3xl' : 'text-4xl' }} font-extrabold tracking-normal {{ $resolved === 'white' ? 'text-gray-900 dark:text-white' : 'text-white' }}">{{ $value }}</h3>
                    @if($subtitle)
                        <span class="text-[11px] font-bold uppercase tracking-wider {{ $resolved === 'white' ? 'text-gray-400' : 'text-white/70' }}">/ {{ $subtitle }}</span>
                    @endif
                </div>
                <span class="inline-block mt-1.5 px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $resolved === 'white' ? "$accentBgSoft $accentText" : 'bg-white/15 text-white/80' }}">{{ $title }}</span>
            </div>

            @if($link)
                <a href="{{ $link }}" class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center transition-all {{ $resolved === 'white' ? 'bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400' : 'bg-white/10 hover:bg-white/20 text-white' }}" title="{{ $linkText }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MOBILE LAYOUT (< md) — Compact with bottom accent border     --}}
    {{-- ============================================================ --}}
    <div class="flex md:hidden flex-col h-full rounded-[24px] p-5 border-b-[5px] shadow-sm dark:shadow-none border {{ $resolved === 'white' ? 'bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800' : 'border-transparent' }}" style="{{ $resolved !== 'white' ? "border-bottom-color: transparent" : '' }}">
        {{-- Colored top corner dot --}}
        <div class="absolute top-3 right-3 w-2.5 h-2.5 rounded-full {{ $resolved === 'white' ? $accentBgStrong : '' }} {{ $resolved !== 'white' ? "$accentBg" : '' }}"></div>

        <div class="flex items-center gap-3 mb-3">
            @if($icon)
                <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $resolved === 'white' ? $accentBgSoft : 'bg-white/15' }}">
                    <div class="w-4 h-4 {{ $resolved === 'white' ? $accentText : 'text-white' }}">
                        {!! $icon !!}
                    </div>
                </div>
            @endif
            <span class="text-[9px] font-bold uppercase tracking-wider {{ $resolved === 'white' ? 'text-gray-400 dark:text-gray-500' : 'text-white/70' }}">{{ $title }}</span>
        </div>

        <h3 class="text-[28px] font-extrabold tracking-normal leading-none {{ $resolved === 'white' ? 'text-gray-900 dark:text-white' : 'text-white' }}">{{ $value }}</h3>

        @if($subtitle)
            <p class="text-[10px] uppercase tracking-wider mt-1.5 font-bold {{ $resolved === 'white' ? 'text-gray-400' : 'text-white/70' }}">{{ $subtitle }}</p>
        @endif

        @if($link)
            <div class="mt-3 pt-3 border-t {{ $resolved === 'white' ? 'border-gray-100 dark:border-gray-800' : 'border-white/10' }}">
                <a href="{{ $link }}" class="flex items-center justify-center gap-1.5 w-full py-2.5 rounded-xl text-[9px] font-bold uppercase tracking-wider transition-all active:scale-95
                    {{ $resolved === 'white' ? 'bg-gray-50 dark:bg-gray-800 text-[#0B5694] dark:text-blue-400' : 'bg-white/10 text-white' }}">
                    {{ $linkText }}
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        @endif
    </div>
</div>
