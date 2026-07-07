@extends('layouts.app')

@section('title', 'Manage Assessment Form')

@section('content')
<x-page-layout 
    portalType="Head Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="Head Supervisor"
>
    <div x-data="assessmentManager" class="flex flex-col">
        <header class="mb-8 lg:mb-12 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('head-supervisor.onboarding.index', ['#offices']) }}" class="p-2 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl text-gray-400 hover:text-[#0B5694] transition-colors shadow-sm shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-800 dark:text-gray-100 tracking-normal">Assessment Items</h2>
                </div>
                <p class="text-xs sm:text-sm lg:text-base text-gray-500 dark:text-gray-400 font-medium tracking-tight ml-12">Manage competencies used for intern matching</p>
            </div>
            <button @click="addItemOpen = true" class="bg-[#0B5694] dark:bg-blue-600 text-white px-8 py-3.5 rounded-xl text-[10px] sm:text-xs font-bold uppercase tracking-wider shadow-lg hover:bg-[#084577] dark:hover:bg-blue-700 transition active:scale-95 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add Competency
            </button>
        </header>

        <div class="space-y-12">
            @foreach($items as $category => $categoryItems)
                <section>
                    <div class="flex items-center gap-4 mb-6">
                        <h3 class="text-xs font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-[0.2em] whitespace-nowrap">{{ $category }}</h3>
                        <div class="h-px w-full bg-gray-200 dark:bg-gray-800"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($categoryItems as $item)
                            <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 p-6 flex flex-col justify-between hover:shadow-md transition-all group">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Item # {{ $item->order_index + one }}</span>
                                        <div class="flex gap-1">
                                            <button @click="editItemOpen = true; editItem = {{ $item->toJson() }}; editItem.id = '{{ $item->getRouteKey() }}'" 
                                                    class="p-2 text-gray-300 dark:text-gray-600 hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            </button>
                                            <form action="{{ route('head-supervisor.assessment-items.destroy', $item) }}" method="POST" onsubmit="return confirm('Remove this competency permanently?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-gray-300 dark:text-gray-600 hover:text-rose-500 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-200 leading-relaxed">{{ $item->competency_text }}</p>
                                </div>
                                
                                <div class="mt-6 flex items-center justify-between border-t border-gray-50 dark:border-gray-800 pt-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}"></div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $item->is_active ? 'text-emerald-500' : 'text-gray-400' }}">
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        {{-- Add Item Modal --}}
        <div x-show="addItemOpen" x-cloak class="fixed inset-0 z-[150] flex items-center justify-center p-4 sm:p-6"
             @keydown.escape.window="addItemOpen = false">
            <div @click="addItemOpen = false" class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm"></div>
            <div class="relative bg-white dark:bg-gray-900 w-full max-w-xl mx-4 rounded-[25px] sm:rounded-[35px] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 text-left">
                <div class="p-6 sm:p-10">
                    <h3 class="text-xl sm:text-2xl font-extrabold text-gray-800 dark:text-gray-100 tracking-normal uppercase mb-2">Add Competency</h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium mb-6 sm:mb-8">Create a new item for the assessment form.</p>

                    <form action="{{ route('head-supervisor.assessment-items.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider ml-1">Category / Section</label>
                            <input type="text" name="category" required list="categories-list-hs" placeholder="Select or type a category..." class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all">
                            <datalist id="categories-list-hs">
                                @foreach(array_keys($items->toArray()) as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider ml-1">Competency Description</label>
                            <textarea name="competency_text" required rows="4" class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all placeholder:text-gray-400" placeholder="e.g., Using word processing software for document creation..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider ml-1">Order Index</label>
                                <input type="number" name="order_index" value="0" class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all">
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="addItemOpen = false" class="flex-1 px-8 py-4 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-200 dark:hover:bg-gray-700 transition">Cancel</button>
                            <button type="submit" class="flex-1 px-8 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-wider shadow-lg hover:bg-[#084577] dark:hover:bg-blue-700 transition">Add Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit Item Modal --}}
        <div x-show="editItemOpen" x-cloak class="fixed inset-0 z-[150] flex items-center justify-center p-4 sm:p-6"
             @keydown.escape.window="editItemOpen = false">
            <div @click="editItemOpen = false" class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm"></div>
            <div class="relative bg-white dark:bg-gray-900 w-full max-w-xl mx-4 rounded-[25px] sm:rounded-[35px] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 text-left">
                <div class="p-6 sm:p-10">
                    <h3 class="text-xl sm:text-2xl font-extrabold text-gray-800 dark:text-gray-100 tracking-normal uppercase mb-2">Edit Competency</h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium mb-6 sm:mb-8">Update the assessment item details.</p>

                    <form :action="'{{ route('head-supervisor.assessment-items.index') }}/' + editItem.id" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider ml-1">Category / Section</label>
                            <input type="text" name="category" x-model="editItem.category" required list="categories-list-hs" placeholder="Select or type a category..." class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider ml-1">Competency Description</label>
                            <textarea name="competency_text" x-model="editItem.competency_text" required rows="4" class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all placeholder:text-gray-400"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider ml-1">Order Index</label>
                                <input type="number" name="order_index" x-model="editItem.order_index" class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider ml-1">Status</label>
                                <select name="is_active" x-model="editItem.is_active" class="w-full bg-gray-50 dark:bg-gray-800 border-2 border-transparent focus:border-blue-500/20 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all">
                                    <option :value="1">Active</option>
                                    <option :value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="editItemOpen = false" class="flex-1 px-8 py-4 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-2xl font-bold text-xs uppercase tracking-wider hover:bg-gray-200 dark:hover:bg-gray-700 transition">Cancel</button>
                            <button type="submit" class="flex-1 px-8 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-wider shadow-lg hover:bg-[#084577] dark:hover:bg-blue-700 transition">Update Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('assessmentManager', () => ({
            addItemOpen: false,
            editItemOpen: false,
            editItem: {
                id: null,
                category: '',
                competency_text: '',
                order_index: 0,
                is_active: 1
            }
        }));
    });
</script>
@endsection
