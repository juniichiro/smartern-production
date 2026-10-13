@props([
    'selectedDay',
])

<div 
    class="shrink-0 rounded-3xl border border-slate-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 sm:p-5 shadow-xs flex flex-col justify-between text-left transition-all"
    role="region"
    aria-label="Selected date details"
>
    <div>
        {{-- Header: Selected Date & Status Pill --}}
        <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100 dark:border-gray-800">
            <div>
                <p class="text-[11px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-wider mb-0.5">
                    <span x-text="currentSelectedDay.is_today ? 'Today' : (currentSelectedDay.is_past ? 'Past date' : 'Selected date')">
                        {{ $selectedDay['is_today'] ? 'Today' : ($selectedDay['is_past'] ? 'Past date' : 'Selected date') }}
                    </span>
                </p>
                <h3 
                    class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white tracking-tight"
                    x-text="currentSelectedDay.formatted_title"
                >
                    {{ $selectedDay['formatted_title'] }}
                </h3>
            </div>

            {{-- Day Status Pill --}}
            <span 
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold shrink-0 border"
                :class="{
                    'bg-emerald-50 text-emerald-800 border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50': currentSelectedDay.day_type === 'holiday',
                    'bg-amber-50 text-amber-800 border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50': currentSelectedDay.day_type === 'override',
                    'bg-blue-50 text-[#0B5694] border-blue-200/80 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/50': currentSelectedDay.day_type === 'workday',
                    'bg-slate-100 text-slate-700 border-slate-200/80 dark:bg-gray-800 dark:text-slate-300 dark:border-gray-700': currentSelectedDay.day_type === 'weekend' || currentSelectedDay.day_type === 'absence',
                }"
            >
                <span class="w-1.5 h-1.5 rounded-full"
                      :class="{
                          'bg-emerald-500': currentSelectedDay.day_type === 'holiday',
                          'bg-amber-500': currentSelectedDay.day_type === 'override',
                          'bg-[#0B5694] dark:bg-blue-400': currentSelectedDay.day_type === 'workday',
                          'bg-slate-400 dark:bg-slate-500': currentSelectedDay.day_type === 'weekend' || currentSelectedDay.day_type === 'absence',
                      }"></span>
                <span x-text="currentSelectedDay.day_type_label">{{ $selectedDay['day_type_label'] }}</span>
            </span>
        </div>

        {{-- Merged shift row & quiet status for regular workday (one compact block, no separate bordered box) --}}
        <template x-if="currentSelectedDay.scheduled_hours && !currentSelectedDay.has_data">
            <div class="mt-3 py-1">
                <div class="flex items-center justify-between text-xs font-medium">
                    <span class="text-slate-500 dark:text-slate-400">Scheduled shift</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200" x-text="currentSelectedDay.scheduled_hours"></span>
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Nothing else scheduled</p>
            </div>
        </template>

        {{-- Shift row when other items (tasks, etc.) exist --}}
        <template x-if="currentSelectedDay.scheduled_hours && currentSelectedDay.has_data">
            <div class="mt-3 flex items-center justify-between text-xs py-1 font-medium">
                <span class="text-slate-500 dark:text-slate-400">Scheduled shift</span>
                <span class="font-bold text-slate-800 dark:text-slate-200" x-text="currentSelectedDay.scheduled_hours"></span>
            </div>
        </template>

        {{-- Contents Stack --}}
        <div class="mt-2.5 space-y-2.5">
            {{-- 1. Holidays on this date --}}
            <template x-for="item in (currentSelectedDay.markers || []).filter(m => m.type === 'holiday')" :key="item.id || item.title">
                <div class="p-3.5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-900/50">
                    <div class="flex items-center gap-2 mb-1 text-[11px] font-bold text-emerald-800 dark:text-emerald-300">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                        <span>Official Holiday</span>
                    </div>
                    <h4 class="text-sm font-bold text-emerald-950 dark:text-emerald-100" x-text="item.title"></h4>
                    <p class="text-xs text-emerald-700/90 dark:text-emerald-400 mt-0.5" x-text="item.effect"></p>
                </div>
            </template>

            {{-- 2. Schedule Overrides on this date --}}
            <template x-for="item in (currentSelectedDay.markers || []).filter(m => m.type === 'override')" :key="item.id || item.title">
                <div class="p-3.5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-900/50">
                    <div class="flex items-center gap-2 mb-1 text-[11px] font-bold text-amber-800 dark:text-amber-300">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>Schedule Override</span>
                    </div>
                    <h4 class="text-sm font-bold text-amber-950 dark:text-amber-100" x-text="item.title"></h4>
                    <p class="text-xs text-amber-700/90 dark:text-amber-400 mt-0.5" x-text="item.effect"></p>
                </div>
            </template>

            {{-- 3. Tasks Due on this date --}}
            <template x-for="task in (currentSelectedDay.markers || []).filter(m => m.type === 'task')" :key="task.id || task.title">
                <div class="p-3.5 rounded-2xl bg-white dark:bg-gray-850 border border-slate-200/80 dark:border-gray-800 hover:border-blue-300 transition-colors shadow-xs">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#0B5694] dark:text-blue-400">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                            <span>Task Deadline</span>
                        </span>
                        <span 
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                            :class="task.is_overdue ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : 'bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-slate-300'"
                            x-text="task.is_overdue ? 'Overdue' : task.status"
                        ></span>
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white" x-text="task.title"></h4>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500 dark:text-slate-400">Due at <span x-text="task.due_time"></span></span>
                        <a 
                            :href="task.route" 
                            class="text-xs font-semibold text-[#0B5694] dark:text-blue-400 hover:underline inline-flex items-center gap-1"
                        >
                            <span>Open task</span>
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </div>
                </div>
            </template>

            {{-- 4. Attendance Records on this date --}}
            <template x-for="att in (currentSelectedDay.markers || []).filter(m => m.type === 'attendance')" :key="att.id">
                <div class="p-3.5 rounded-2xl bg-teal-50/70 dark:bg-teal-950/30 border border-teal-200/70 dark:border-teal-900/50">
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-teal-800 dark:text-teal-300">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <span>Attendance Log</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-teal-100 text-teal-800 dark:bg-teal-900/60 dark:text-teal-200" x-text="att.status"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-700 dark:text-slate-300">
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 text-[11px] block">Clock In</span>
                            <span class="font-bold text-slate-900 dark:text-white" x-text="att.clock_in"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 text-[11px] block">Clock Out</span>
                            <span class="font-bold text-slate-900 dark:text-white" x-text="att.clock_out"></span>
                        </div>
                    </div>
                    <template x-if="att.hours !== null">
                        <p class="text-xs font-semibold text-teal-800 dark:text-teal-300 mt-2">
                            Total rendered: <span x-text="att.hours"></span> hrs
                        </p>
                    </template>
                </div>
            </template>

            {{-- 5. Absence Requests on this date --}}
            <template x-for="abs in (currentSelectedDay.markers || []).filter(m => m.type === 'absence_request')" :key="abs.id">
                <div class="p-3.5 rounded-2xl bg-white dark:bg-gray-850 border border-slate-200/80 dark:border-gray-800 shadow-xs space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300" x-text="abs.request_type === 'early_leave' ? 'Early Leave Request' : 'Absence Request'"></span>
                        <span 
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                            :class="{
                                'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800': abs.status === 'pending',
                                'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800': abs.status === 'approved',
                                'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800': abs.status === 'rejected',
                            }"
                            x-text="abs.status"
                        ></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Reason</span>
                        <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed font-medium" x-text="abs.reason"></p>
                    </div>
                    <template x-if="abs.remarks">
                        <div class="p-2 rounded-xl bg-slate-50 dark:bg-gray-800 text-xs">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Supervisor Remarks</span>
                            <p class="text-slate-600 dark:text-slate-300" x-text="abs.remarks"></p>
                        </div>
                    </template>
                    <template x-if="abs.has_attachment">
                        <a :href="abs.attachment_url" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0B5694] dark:text-blue-400 hover:underline">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span>Preview Attachment</span>
                        </a>
                    </template>

                    {{-- Actions if Pending --}}
                    <template x-if="abs.status === 'pending'">
                        <div class="pt-2 flex items-center gap-2 border-t border-slate-100 dark:border-gray-800">
                            <button 
                                type="button" 
                                @click="openEditAbsence(abs)"
                                class="flex-1 py-1.5 px-3 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-300 transition-colors cursor-pointer text-center"
                            >
                                Edit
                            </button>
                            <button 
                                type="button" 
                                @click="openWithdrawAbsence(abs)"
                                class="flex-1 py-1.5 px-3 rounded-xl text-xs font-semibold bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 transition-colors cursor-pointer text-center"
                            >
                                Withdraw
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            {{-- 6. Quiet Empty State when no scheduled hours and no events (e.g. weekend) --}}
            <template x-if="!currentSelectedDay.scheduled_hours && !currentSelectedDay.has_data">
                <p class="text-xs text-slate-400 dark:text-slate-500 py-1" x-text="currentSelectedDay.is_weekend ? 'Weekend. Off duty.' : 'Regular workday. Nothing scheduled.'"></p>
            </template>
        </div>
    </div>

    {{-- Bottom Action: Request Absence / Early Leave Button --}}
    <div class="mt-3.5 pt-3 border-t border-slate-100 dark:border-gray-800">
        <template x-if="currentSelectedDay.can_request_absence">
            <button 
                type="button" 
                @click="openRequestAbsence(currentSelectedDay.date)"
                class="w-full py-2.5 px-4 rounded-xl text-xs font-semibold text-white bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 transition-all shadow-xs cursor-pointer flex items-center justify-center gap-2"
            >
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                <span x-text="currentSelectedDay.is_today ? 'Request Early Leave' : 'Request Absence'"></span>
            </button>
        </template>
        <template x-if="!currentSelectedDay.can_request_absence && !currentSelectedDay.is_past && !currentSelectedDay.markers.some(m => m.type === 'absence_request')">
            <p class="text-[11px] text-slate-400 text-center font-medium">
                No leave requests required for non-working days.
            </p>
        </template>
    </div>
</div>
