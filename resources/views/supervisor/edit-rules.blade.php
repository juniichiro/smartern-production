@extends('layouts.app')

@section('title', 'Manage Rules - ' . $office->name)

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ $office->name }}"
>
    <div x-data="officeRulesManager(@js($assessmentItems), @js($rules))" class="flex flex-col">
        
        <header class="mb-6 md:mb-8 lg:mb-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4 sm:gap-6">
                <a href="{{ route('supervisor.interns') }}" class="w-10 h-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 hover:border-[#0B5694] dark:hover:border-blue-500 transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div>
                    <h2 class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-800 dark:text-gray-100 tracking-normal flex items-center gap-2">
                        Requirements & Rules: 
                        <span class="text-[#0B5694] dark:text-blue-400 font-extrabold uppercase">
                            {{ $office->name }}
                        </span>
                    </h2>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1 font-medium italic">Define competency thresholds and limits for your office</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-3 gap-6 md:gap-10 items-start">
            {{-- Main Form for Office Rules --}}
            <div class="lg:col-span-2">
                <form action="{{ route('supervisor.office.rules.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-12">
                        {{-- Office Capacity Configuration --}}
                        <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                            <div class="bg-gray-50 dark:bg-gray-950 px-10 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <h3 class="text-sm font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-[0.2em]">Office Capacity</h3>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">General Configuration</span>
                            </div>
                            <div class="p-6 md:p-10">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                                    <div class="md:col-span-8">
                                        <p class="text-sm font-bold text-gray-700 dark:text-gray-200 leading-relaxed">Intern Capacity Limit</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 font-medium italic">Define the maximum number of interns this office can accommodate (Default: 5)</p>
                                    </div>
                                    <div class="md:col-span-4">
                                        <div class="relative group">
                                            <input type="number" name="intern_limit" value="{{ $office->intern_limit }}" min="0" max="100" required
                                                   class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-4 md:px-5 py-3 md:py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all text-center">
                                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-300 uppercase tracking-wider pointer-events-none group-focus-within:text-blue-500/40">Slots</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <template x-for="(items, category) in groupedItems" :key="category">
                            <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                                <div class="bg-gray-50 dark:bg-gray-950 px-10 py-6 border-b border-gray-100 dark:border-gray-800">
                                    <h3 class="text-sm font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-[0.2em]" x-text="category"></h3>
                                </div>
                                
                                <div class="p-6 md:p-10 space-y-8">
                                    <template x-for="item in items" :key="item.id">
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center group">
                                            <div class="lg:col-span-8">
                                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200 leading-relaxed group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors" x-text="item.competency_text"></p>
                                            </div>
                                            <div class="lg:col-span-4 flex items-center gap-6">
                                                <div class="flex-1 space-y-2">
                                                    <div class="flex justify-between items-center mb-1">
                                                        <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Requirement Level</span>
                                                        <span class="text-xs font-bold text-[#0B5694] dark:text-blue-400" x-text="getPriorityLabel(item.id)"></span>
                                                    </div>
                                                    <div class="relative flex flex-col items-center">
                                                        <input type="hidden" :name="`rules[${item.id}][require_rule]`" :value="item.category.includes('.') ? item.category.split('.')[1].trim() : item.category">
                                                        <input type="hidden" :name="`rules[${item.id}][rule_value]`" :value="item.competency_text">
                                                        <input type="range" 
                                                               :name="`rules[${item.id}][priority]`" 
                                                               x-model="ruleScores[item.id]" 
                                                               min="1" max="5" 
                                                               class="w-full h-1.5 bg-gray-100 dark:bg-gray-800 rounded-lg appearance-none cursor-pointer accent-[#0B5694] dark:accent-blue-500 transition-all">
                                                        
                                                        <div class="w-full flex justify-between px-0.5 mt-2">
                                                            <template x-for="i in [1,2,3,4,5]">
                                                                <div class="flex flex-col items-center gap-1">
                                                                    <div class="w-0.5 h-1 rounded-full transition-colors duration-200"
                                                                         :class="ruleScores[item.id] >= i ? 'bg-[#0B5694] dark:bg-blue-500' : 'bg-gray-200 dark:bg-gray-700'"></div>
                                                                    <span class="text-[9px] font-bold transition-all duration-200" 
                                                                          :class="ruleScores[item.id] == i ? 'text-[#0B5694] dark:text-blue-400 scale-125' : 'text-gray-300 dark:text-gray-700'"
                                                                          x-text="i"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-10 sm:mt-12 flex flex-col sm:flex-row justify-end gap-3 sm:gap-4 bg-white dark:bg-gray-900 p-6 sm:p-8 rounded-[25px] sm:rounded-[30px] border border-gray-100 dark:border-gray-800 sticky bottom-2 sm:bottom-4 md:bottom-8 shadow-xl z-10">
                        <a href="{{ route('supervisor.interns') }}" class="px-6 sm:px-8 md:px-10 py-3 md:py-4 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl font-bold text-[10px] sm:text-xs uppercase tracking-wider hover:bg-gray-200 dark:hover:bg-gray-700 transition text-center">
                            Discard
                        </a>
                        <button type="submit" class="px-6 sm:px-8 md:px-10 py-3 md:py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-bold text-[10px] sm:text-xs uppercase tracking-wider hover:bg-[#084577] dark:hover:bg-blue-700 transition shadow-lg shadow-blue-500/20 active:scale-95">
                            Update Office Requirements
                        </button>
                    </div>
                </form>
            </div>

            {{-- Sidebar for Adding Competency --}}
            <div class="lg:col-span-1 md:order-first lg:order-none">
                <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden p-8 sticky top-6">
                    <h3 class="text-xs font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-[0.2em] mb-6">Add Competency Requirement</h3>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-6 font-medium leading-relaxed">
                        Add a new custom skill requirement to the assessment form. This will immediately make it selectable for all matching rules in your office.
                    </p>
                    
                    <form action="{{ route('supervisor.office.assessment-items.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="category" class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Category / Section</label>
                            <input type="text" name="category" id="category" list="categories-list" required placeholder="Select or type a category..." class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-4 md:px-5 py-3 md:py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all">
                            <datalist id="categories-list">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>
                        
                        <div>
                            <label for="competency_text" class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Requirement Description</label>
                            <textarea name="competency_text" id="competency_text" rows="3" required placeholder="e.g. Developing custom web templates and integrations" class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-4 md:px-5 py-3 md:py-4 text-sm font-medium text-gray-700 dark:text-gray-200 outline-none transition-all placeholder-gray-300 dark:placeholder-gray-600"></textarea>
                        </div>

                        <div>
                            <label for="priority" class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Initial Priority Level</label>
                            <select name="priority" id="priority" required class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-4 md:px-5 py-3 md:py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all">
                                <option value="1">Not Required (1)</option>
                                <option value="2">Slightly Important (2)</option>
                                <option value="3">Moderately Important (3)</option>
                                <option value="4" selected>Important (4)</option>
                                <option value="5">Highly Required (5)</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-[#0B5694] hover:bg-[#084577] text-white rounded-2xl font-bold py-3 md:py-4 text-[10px] uppercase tracking-widest transition shadow-lg shadow-blue-500/10">
                            Add & Save Competency
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('officeRulesManager', (assessmentItems, existingRules) => ({
            groupedItems: assessmentItems,
            ruleScores: {},
            
            init() {
                // Initialize all items with one (Not Required)
                Object.values(this.groupedItems).forEach(categoryItems => {
                    categoryItems.forEach(item => {
                        this.ruleScores[item.id] = 1;
                    });
                });

                // Overwrite with existing rules from DB
                existingRules.forEach(rule => {
                    // Find the item that matches the rule_value
                    let foundItem = null;
                    Object.values(this.groupedItems).forEach(categoryItems => {
                        const match = categoryItems.find(i => i.competency_text === rule.rule_value);
                        if (match) foundItem = match;
                    });

                    if (foundItem) {
                        this.ruleScores[foundItem.id] = rule.priority;
                    }
                });
            },

            getPriorityLabel(itemId) {
                const labels = [
                    'Not Required',
                    'Slightly Important',
                    'Moderately Important',
                    'Important',
                    'Highly Required'
                ];
                return labels[this.ruleScores[itemId] - 1];
            }
        }));
    });
</script>
@endsection
