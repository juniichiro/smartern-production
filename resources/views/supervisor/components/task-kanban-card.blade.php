@php
    $dueDate = \Carbon\Carbon::parse($task->due_date);
    $isOverdue = $dueDate->isPast() && $task->status->value !== 'completed';
    $isDueToday = $dueDate->isToday();
@endphp
<div class="flex flex-col bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[24px] overflow-hidden shadow-sm transition-all duration-300 hover:shadow-md hover:border-[#0B5694]/20 group/card text-left"
     x-show="searchQuery === '' || '{{ strtolower(addslashes($task->title)) }}'.includes(searchQuery.toLowerCase())">
    
    <!-- Header -->
    <div class="px-5 py-3 bg-gray-50/50 dark:bg-gray-800/20 border-b border-gray-100 dark:border-gray-800/60 flex items-center justify-between text-[10px]">
        <div class="flex items-center gap-1.5 text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest">
            <svg class="w-4 h-4 text-[#0B5694] dark:text-blue-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Task details
        </div>
        
        @if($task->status->value == 'completed')
            <span class="text-emerald-500 font-black uppercase tracking-wider">Verified</span>
        @elseif($task->status->value == 'revision')
            <span class="text-yellow-600 font-black uppercase tracking-wider">Revision</span>
        @elseif($task->submissions->count() > 0)
            <span class="text-blue-500 font-black uppercase tracking-wider">Needs Review</span>
        @else
            <span class="text-orange-400 font-black uppercase tracking-wider">In Progress</span>
        @endif
    </div>

    <!-- Body -->
    <div class="p-4 md:p-5 flex-1 flex flex-col justify-between">
        <div class="space-y-3">
            <h5 class="text-[14px] font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight leading-snug group-hover/card:text-[#0B5694] dark:group-hover/card:text-blue-400 transition-colors">
                {{ $task->title }}
            </h5>
            <p class="text-xs text-gray-400 dark:text-gray-500 line-clamp-3 italic font-medium leading-relaxed">
                {{ $task->description }}
            </p>
        </div>

        <!-- Footer -->
        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800/60 space-y-3">
            <div class="flex items-center justify-between text-[10px] text-gray-400 dark:text-gray-500">
                <div class="flex items-center gap-1 font-bold uppercase tracking-wider {{ $isOverdue ? 'text-rose-500 dark:text-rose-400' : '' }}">
                    <svg class="w-3.5 h-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Due: {{ $dueDate->format('M d') }}
                </div>
                <span class="font-bold text-[9px] uppercase {{ $isOverdue ? 'text-rose-400 dark:text-rose-500 animate-pulse' : 'text-gray-300 dark:text-gray-600' }}">
                    {{ $dueDate->diffForHumans() }}
                </span>
            </div>

            <div class="flex justify-end gap-1.5 items-center">
                @if($task->submissions->count() > 0 && $task->status->value !== 'revision')
                    <a href="{{ route('supervisor.tasks.show', $task) }}" 
                       class="p-2 bg-[#0B5694] dark:bg-blue-600 text-white hover:bg-[#084577] dark:hover:bg-blue-700 rounded-xl transition-all shadow-md active:scale-95" 
                       title="Review Submission">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                @endif
                
                {{-- Actions hidden for 'completed' tasks to ensure non-repudiation --}}
                @if($task->status->value !== 'completed')
                    @can('update', $task)
                        <button @click="openEditModal('{{ $task->getRouteKey() }}', {{ json_encode($task->title) }}, {{ json_encode($task->description) }}, '{{ $task->due_date }}', {{ $task->is_file_required ? 'true' : 'false' }})" 
                                class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 rounded-xl shadow-sm transition-all active:scale-95">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                    @endcan
                    
                    @can('delete', $task)
                        <button @click="confirmDelete('{{ $task->getRouteKey() }}')" 
                                class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-red-600 dark:hover:text-red-400 rounded-xl shadow-sm transition-all active:scale-95">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</div>
