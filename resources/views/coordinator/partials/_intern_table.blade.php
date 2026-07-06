<div class="border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-inner">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">
                    <th class="px-10 py-6">Student Details</th>
                    <th class="px-6 py-6 text-center">Assigned Office</th>
                    <th class="px-6 py-6">Training Progress</th>
                    <th class="px-6 py-6 text-center">Status</th>
                    <th class="px-10 py-6 text-center">Absences & Lates</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($interns as $intern)
                    @php
                        $goal = $intern->required_hours ?? 480;
                        $rendered = round($intern->hours_rendered ?? 0); 
                        $progress = $goal > 0 ? min(100, round(($rendered / $goal) * 100)) : 0;
                        $isCertified = optional($intern->certificate)->status === 'published';
                    @endphp
                    
                    <tr onclick="window.location.href='{{ route('coordinator.interns.show', $intern->user) }}'" 
                        class="hover:bg-blue-50/40 dark:hover:bg-blue-900/10 transition-all group cursor-pointer">
                        <td class="px-10 py-6">
                            <div class="flex items-center gap-4">
                                <x-avatar :user="$intern" size="10" circular="true" border="0" />
                                <div class="min-w-0">
                                    <p class="text-[14px] font-black text-gray-800 dark:text-gray-100 leading-tight group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors uppercase tracking-tight truncate">
                                        {{ $intern->first_name }} {{ $intern->last_name }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold tracking-widest uppercase">{{ $intern->student_id ?? 'No Student ID' }}</p>
                                        @if($intern->absent_count > 2)
                                            <span class="flex items-center gap-1 text-[9px] font-black text-rose-500 uppercase tracking-tighter bg-rose-50 dark:bg-rose-950/30 px-1.5 py-0.5 rounded border border-rose-100 dark:border-rose-900/50">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                                At Risk
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-6 text-center">
                            <span class="text-gray-500 dark:text-gray-400 text-[10px] font-black uppercase tracking-widest truncate max-w-[150px] inline-block" title="{{ $intern->office->name ?? 'Unassigned' }}">
                                {{ $intern->office->name ?? 'Unassigned' }}
                            </span>
                        </td>

                        <td class="px-6 py-6">
                            <div class="flex flex-col gap-2 min-w-[160px]">
                                <div class="flex justify-between items-center px-1">
                                    <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-tighter">{{ $rendered }} / {{ $goal }} HRS</span>
                                    <span class="text-[11px] font-black {{ $isCertified ? 'text-emerald-500' : 'text-[#0B5694] dark:text-blue-400' }}">{{ $progress }}%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-50 dark:bg-gray-800 rounded-full overflow-hidden shadow-inner border border-gray-100 dark:border-gray-700">
                                    <div class="h-full rounded-full transition-all duration-1000 ease-out {{ $isCertified ? 'bg-emerald-500' : 'bg-[#0B5694] dark:bg-blue-500' }}" 
                                         style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-6 text-center">
                            @if($isCertified)
                                <span class="bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 px-4 py-1.5 rounded-full text-[10px] font-black uppercase border border-emerald-100 dark:border-emerald-800/50 tracking-widest">Completed</span>
                            @elseif($rendered >= $goal)
                                <span class="bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 px-4 py-1.5 rounded-full text-[10px] font-black uppercase border border-orange-100 dark:border-orange-800/50 tracking-widest shadow-sm animate-pulse">Endorsing</span>
                            @else
                                <span class="bg-blue-50 dark:bg-blue-950/30 text-[#0B5694] dark:text-blue-400 px-4 py-1.5 rounded-full text-[10px] font-black uppercase border border-blue-100 dark:border-blue-800/50 tracking-widest">Active</span>
                            @endif
                        </td>

                        <td class="px-10 py-6 text-center">
                            <div class="flex justify-center items-center gap-6">
                                <div class="flex flex-col items-center">
                                    <span class="text-[13px] font-black text-gray-800 dark:text-gray-100">{{ $intern->absent_count }}</span>
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Absences</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <span class="text-[13px] font-black text-gray-800 dark:text-gray-100">{{ $intern->late_count }}</span>
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Lates</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <span class="text-[13px] font-black text-gray-800 dark:text-gray-100">{{ $intern->excused_count ?? 0 }}</span>
                                    <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Excused</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-24 text-center">
                            <x-empty-state title="No student records identified" compact="true" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
@if($interns->hasPages())
    <div class="mt-8 flex justify-end">
        {{ $interns->appends(request()->query())->links('vendor.pagination.tailwind-custom') }}
    </div>
@endif
