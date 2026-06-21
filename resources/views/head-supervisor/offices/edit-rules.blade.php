@extends('layouts.app')

@section('title', 'Manage Rules - ' . $office->name)

@section('content')
<div class="flex min-h-screen bg-gray-50 dark:bg-gray-950 font-sans text-[14px] transition-colors duration-300">
    
    <x-sidebar portalType="Head Supervisor" 
               userName="{{ Auth::user()->supervisorDetails->first_name }}" 
               userRole="Head Supervisor"/>

    <main class="lg:ml-64 flex-1 p-4 sm:p-6 lg:p-10 text-left mt-16 lg:mt-0 transition-all duration-300" 
          x-data="officeRulesManager(@js($assessmentItems), @js($rules))">
        
        <header class="mb-8 lg:mb-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4 sm:gap-6">
                <a href="{{ route('head-supervisor.onboarding.index', ['#offices']) }}" class="w-10 h-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400 hover:border-[#0B5694] dark:hover:border-blue-500 transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div>
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-800 dark:text-gray-100 tracking-normal flex items-center gap-2">
                        Matching Rules: 
                        <span class="text-[#0B5694] dark:text-blue-400 font-extrabold uppercase">
                            {{ $office->name }}
                        </span>
                    </h2>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1 font-medium italic">Define competency thresholds for intern matching</p>
                </div>
            </div>
            <a href="{{ route('head-supervisor.assessment-items.index') }}" class="px-6 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-[10px] font-bold uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-2 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                Edit Form Items
            </a>
        </header>

        <form action="{{ route('head-supervisor.offices.rules.update', $office) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-12">
                {{-- Office Capacity Configuration --}}
                <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-950 px-10 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <h3 class="text-sm font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-[0.2em]">Office Capacity</h3>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">General Configuration</span>
                    </div>
                    <div class="p-10">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                            <div class="md:col-span-8">
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200 leading-relaxed">Intern Capacity Limit</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 font-medium italic">Define the maximum number of interns this office can accommodate (Default: 5)</p>
                            </div>
                            <div class="md:col-span-4">
                                <div class="relative group">
                                    <input type="number" name="intern_limit" value="{{ $office->intern_limit }}" min="1" max="100" required
                                           class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all text-center">
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
                        
                        <div class="p-10 space-y-8">
                            <template x-for="item in items" :key="item.id">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center group">
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
                                                <input type="hidden" :name="`rules[${item.id}][require_rule]`" :value="item.category.split('.')[1].trim()">
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

            <div class="mt-12 flex justify-end gap-4 bg-white dark:bg-gray-900 p-8 rounded-[30px] border border-gray-100 dark:border-gray-800 sticky bottom-8 shadow-xl z-10 animate-in slide-in-from-bottom-4 duration-500">
                <a href="{{ route('head-supervisor.onboarding.index', ['#offices']) }}" class="px-10 py-4 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                    Discard
                </a>
                <button type="submit" class="px-10 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-[#084577] dark:hover:bg-blue-700 transition shadow-lg shadow-blue-500/20 active:scale-95">
                    Update Office Requirements
                </button>
            </div>
        </form>
    </main>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('officeRulesManager', (assessmentItems, existingRules) => ({
            groupedItems: assessmentItems,
            ruleScores: {},
            
            init() {
                // Initialize all items with 1 (Not Required)
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
