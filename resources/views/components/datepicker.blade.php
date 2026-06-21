@props([
    'name',
    'id' => null,
    'value' => '',
    'label' => null,
    'required' => false,
    'placeholder' => 'Select Date',
    'type' => 'date', // 'date' or 'datetime-local'
    'disabled' => null,
])

<div x-data="customDatePicker({
    value: '{{ $value }}',
    type: '{{ $type }}'
})" 
     x-modelable="value"
     {{ $attributes->whereStartsWith('x-model') }}
     @click.away="show = false"
     @keydown.escape="show = false"
     @keydown.enter.prevent="show = false"
     class="relative w-full">
     
    @if($label)
        <label class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider ml-1 mb-2 block">
            {{ $label }}
        </label>
    @endif

    <!-- Input Trigger -->
    <div class="relative group">
        <input type="text" 
               name="{{ $name }}"
               id="{{ $id ?? $name }}"
               x-model="displayValue"
               @click="if (!{{ $disabled ?? 'false' }}) show = !show"
               readonly
               {{ $required ? 'required' : '' }}
               :disabled="{{ $disabled ?? 'false' }}"
               placeholder="{{ $placeholder }}"
               :class="[
                   isValid ? 'border-[#0B5694] dark:border-blue-500 ring-2 ring-[#0B5694]/10' : 'border-gray-200 dark:border-gray-700',
                   ({{ $disabled ?? 'false' }}) ? 'opacity-60 cursor-not-allowed pointer-events-none' : 'cursor-pointer'
               ]"
               class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 outline-none transition-all">
        
        <div class="absolute inset-y-0 right-5 flex items-center pointer-events-none text-gray-400 group-hover:text-[#0B5694] transition-colors">
            @if($type === 'datetime-local')
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            @endif
        </div>
    </div>

    <!-- Hidden Input for Form Submission -->
    <input type="hidden" name="{{ $name }}" x-model="value" :disabled="{{ $disabled ?? 'false' }}">

    <!-- Datepicker Dropdown -->
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         :class="position === 'top' ? 'bottom-full mb-1' : 'top-full mt-1'"
         class="absolute z-[110] w-80 bg-white dark:bg-gray-900 rounded-[22px] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col max-h-[380px]">
        
        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden scroll-smooth custom-scrollbar p-6 pr-4">
            <!-- Header / Selection -->
            <div class="flex justify-between items-center mb-6 gap-2">
                <button type="button" @click="prevMonth()" class="p-2 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl transition-colors text-gray-600 dark:text-gray-400 hover:text-[#0B5694]">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                
                <div class="flex-1 flex justify-center gap-1 min-w-0">
                    <select x-model="month" @change="getNoOfDays()" class="bg-transparent border-none p-0 text-sm font-bold text-gray-800 dark:text-gray-100 uppercase tracking-tighter focus:ring-0 cursor-pointer hover:text-[#0B5694] transition-colors appearance-none bg-none">
                        <template x-for="(monthName, index) in monthNames">
                            <option :value="index" x-text="monthName" :selected="index === month"></option>
                        </template>
                    </select>
                    <select x-model="year" @change="getNoOfDays()" class="bg-transparent border-none p-0 text-sm font-bold text-gray-800 dark:text-gray-100 uppercase tracking-tighter focus:ring-0 cursor-pointer hover:text-[#0B5694] transition-colors appearance-none bg-none">
                        <template x-for="y in Array.from({length: 21}, (_, i) => new Date().getFullYear() - 10 + i)">
                            <option :value="y" x-text="y" :selected="y === year"></option>
                        </template>
                    </select>
                </div>

                <button type="button" @click="nextMonth()" class="p-2 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl transition-colors text-gray-600 dark:text-gray-400 hover:text-[#0B5694]">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>

            <!-- Weekdays -->
            <div class="grid grid-cols-7 mb-4">
                <template x-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']">
                    <div class="text-center text-[10px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wider" x-text="day"></div>
                </template>
            </div>

            <!-- Days Grid -->
            <div class="grid grid-cols-7 gap-1">
                <template x-for="blank in blankDays">
                    <div class="p-2"></div>
                </template>
                <template x-for="date in noOfDays">
                    <button type="button" 
                            @click="selectDate(date)"
                            class="p-2 text-xs font-bold rounded-xl transition-all relative group"
                            :class="{
                                'bg-[#0B5694] text-white shadow-lg ring-2 ring-[#0B5694] ring-offset-2 dark:ring-offset-gray-900': isSelected(date),
                                'text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-[#0B5694]': !isSelected(date),
                                'text-[#0B5694] dark:text-blue-400 font-bold': isToday(date) && !isSelected(date)
                            }">
                        <span x-text="date"></span>
                        <template x-if="isToday(date) && !isSelected(date)">
                            <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-1 h-1 bg-[#0B5694] rounded-full"></div>
                        </template>
                    </button>
                </template>
            </div>

            <!-- Time Picker (Conditional) -->
            <template x-if="type === 'datetime-local'">
                <div class="mt-6 pt-6 border-t border-gray-50 dark:border-gray-800">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 p-2 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <input type="number" x-model="hours" 
                                   @wheel="handleTimeScroll($event, 'hours')"
                                   @click="$event.target.select()"
                                   min="1" max="12" 
                                   class="w-12 bg-transparent border-none text-center text-sm font-bold text-gray-700 dark:text-gray-200 p-0 focus:ring-0 appearance-none">
                            <span class="text-gray-400 font-bold animate-pulse">:</span>
                            <input type="number" x-model="minutes" 
                                   @wheel="handleTimeScroll($event, 'minutes')"
                                   @click="$event.target.select()"
                                   @blur="minutes = String(minutes).padStart(2, '0')"
                                   min="0" max="59"
                                   class="w-12 bg-transparent border-none text-center text-sm font-bold text-gray-700 dark:text-gray-200 p-0 focus:ring-0 appearance-none">
                        </div>
                        <div class="flex bg-gray-100 dark:bg-gray-800 p-1 rounded-xl border border-gray-200 dark:border-gray-700">
                            <button type="button" @click="ampm = 'AM'" :class="ampm === 'AM' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border border-gray-100 dark:border-gray-600' : 'text-gray-400'" class="px-4 py-2 rounded-lg text-[10px] font-bold uppercase transition-all">AM</button>
                            <button type="button" @click="ampm = 'PM'" :class="ampm === 'PM' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border border-gray-100 dark:border-gray-600' : 'text-gray-400'" class="px-4 py-2 rounded-lg text-[10px] font-bold uppercase transition-all">PM</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Sticky Actions Footer -->
        <div class="sticky bottom-0 bg-white dark:bg-gray-900 mt-auto px-6 py-4 border-t border-gray-100 dark:border-gray-800/50 flex justify-between items-center gap-4 z-10">
            <button type="button" @click="setToday()" class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-wider hover:opacity-70 transition-opacity">Today</button>
            <div class="flex items-center gap-6">
                <button type="button" @click="clearDate()" class="text-[10px] font-bold text-gray-400 dark:text-gray-600 uppercase tracking-wider hover:text-red-500 transition-colors">Clear</button>
                <button type="button" @click="show = false" class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-wider hover:opacity-70 transition-opacity font-bold">Apply</button>
            </div>
        </div>
    </div>
</div>

