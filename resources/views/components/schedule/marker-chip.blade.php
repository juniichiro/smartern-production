@props(['marker'])

@php
    $type = $marker['type'] ?? 'neutral';
    $title = $marker['title'] ?? '';
    $status = $marker['status'] ?? '';
    $isOverdue = $marker['is_overdue'] ?? false;
@endphp

@if($type === 'holiday')
    <div 
        class="inline-flex items-start gap-1 w-full px-1.5 py-0.5 rounded-md text-[10px] font-semibold border transition-transform hover:scale-[1.01] bg-emerald-50 text-emerald-800 border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50 shrink-0"
        title="{{ $title }}"
    >
        <svg class="w-3 h-3 shrink-0 text-emerald-600 dark:text-emerald-400 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
            <line x1="4" y1="22" x2="4" y2="15"/>
        </svg>
        <span class="line-clamp-1 xl:line-clamp-2 text-left break-words leading-tight">{{ $title }}</span>
    </div>
@elseif($type === 'override')
    <div 
        class="inline-flex items-start gap-1 w-full px-1.5 py-0.5 rounded-md text-[10px] font-semibold border transition-transform hover:scale-[1.01] bg-amber-50 text-amber-800 border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50 shrink-0"
        title="{{ $title }}"
    >
        <svg class="w-3 h-3 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
        </svg>
        <span class="line-clamp-1 xl:line-clamp-2 text-left break-words leading-tight">{{ $title }}</span>
    </div>
@elseif($type === 'task')
    <div 
        class="inline-flex items-start gap-1 w-full px-1.5 py-0.5 rounded-md text-[10px] font-semibold border transition-transform hover:scale-[1.01] {{ $isOverdue ? 'bg-rose-50 text-rose-800 border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50' : 'bg-blue-50 text-blue-800 border-blue-200/80 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/50' }} shrink-0"
        title="Task: {{ $title }}{{ $isOverdue ? ' (Overdue)' : '' }}"
    >
        <svg class="w-3 h-3 shrink-0 mt-0.5 {{ $isOverdue ? 'text-rose-600 dark:text-rose-400' : 'text-[#0B5694] dark:text-blue-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <rect width="18" height="18" x="3" y="3" rx="2"/>
            <path d="m9 12 2 2 4-4"/>
        </svg>
        <span class="line-clamp-1 xl:line-clamp-2 text-left break-words leading-tight">{{ $title }}</span>
    </div>
@elseif($type === 'attendance')
    <div 
        class="inline-flex items-start gap-1 w-full px-1.5 py-0.5 rounded-md text-[10px] font-semibold border transition-transform hover:scale-[1.01] bg-teal-50 text-teal-800 border-teal-200/80 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800/50 shrink-0"
        title="Attendance: {{ $marker['clock_in'] }} - {{ $marker['clock_out'] }}"
    >
        <svg class="w-3 h-3 shrink-0 text-teal-600 dark:text-teal-400 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <span class="line-clamp-1 xl:line-clamp-2 text-left break-words leading-tight">{{ $marker['clock_in'] }} – {{ $marker['clock_out'] }}</span>
    </div>
@elseif($type === 'absence_request')
    <div 
        class="inline-flex items-start gap-1 w-full px-1.5 py-0.5 rounded-md text-[10px] font-semibold border transition-transform hover:scale-[1.01] {{ $status === 'approved' ? 'bg-emerald-50 text-emerald-800 border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50' : ($status === 'rejected' ? 'bg-rose-50 text-rose-800 border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50' : 'bg-amber-50 text-amber-800 border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50') }} shrink-0"
        title="Absence: {{ $marker['reason'] }} ({{ ucfirst($status) }})"
    >
        <span class="w-1.5 h-1.5 rounded-full {{ $status === 'pending' ? 'bg-amber-500 animate-pulse' : ($status === 'approved' ? 'bg-emerald-500' : 'bg-rose-500') }} shrink-0 mt-1"></span>
        <span class="line-clamp-1 xl:line-clamp-2 text-left break-words leading-tight">{{ $marker['request_type'] === 'early_leave' ? 'Early Leave' : 'Absence' }} ({{ ucfirst($status) }})</span>
    </div>
@endif
