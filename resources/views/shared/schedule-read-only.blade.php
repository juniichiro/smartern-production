@extends('layouts.app')

@section('title', 'Schedule')

@section('content')
<div class="flex min-h-screen bg-gray-50 dark:bg-gray-950 font-sans text-[14px]">
    {{-- Sidebar --}}
    <x-sidebar :portalType="$portalType" :userName="$userName" :userRole="$userRole" />

    {{-- Main Content --}}
    <main class="flex-1 min-w-0 ml-0 lg:ml-64 mr-0 lg:mr-80 p-4 md:p-6 lg:p-10 pt-20 lg:pt-10 text-left transition-all duration-300"
          x-data="calendarManager(JSON.parse('{{ addslashes($schedulesJson) }}'))">
        
        {{-- Header --}}
        <x-page-header 
            title="Schedule" 
            subtitle="Calendar Overrides, Holidays & Task Deadlines" 
        />

        {{-- CALENDAR CONTAINER --}}
        <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 p-6 lg:p-10 overflow-hidden flex flex-col">
            
            {{-- CALENDAR MONTH NAVIGATION HEADER --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div>
                        <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-gray-100 tracking-tight uppercase" x-text="monthName()"></h3>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 font-semibold">Click on any marked date to view override details.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="prevMonth()" class="p-3 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-2xl transition-all border border-gray-100 dark:border-gray-700 active:scale-95 min-w-[44px] min-h-[44px] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                        <button @click="nextMonth()" class="p-3 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-2xl transition-all border border-gray-100 dark:border-gray-700 active:scale-95 min-w-[44px] min-h-[44px] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- DESKTOP CALENDAR GRID (lg+) --}}
            <div class="hidden lg:block">
                <div class="border border-gray-100 dark:border-gray-850 rounded-[25px] overflow-hidden shadow-inner bg-gray-50/50 dark:bg-gray-950/20">
                    
                    {{-- Week Days Labels --}}
                    <div class="grid grid-cols-7 border-b border-gray-100 dark:border-gray-850 text-center bg-gray-50 dark:bg-gray-800/40">
                        <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']">
                            <div class="py-4 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest" x-text="day"></div>
                        </template>
                    </div>

                    {{-- Date Grid Cells --}}
                    <div class="grid grid-cols-7 divide-x divide-y divide-gray-100 dark:divide-gray-850">
                        
                        {{-- Blank offsets --}}
                        <template x-for="blank in blankDays">
                            <div class="min-h-[120px] bg-gray-100/30 dark:bg-gray-900/10"></div>
                        </template>

                        {{-- Days in Month --}}
                        <template x-for="day in daysInMonth" :key="day.dateStr">
                            <div @click="selectDate(day)"
                                 class="min-h-[120px] p-4 bg-white dark:bg-gray-900 flex flex-col justify-between transition-all group border-t border-l border-gray-100 dark:border-gray-850 first-of-type:border-t-0 cursor-pointer hover:bg-blue-50/20 dark:hover:bg-blue-900/5"
                                 :class="{
                                    'opacity-50 cursor-not-allowed': isPastDate(day.dateStr) && day.events.length === 0,
                                    'ring-2 ring-blue-500 ring-inset z-10': isToday(day.dateStr)
                                 }">
                                
                                {{-- Day number and base type --}}
                                <div class="flex justify-between items-start">
                                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 transition-colors uppercase tracking-[0.1em]"
                                          :class="day.events.length > 0 ? 'group-hover:text-blue-500' : ''"
                                          x-text="getCellLabel(day)"></span>
                                    <span class="text-lg font-black text-gray-850 dark:text-gray-100 leading-none transition-transform"
                                          :class="day.events.length > 0 ? 'group-hover:scale-110' : ''"
                                          x-text="day.day"></span>
                                </div>

                                {{-- Override Indicators & Tasks --}}
                                <div class="mt-4 space-y-2 flex-1 flex flex-col justify-end">
                                    <template x-for="(event, index) in day.events" :key="event.id || event.title">
                                        <div x-show="index < 2">
                                            <template x-if="event.type === 'task'">
                                                <a :href="'{{ $portalType }}' === 'Intern' ? `/intern/tasks/${event.id}` : `/supervisor/tasks/review/${event.id}`"
                                                   @click.stop
                                                   class="block rounded-2xl p-3 text-xs font-bold text-left leading-tight tracking-tight shadow-sm border bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900/50 hover:scale-[1.02] active:scale-[0.98] transition-all hover:bg-amber-100/50 dark:hover:bg-amber-950/50">
                                                    <div class="font-black uppercase tracking-widest text-[9px] mb-1 opacity-90 flex justify-between items-center">
                                                        <span>Task Due</span>
                                                        <template x-if="event.is_overdue">
                                                            <span class="text-[8px] bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 px-1.5 py-0.5 rounded-full font-black animate-pulse uppercase">Overdue</span>
                                                        </template>
                                                    </div>
                                                    <div class="truncate font-bold" x-text="event.title"></div>
                                                </a>
                                            </template>

                                            <template x-if="['holiday', 'suspension', 'work_day'].includes(event.type)">
                                                <div class="rounded-2xl p-3 text-xs font-bold text-left leading-tight tracking-tight shadow-sm border"
                                                     :class="{
                                                         'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50': event.type === 'holiday',
                                                         'bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border-purple-100 dark:border-purple-900/50': event.type === 'suspension',
                                                         'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900/50': event.type === 'work_day'
                                                     }">
                                                    <div class="font-black uppercase tracking-widest text-[9px] mb-1 opacity-90"
                                                         x-text="event.type === 'holiday' ? 'Holiday' : (event.type === 'suspension' ? 'Suspended' : 'Weekend Work')"></div>
                                                    <div class="truncate font-bold" x-text="event.description"></div>
                                                </div>
                                            </template>

                                            <template x-if="event.type === 'absence_request'">
                                                <div class="rounded-2xl p-3 text-xs font-bold text-left leading-tight tracking-tight shadow-sm border"
                                                     :class="{
                                                         'bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 border-orange-100 dark:border-orange-900/50': event.status === 'pending',
                                                         'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50': event.status === 'approved',
                                                         'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/50': event.status === 'rejected'
                                                     }">
                                                    <div class="font-black uppercase tracking-widest text-[9px] mb-1 opacity-90 flex justify-between items-center">
                                                        <span x-text="(event.request_type === 'early_leave' ? 'Early Leave ' : 'Absence ') + (event.status === 'pending' ? 'Pending' : (event.status === 'approved' ? 'Approved' : 'Rejected'))"></span>
                                                        <template x-if="event.status === 'pending'">
                                                            <div class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></div>
                                                        </template>
                                                    </div>
                                                    <div class="truncate font-bold" x-text="('{{ $portalType }}' !== 'Intern' ? event.intern_name + ': ' : '') + event.reason"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="day.events.length > 2">
                                        <div class="rounded-2xl p-2 text-center text-[10px] font-black uppercase tracking-wider bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border border-gray-100 dark:border-gray-800/80 mt-1 select-none">
                                            + <span x-text="day.events.length - 2"></span> more requests
                                        </div>
                                    </template>
                                </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- MOBILE MONTH + AGENDA VIEW (< lg) - Google Calendar-style --}}
            <div class="lg:hidden -mx-4 sm:-mx-6 px-4 sm:px-6">
                {{-- Month grid (compact) --}}
                <div class="bg-white dark:bg-gray-900 rounded-[20px] border border-gray-100 dark:border-gray-800 overflow-hidden shadow-sm mb-5">
                    <div class="grid grid-cols-7 border-b border-gray-50 dark:border-gray-800 text-center bg-gray-50 dark:bg-gray-800/30">
                        <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']">
                            <div class="py-2.5 text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest" x-text="day"></div>
                        </template>
                    </div>
                    <div class="grid grid-cols-7">
                        <template x-for="blank in blankDays">
                            <div class="bg-gray-50/30 dark:bg-gray-900/20"></div>
                        </template>
                        <template x-for="(day, i) in daysInMonth" :key="day.dateStr">
                            <button @click="mobileSelectedDay = day.dateStr; mobileSelectedIndex = i"
                                    class="relative flex flex-col items-center justify-center py-2.5 transition-all active:scale-90"
                                    :class="mobileSelectedDay === day.dateStr
                                        ? 'bg-[#0B5694]/10 dark:bg-blue-600/20'
                                        : isPastDate(day.dateStr) ? 'opacity-40' : 'hover:bg-gray-50 dark:hover:bg-gray-800/30'">
                                <span class="text-sm font-bold leading-none"
                                      :class="mobileSelectedDay === day.dateStr
                                          ? 'text-[#0B5694] dark:text-blue-400'
                                          : day.isWeekend ? 'text-gray-300 dark:text-gray-600' : 'text-gray-700 dark:text-gray-300'"
                                      x-text="day.day"></span>
                                {{-- Event dot indicators --}}
                                <div class="flex items-center gap-0.5 mt-1 h-1.5">
                                    <template x-for="(evt, ei) in (day.events || []).slice(0, 3)" :key="ei">
                                        <div class="w-1 h-1 rounded-full"
                                             :class="{
                                                 'bg-emerald-500': evt.type === 'holiday',
                                                 'bg-purple-500': evt.type === 'suspension',
                                                 'bg-blue-500': evt.type === 'work_day',
                                                 'bg-amber-500': evt.type === 'task',
                                                 'bg-orange-500': evt.type === 'absence_request' && evt.status === 'pending',
                                                 'bg-emerald-400': evt.type === 'absence_request' && evt.status === 'approved',
                                                 'bg-rose-400': evt.type === 'absence_request' && evt.status === 'rejected'
                                             }"></div>
                                    </template>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Selected date label --}}
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight"
                            x-text="mobileSelectedDay ? formatDateLong(mobileSelectedDay) : ''"></h4>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5"
                           x-text="mobileSelectedDay && getMobileDayEvents().length > 0 ? getMobileDayEvents().length + ' event(s)' : ''"></p>
                    </div>
                </div>

                {{-- Agenda list for selected day --}}
                <div class="space-y-2.5">
                    <template x-if="getMobileDayEvents().length === 0 && !isPastDate(mobileSelectedDay)">
                        <div class="flex flex-col items-center justify-center py-10 px-4 bg-gray-50 dark:bg-gray-800/30 rounded-3xl">
                            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-2xl flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            </div>
                            <p class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">No events scheduled</p>
                        </div>
                    </template>

                    <template x-if="getMobileDayEvents().length === 0 && isPastDate(mobileSelectedDay)">
                        <div class="flex flex-col items-center justify-center py-10 px-4 bg-gray-50 dark:bg-gray-800/30 rounded-3xl">
                            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 text-gray-300 dark:text-gray-600 rounded-2xl flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <p class="text-sm font-bold text-gray-300 dark:text-gray-600 uppercase tracking-wider">No records for this date</p>
                        </div>
                    </template>

                    <template x-for="event in getMobileDayEvents()" :key="event.id || event.description || event.reason">
                        <div @click="openMobileEvent(event)"
                             class="flex items-start gap-3 sm:gap-4 p-4 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md active:scale-[0.99] transition-all cursor-pointer">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 mt-0.5"
                                 :class="{
                                     'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400': event.type === 'holiday',
                                     'bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400': event.type === 'suspension',
                                     'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400': event.type === 'work_day',
                                     'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400': event.type === 'task',
                                     'bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400': event.type === 'absence_request' && event.status === 'pending',
                                     'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400': event.type === 'absence_request' && event.status === 'approved',
                                     'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400': event.type === 'absence_request' && event.status === 'rejected'
                                 }">
                                <template x-if="event.type === 'holiday' || event.type === 'suspension' || event.type === 'work_day'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </template>
                                <template x-if="event.type === 'task'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="16" x="4" y="4" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                                </template>
                                <template x-if="event.type === 'absence_request'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-[9px] font-black uppercase tracking-widest"
                                          :class="{
                                              'text-emerald-600 dark:text-emerald-400': event.type === 'holiday',
                                              'text-purple-600 dark:text-purple-400': event.type === 'suspension',
                                              'text-blue-600 dark:text-blue-400': event.type === 'work_day',
                                              'text-amber-600 dark:text-amber-400': event.type === 'task',
                                              'text-orange-600 dark:text-orange-400': event.type === 'absence_request' && event.status === 'pending',
                                              'text-emerald-600 dark:text-emerald-400': event.type === 'absence_request' && event.status === 'approved',
                                              'text-rose-600 dark:text-rose-400': event.type === 'absence_request' && event.status === 'rejected'
                                          }">
                                        <template x-if="event.type === 'holiday'"><span>Holiday</span></template>
                                        <template x-if="event.type === 'suspension'"><span>Suspended</span></template>
                                        <template x-if="event.type === 'work_day'"><span>Weekend Work</span></template>
                                        <template x-if="event.type === 'task'"><span>Task Due</span></template>
                                        <template x-if="event.type === 'absence_request'">
                                            <span x-text="(event.request_type === 'early_leave' ? 'Early Leave' : 'Absence') + ' - ' + event.status"></span>
                                        </template>
                                    </span>
                                    <template x-if="event.type === 'absence_request' && event.status === 'pending'">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse shrink-0"></span>
                                    </template>
                                </div>
                                <template x-if="event.type === 'task'">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate" x-text="event.title"></p>
                                </template>
                                <template x-if="event.type !== 'task' && event.type !== 'absence_request'">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate" x-text="event.description"></p>
                                </template>
                                <template x-if="event.type === 'absence_request'">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate" x-text="('{{ $portalType }}' !== 'Intern' ? event.intern_name + ': ' : '') + event.reason"></p>
                                </template>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-0.5 truncate">
                                    <template x-if="event.type === 'holiday'"><span>Non-working day</span></template>
                                    <template x-if="event.type === 'suspension'"><span>Work suspended</span></template>
                                    <template x-if="event.type === 'work_day'"><span>Make-up day</span></template>
                                    <template x-if="event.type === 'task'"><span>Task assignment</span></template>
                                    <template x-if="event.type === 'absence_request'">
                                        <span x-text="event.status === 'pending' ? 'Awaiting review' : (event.status === 'approved' ? 'Approved' : 'Rejected')"></span>
                                    </template>
                                </p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-gray-300 dark:text-gray-600 mt-2"><path d="m9 18 6-6-6-6"/></svg>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- ABSENCE REQUEST & REVIEW MODALS --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-md"
             style="z-index: 1000;"
             x-cloak>
            
             <div @click.away="showModal = false"
                  class="bg-white dark:bg-[#090D16] w-full max-w-xl min-h-[520px] rounded-[40px] shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden animate-in zoom-in-95 duration-300 flex flex-col justify-between">
                
                {{-- Modal Header --}}
                <header class="p-6 border-b border-gray-250 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-[#0D121F] shrink-0">
                    <div>
                        <h4 class="text-sm font-black text-gray-850 dark:text-white uppercase tracking-widest" x-text="modalTitle"></h4>
                        <p class="text-xs text-[#0B5694] dark:text-blue-400 font-bold uppercase mt-1" x-text="selectedDateStrFormatted"></p>
                    </div>
                    <button @click="showModal = false" class="p-2.5 bg-gray-200 dark:bg-gray-850 hover:bg-gray-300 dark:hover:bg-gray-850 text-gray-500 dark:text-gray-400 rounded-xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </header>

                <div class="p-8 flex-1 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    {{-- 1. INTERN: Request Absence Form --}}
                    <template x-if="'{{ $portalType }}' === 'Intern' && modalMode === 'request'">
                        <form :action="isEditingAbsence ? `/intern/schedule/absence/${absenceToEdit.id}` : '{{ route('intern.schedule.absence.store') }}'" 
                              method="POST" 
                              enctype="multipart/form-data" 
                              class="space-y-6">
                            @csrf
                            <template x-if="isEditingAbsence">
                                <input type="hidden" name="_method" value="PUT">
                            </template>
                            
                            <input type="hidden" name="date" :value="selectedDateStr">
                            
                            <div>
                                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3 block">Reason for Absence</label>
                                <textarea name="reason" rows="4" required
                                          x-model="absenceFormData.reason"
                                          class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-5 text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#0B5694] transition-all placeholder:text-gray-300 dark:placeholder:text-gray-700"
                                          placeholder="Please provide a clear and professional reason for your absence..."></textarea>
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3 block">Supporting Document (Optional)</label>
                                <div class="relative group">
                                    <input type="file" name="attachment" id="absence_attachment" class="hidden" accept=".pdf,.jpg,.jpeg,.png"
                                           @change="attachmentName = $event.target.files[0] ? $event.target.files[0].name : ''">
                                    <label for="absence_attachment" class="flex items-center justify-between w-full px-6 py-4 border border-dashed rounded-2xl cursor-pointer transition-all"
                                           :class="attachmentName ? 'border-emerald-500 bg-emerald-50/30 dark:bg-emerald-950/10' : 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800 group-hover:border-[#0B5694]'">
                                        <div class="flex items-center gap-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" 
                                                 :class="attachmentName ? 'text-emerald-500' : 'text-gray-400 group-hover:text-[#0B5694]'"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-white truncate max-w-[200px]"
                                                  x-text="attachmentName ? attachmentName : 'Upload Reference (PDF/JPG)'"></span>
                                        </div>
                                        <span class="text-[10px] font-black uppercase tracking-widest"
                                              :class="attachmentName ? 'text-emerald-500' : 'text-gray-300 dark:text-gray-700'"
                                              x-text="attachmentName ? 'Selected' : 'Max 5MB'"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="pt-4 flex flex-col gap-3">
                                <button type="submit" class="w-full py-4 bg-[#0B5694] text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                    <span x-text="isEditingAbsence ? 'Update Request' : 'Submit Request'"></span>
                                </button>
                                <button type="button" @click="showModal = false" class="w-full py-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs font-black uppercase tracking-widest transition-colors">
                                    Discard
                                </button>
                            </div>
                        </form>
                    </template>

                    {{-- 2. SUPERVISOR/HEAD: Review Absence Form --}}
                    <template x-if="('{{ $portalType }}' === 'Supervisor' || '{{ $portalType }}' === 'Head Supervisor') && modalMode === 'review'">
                        <div class="space-y-6">
                            <template x-for="event in selectedEvents.filter(e => e.type === 'absence_request')" :key="event.id">
                                <div class="rounded-3xl p-6 border text-left bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800 space-y-4 mb-4 last:mb-0 shadow-sm">
                                    {{-- Request Info Header --}}
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800/80 pb-3">
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none mb-1.5"
                                               x-text="event.request_type === 'early_leave' ? 'Early Leave Request' : 'Absence Request'"></p>
                                            <h5 class="font-extrabold text-gray-850 dark:text-gray-100 text-sm truncate" x-text="event.intern_name"></h5>
                                        </div>
                                        <span class="text-[9px] font-black uppercase px-3 py-1.5 rounded-full border w-fit"
                                              :class="{
                                                  'bg-orange-50 dark:bg-orange-950/20 text-orange-600 dark:text-orange-400 border-orange-100 dark:border-orange-900/30': event.status === 'pending',
                                                  'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30': event.status === 'approved',
                                                  'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/30': event.status === 'rejected'
                                              }"
                                              x-text="event.status"></span>
                                    </div>
                                    
                                    {{-- Reason --}}
                                    <div class="space-y-1">
                                        <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Reason / Details</span>
                                        <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed font-semibold italic" x-text="'&quot;' + event.reason + '&quot;'"></p>
                                    </div>
                                    
                                    {{-- Attachment actions inside the same container --}}
                                    <template x-if="event.has_attachment">
                                        <a :href="('{{ $portalType }}' === 'Supervisor' ? '/supervisor' : ('{{ $portalType }}' === 'Head Supervisor' ? '/head-supervisor' : '/intern')) + `/schedule/absence/${event.id}/attachment`" target="_blank"
                                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-[#0B5694] dark:text-blue-400 hover:bg-blue-100 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            Preview Attachment
                                        </a>
                                    </template>

                                    {{-- Pending actions form inside the container --}}
                                    <template x-if="event.status === 'pending'">
                                        <form :action="('{{ $portalType }}' === 'Supervisor' ? `/supervisor/schedule/absence/${event.id}` : `/head-supervisor/schedule/absence/${event.id}`)" method="POST" class="space-y-4 pt-3 border-t border-gray-100 dark:border-gray-800/80">
                                            @csrf
                                            @method('PATCH')
                                            <div>
                                                <label class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5 block">Review Remarks</label>
                                                <textarea name="supervisor_remarks" rows="2"
                                                          class="w-full bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-850 rounded-2xl p-4 text-xs text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#0B5694] transition-all placeholder:text-gray-300 dark:placeholder:text-gray-700 resize-none font-semibold"
                                                          placeholder="Optional remarks for the intern..."></textarea>
                                            </div>
                                            <div class="flex gap-2.5">
                                                <button type="submit" name="status" value="rejected" class="flex-1 py-3.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30 rounded-xl font-bold text-xs uppercase tracking-wider transition-all">
                                                    Reject
                                                </button>
                                                <button type="submit" name="status" value="approved" class="flex-1 py-3.5 bg-[#0B5694] hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-md hover:scale-[1.01] active:scale-95 transition-all">
                                                    Approve
                                                </button>
                                            </div>
                                        </form>
                                    </template>

                                    {{-- Processed remarks inside the container --}}
                                    <template x-if="event.status !== 'pending'">
                                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800/80">
                                            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">Review Remarks</span>
                                            <p class="text-xs text-gray-700 dark:text-gray-200 font-bold" x-text="event.remarks || 'No remarks provided.'"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- 3. SHARED: Generic Event Details --}}
                    <template x-if="modalMode === 'view'">
                        <div class="space-y-6">
                            {{-- Calendar Exceptions & Tasks (Explicitly filtered) --}}
                            <template x-for="event in selectedEvents.filter(e => ['holiday', 'suspension', 'work_day', 'task'].includes(e.type))" :key="event.id || event.title">
                                <div class="rounded-3xl p-6 border text-left"
                                     :class="{
                                         'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/40': event.type === 'holiday',
                                         'bg-purple-50 dark:bg-purple-950/20 text-purple-700 dark:text-purple-400 border-purple-100 dark:border-purple-800/40': event.type === 'suspension',
                                         'bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-800/40': event.type === 'work_day',
                                         'bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 border-amber-100 dark:border-amber-800/40': event.type === 'task'
                                     }">
                                    <span class="text-[9px] font-black uppercase tracking-[0.2em] px-3 py-1.5 rounded-full border bg-white dark:bg-gray-900 border-inherit">
                                        <template x-if="event.type === 'holiday'">
                                            <span><span class="sm:hidden">Holiday</span><span class="hidden sm:inline">Holiday Exemption</span></span>
                                        </template>
                                        <template x-if="event.type === 'suspension'">
                                            <span><span class="sm:hidden">Suspension</span><span class="hidden sm:inline">Suspension Override</span></span>
                                        </template>
                                        <template x-if="event.type === 'work_day'">
                                            <span><span class="sm:hidden">Weekend</span><span class="hidden sm:inline">Weekend Shift Override</span></span>
                                        </template>
                                        <template x-if="event.type === 'task'">
                                            <span><span class="sm:hidden">Task</span><span class="hidden sm:inline">Task Assignment</span></span>
                                        </template>
                                    </span>
                                    <h5 class="text-xl font-black uppercase tracking-tight mt-6 leading-tight" x-text="event.description || event.title"></h5>
                                    
                                    {{-- Task details --}}
                                    <template x-if="event.type === 'task'">
                                        <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xs font-bold uppercase tracking-widest opacity-80">Status:</span>
                                                <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full border bg-white dark:bg-gray-900 border-inherit"
                                                      x-text="event.status"></span>
                                            </div>
                                            <template x-if="'{{ $portalType }}' === 'Intern'">
                                                <a :href="`/intern/tasks/${event.id}`" class="text-xs font-bold underline hover:opacity-80 transition-opacity">
                                                    Open Submission &rarr;
                                                </a>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Absence Requests (Intern View) --}}
                            <template x-for="event in selectedEvents.filter(e => e.type === 'absence_request')" :key="'abs-' + event.id">
                                <div class="rounded-3xl p-6 border text-left bg-gray-50/50 dark:bg-gray-800/20 border-gray-100 dark:border-gray-800">
                                    <div class="flex justify-between items-start mb-6">
                                        <span class="text-[9px] font-black uppercase tracking-[0.2em] px-3 py-1.5 rounded-full border bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800"
                                              x-text="event.request_type === 'early_leave' ? 'Early Leave Request' : 'Absence Request'"></span>
                                        <span class="text-[9px] font-black uppercase px-3 py-1.5 rounded-full border"
                                              :class="{
                                                  'bg-orange-50 text-orange-600 border-orange-100': event.status === 'pending',
                                                  'bg-emerald-50 text-emerald-600 border-emerald-100': event.status === 'approved',
                                                  'bg-rose-50 text-rose-600 border-rose-100': event.status === 'rejected'
                                              }"
                                              x-text="event.status"></span>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1 block">Your Reason</label>
                                            <p class="text-sm text-gray-700 dark:text-gray-200 font-bold" x-text="event.reason"></p>
                                        </div>
                                        
                                        <template x-if="event.remarks">
                                            <div>
                                                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1 block">Supervisor Remarks</label>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium italic" x-text="event.remarks"></p>
                                            </div>
                                        </template>

                                        <div class="pt-4 flex flex-wrap gap-2">
                                            <template x-if="event.has_attachment">
                                                 <a :href="('{{ $portalType }}' === 'Supervisor' ? '/supervisor' : ('{{ $portalType }}' === 'Head Supervisor' ? '/head-supervisor' : '/intern')) + `/schedule/absence/${event.id}/attachment`" target="_blank"
                                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-[#0B5694] dark:text-blue-400 hover:bg-blue-100 transition-all">
                                                     <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                     Preview Attachment
                                                 </a>
                                            </template>

                                                    <template x-if="event.status === 'pending'">
                                                        <div class="flex gap-2 w-full">
                                                            <button @click="startEditingAbsence(event)" 
                                                                    class="flex-1 py-3 bg-blue-50 text-[#0B5694] border border-blue-100 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-100 transition-all">
                                                                Edit
                                                            </button>
                                                            <button @click="confirmRevoke(event)" 
                                                                    class="flex-1 py-3 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-100 transition-all">
                                                                Revoke
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <button @click="showModal = false"
                                            class="w-full py-4 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-250 dark:hover:bg-gray-700 rounded-2xl font-black text-xs uppercase tracking-widest active:scale-95 transition-all">
                                        Close Details
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                    {{-- 4. INTERN: Revoke Confirmation --}}
                    <template x-if="'{{ $portalType }}' === 'Intern' && modalMode === 'revoke_confirm'">
                        <form :action="'{{ url('/intern/schedule/absence') }}/' + (absenceToRevoke ? absenceToRevoke.id : '')" method="POST" class="text-center space-y-6">
                            @csrf
                            @method('DELETE')
                            
                            <div class="w-20 h-20 bg-rose-50 dark:bg-rose-950/30 rounded-full flex items-center justify-center mx-auto mb-6 border border-rose-100 dark:border-rose-900/50">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-rose-600 dark:text-rose-400"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            </div>

                            <div class="space-y-2">
                                <h4 class="text-lg font-black text-gray-850 dark:text-white uppercase tracking-tight">Withdraw Request?</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium leading-relaxed px-4">
                                    This will permanently remove your <span class="text-rose-600 font-bold italic" x-text="absenceToRevoke?.request_type === 'early_leave' ? 'early leave' : 'absence'"></span> request from the schedule.
                                </p>
                            </div>

                            <div class="pt-4 flex flex-col gap-3">
                                <button type="submit" class="w-full py-4 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-rose-500/20 active:scale-95 transition-all">
                                    Withdraw Permanently
                                </button>
                                <button type="button" @click="modalMode = 'view'" class="w-full py-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs font-black uppercase tracking-widest transition-colors">
                                    Keep Request
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>

    </main>

    <x-notification-sidebar :notifications="$notifications" />
</div>

<script>
    function calendarManager(initialEvents) {
        return {
            currentDate: new Date(),
            daysInMonth: [],
            blankDays: [],
            events: initialEvents || {},
            showModal: false,
            showRevokeModal: false,
            modalTitle: '',
            modalMode: 'view', // 'view', 'request', 'review'
            selectedDateStr: '',
            selectedDateStrFormatted: '',
            selectedEvents: [],
            selectedEvent: null,
            deleteActionUrl: '',
            mobileSelectedDay: '',
            mobileSelectedIndex: 0,

            // Absence Form State
            isEditingAbsence: false,
            absenceToEdit: null,
            absenceToRevoke: null,
            attachmentName: '',
            absenceFormData: {
                reason: ''
            },



            init() {
                this.generateCalendar();
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');
                this.mobileSelectedDay = `${yyyy}-${mm}-${dd}`;
                this.mobileSelectedIndex = this.daysInMonth.findIndex(d => d.dateStr === this.mobileSelectedDay);
                if (this.mobileSelectedIndex < 0) this.mobileSelectedIndex = 0;
            },



            prevMonth() {
                const newDate = new Date(this.currentDate);
                newDate.setMonth(newDate.getMonth() - 1);
                this.currentDate = newDate;
                this.generateCalendar();
            },

            nextMonth() {
                const newDate = new Date(this.currentDate);
                newDate.setMonth(newDate.getMonth() + 1);
                this.currentDate = newDate;
                this.generateCalendar();
            },

            monthName() {
                return this.currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
            },

            generateCalendar() {
                const year = this.currentDate.getFullYear();
                const month = this.currentDate.getMonth();
                const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

                const firstDay = new Date(year, month, 1).getDay();
                const totalDays = new Date(year, month + 1, 0).getDate();

                this.blankDays = Array.from({ length: firstDay }, (_, i) => i);

                this.daysInMonth = Array.from({ length: totalDays }, (_, i) => {
                    const dayNum = i + 1;
                    const dateObj = new Date(year, month, dayNum);
                    
                    const yyyy = dateObj.getFullYear();
                    const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const dd = String(dateObj.getDate()).padStart(2, '0');
                    const dateStr = `${yyyy}-${mm}-${dd}`;

                    const isWeekend = dateObj.getDay() === 0 || dateObj.getDay() === 6;
                    const events = this.events[dateStr] || [];

                    return {
                        day: dayNum,
                        dateStr: dateStr,
                        isWeekend: isWeekend,
                        dayNameShort: dayNames[dateObj.getDay()],
                        events: events
                    };
                });
                if (this.mobileSelectedDay && !this.daysInMonth.some(d => d.dateStr === this.mobileSelectedDay)) {
                    this.mobileSelectedDay = this.daysInMonth[0]?.dateStr || '';
                    this.mobileSelectedIndex = 0;
                }
                this.mobileSelectedIndex = Math.max(0, this.daysInMonth.findIndex(d => d.dateStr === this.mobileSelectedDay));
            },

            getCellLabel(day) {
                if (day.events && day.events.length > 0) {
                    // Only show status override if APPROVED or REJECTED. 
                    // Pending requests still show as "Workday" or "Weekend" at the top level.
                    const absence = day.events.find(e => e.type === 'absence_request' && e.status !== 'pending');
                    if (absence) {
                        const prefix = absence.request_type === 'early_leave' ? 'Leave ' : 'Absence ';
                        return prefix + (absence.status === 'approved' ? '(A)' : '(R)');
                    }
                    const primary = day.events.find(e => e.type === 'holiday' || e.type === 'suspension' || e.type === 'work_day');
                    if (primary) {
                        return primary.type === 'holiday' ? 'Holiday' : (primary.type === 'suspension' ? 'Suspended' : 'Weekend Work');
                    }
                    if (day.events.some(e => e.type === 'task')) return 'Task Due';
                }
                return day.isWeekend ? 'Weekend' : 'Workday';
            },

            isToday(dateStr) {
                return dateStr === new Date().toISOString().split('T')[0];
            },

            isPastDate(dateStr) {
                return dateStr < new Date().toISOString().split('T')[0];
            },

            startEditingAbsence(event) {
                this.isEditingAbsence = true;
                this.absenceToEdit = event;
                this.absenceFormData.reason = event.reason;
                this.attachmentName = event.has_attachment ? 'Existing Document (Uploaded)' : '';
                this.modalMode = 'request';
                this.modalTitle = event.request_type === 'early_leave' ? 'Edit Early Leave Request' : 'Edit Absence Request';
                this.showModal = true;
            },

            confirmRevoke(event) {
                this.absenceToRevoke = event;
                this.modalMode = 'revoke_confirm';
                this.modalTitle = 'Withdraw Request';
            },

            selectDate(day) {
                this.selectedDateStr = day.dateStr;
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                this.selectedDateStrFormatted = new Date(day.dateStr).toLocaleDateString(undefined, options);
                this.selectedEvents = day.events;

                const hasAbsence = day.events.some(e => e.type === 'absence_request');
                const hasExceptions = day.events.some(e => e.type !== 'absence_request');

                // Reset editing state
                this.isEditingAbsence = false;
                this.absenceToEdit = null;
                this.absenceFormData.reason = '';
                this.attachmentName = '';

                if ('{{ $portalType }}' === 'Intern') {
                    if (hasAbsence || hasExceptions) {
                        this.modalMode = 'view';
                        this.modalTitle = 'Event Details';
                        this.showModal = true;
                    } else if (!this.isPastDate(day.dateStr)) {
                        this.modalMode = 'request';
                        this.modalTitle = this.isToday(day.dateStr) ? 'Request Early Leave' : 'Request Absence';
                        this.showModal = true;
                    }
                } else if ('{{ $portalType }}' === 'Supervisor' || '{{ $portalType }}' === 'Head Supervisor') {
                    if (hasAbsence) {
                        this.modalMode = 'review';
                        this.modalTitle = 'Review Request';
                        this.showModal = true;
                    } else if (hasExceptions) {
                        this.modalMode = 'view';
                        this.modalTitle = 'Event Details';
                        this.showModal = true;
                    }
                }
            },

            openMobileEvent(event) {
                this.selectedDateStr = this.mobileSelectedDay;
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                this.selectedDateStrFormatted = new Date(this.mobileSelectedDay).toLocaleDateString(undefined, options);
                this.selectedEvents = [event];

                this.isEditingAbsence = false;
                this.absenceToEdit = null;
                this.absenceFormData.reason = '';

                if (event.type === 'absence_request') {
                    this.modalMode = 'review';
                    this.modalTitle = 'Review Request';
                } else {
                    this.modalMode = 'view';
                    this.modalTitle = 'Event Details';
                }
                this.showModal = true;
            },

            getMobileDayEvents() {
                if (!this.mobileSelectedDay) return [];
                return this.events[this.mobileSelectedDay] || [];
            },

            formatDateLong(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr + 'T00:00:00');
                return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
            }
        };
    }
</script>
@endsection
