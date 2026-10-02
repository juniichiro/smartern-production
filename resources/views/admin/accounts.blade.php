@extends('layouts.app')

@section('title', 'Account Management')

@section('content')
<x-page-layout 
    portalType="Admin" 
    userName="{{ Auth::user()->email }}" 
    userRole="Admin"
>
    @php
        $isUpdate = old('_method') === 'PUT' || old('_method') === 'PATCH';
        $initialData = null;
        if ($isUpdate && old('id')) {
            $initialData = [
                'id' => old('id'),
                'email' => old('email'),
                'role' => old('role'),
                'first_name' => old('first_name'),
                'middle_name' => old('middle_name'),
                'last_name' => old('last_name'),
                'office_id' => old('office_id'),
                'school' => old('school'),
                'gender' => old('gender'),
                'gender_other_specify' => old('gender_other_specify'),
                'emergency_contact' => old('emergency_contact'),
                'phone_number' => old('phone_number'),
                'is_hr_head' => old('is_hr_head') == '1',
                'initials' => strtoupper(substr(old('first_name', 'U'), 0, 1) . substr(old('last_name', 'N'), 0, 1))
            ];
        }
        $pendingCount = isset($pendingOnboarding) ? $pendingOnboarding->count() : 0;
    @endphp

    <div x-data="Object.assign(accountsManager(
        {{ $hasHrHead ? 'true' : 'false' }}, 
        {{ $errors->any() ? 'true' : 'false' }}, 
        '{{ old('role_id') }}',
        {{ $isUpdate ? 'true' : 'false' }},
        {{ $initialData ? json_encode($initialData) : 'null' }}
    ), { 
        isDeactivated: false,
        activeTab: (window.location.hash && ['directory', 'onboarding'].includes(window.location.hash.replace('#', ''))) ? window.location.hash.replace('#', '') : 'directory',
        setTab(tab) {
            this.activeTab = tab;
            window.location.hash = tab;
        }
    })" class="flex flex-col flex-1 max-w-7xl mx-auto w-full">

        {{-- PAGE HEADER --}}
        <header class="mb-6 lg:mb-8 flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    {{-- Breadcrumb --}}
                    <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-400 dark:text-gray-400 mb-1.5" aria-label="Breadcrumb">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-[#0B5694] dark:hover:text-blue-400 transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span>Dashboard</span>
                        </a>
                        <svg class="w-3 h-3 text-slate-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        <span class="text-slate-600 dark:text-gray-300 font-semibold">Account Management</span>
                    </nav>

                    {{-- Title & Subtitle --}}
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                        Account Management
                    </h1>
                    <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-gray-400 mt-0.5">
                        System access control & batch processing
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 self-start sm:self-center">
                    <button type="button" @click="showImportModal = true" class="h-10 sm:h-11 px-4 sm:px-5 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700/60 hover:border-slate-300 font-semibold text-xs sm:text-sm flex items-center gap-2 transition-all shadow-sm active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4 text-slate-500 dark:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Import Batch</span>
                    </button>

                    <button type="button" @click="showAddModal = true" class="h-10 sm:h-11 px-4 sm:px-5 rounded-xl bg-[#0B5694] hover:bg-[#084577] text-white font-semibold text-xs sm:text-sm flex items-center gap-2 transition-all shadow-sm shadow-[#0B5694]/25 active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Add User</span>
                    </button>
                </div>
            </div>

            {{-- Segmented Tab Switcher --}}
            <div class="flex items-center">
                <nav class="inline-flex p-1 bg-slate-100/90 dark:bg-gray-800/80 rounded-xl border border-slate-200/70 dark:border-gray-700/80" role="tablist" aria-label="Account sections">
                    <button type="button" 
                            @click="setTab('directory')" 
                            :class="activeTab === 'directory' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm font-bold' : 'text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-gray-200 font-medium'" 
                            class="px-4 py-2 rounded-lg text-xs tracking-wide transition-all whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20 cursor-pointer"
                            role="tab"
                            :aria-selected="activeTab === 'directory'">
                        User Directory
                    </button>
                    <button type="button" 
                            @click="setTab('onboarding')" 
                            :class="activeTab === 'onboarding' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm font-bold' : 'text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-gray-200 font-medium'" 
                            class="px-4 py-2 rounded-lg text-xs tracking-wide transition-all whitespace-nowrap flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20 cursor-pointer"
                            role="tab"
                            :aria-selected="activeTab === 'onboarding'">
                        <span>Intern Account Creation</span>
                        @if($pendingCount > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white leading-none">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </button>
                </nav>
            </div>
        </header>

        {{-- SKELETON LOADING VIEW --}}
        <div x-show="loading" x-transition:leave="transition ease-in duration-300 pointer-events-none" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            {{-- Quick Stats Skeleton --}}
            <div class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 sm:p-6 mb-6">
                <div class="h-4 w-32 bg-slate-200 dark:bg-gray-800 rounded animate-shimmer mb-4"></div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                    @for($i = 0; $i < 5; $i++)
                        <div class="bg-slate-50 dark:bg-gray-800/40 rounded-2xl p-4 border border-slate-100 dark:border-gray-800 space-y-3 min-h-[110px]">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-gray-700 animate-shimmer"></div>
                                <div class="h-3 w-16 bg-slate-200 dark:bg-gray-700 rounded animate-shimmer"></div>
                            </div>
                            <div class="h-7 w-12 bg-slate-200 dark:bg-gray-700 rounded animate-shimmer"></div>
                            <div class="h-3 w-20 bg-slate-200 dark:bg-gray-700 rounded animate-shimmer"></div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Table Panel Skeleton --}}
            <div class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 sm:p-6">
                <div class="flex justify-between items-center pb-5 border-b border-slate-100 dark:border-gray-800 mb-4">
                    <div class="h-5 w-28 bg-slate-200 dark:bg-gray-700 rounded animate-shimmer"></div>
                    <div class="h-9 w-64 bg-slate-200 dark:bg-gray-700 rounded-xl animate-shimmer"></div>
                </div>
                <div class="space-y-3">
                    @for($i = 0; $i < 5; $i++)
                        <div class="h-16 bg-slate-50 dark:bg-gray-800/40 rounded-xl animate-shimmer border border-slate-100 dark:border-gray-800"></div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex flex-col flex-1">
            
            {{-- QUICK STATS PANEL --}}
            <section aria-label="Quick Stats" class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 sm:p-6 mb-6 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-800 dark:text-gray-100 tracking-tight">Quick Stats</h2>
                        <p class="text-xs text-slate-400 dark:text-gray-500">Summary across provisioned directory accounts</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                    {{-- Tile 1: Total Users --}}
                    <div class="bg-slate-50/70 dark:bg-gray-800/40 rounded-2xl p-4 border border-slate-100 dark:border-gray-800 flex flex-col justify-between min-h-[110px] sm:min-h-[120px] transition-all hover:border-slate-200 dark:hover:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-[#0B5694] dark:text-blue-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wider text-[11px]">Total Users</span>
                        </div>
                        <div class="my-1">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $quickStats->total ?? 0 }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0B5694]"></span>
                            <span>Provisioned accounts</span>
                        </div>
                    </div>

                    {{-- Tile 2: Admins --}}
                    <div class="bg-slate-50/70 dark:bg-gray-800/40 rounded-2xl p-4 border border-slate-100 dark:border-gray-800 flex flex-col justify-between min-h-[110px] sm:min-h-[120px] transition-all hover:border-slate-200 dark:hover:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-gray-700/60 text-slate-700 dark:text-gray-300 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wider text-[11px]">Admins</span>
                        </div>
                        <div class="my-1">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $quickStats->admins ?? 0 }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                            <span>System access</span>
                        </div>
                    </div>

                    {{-- Tile 3: Supervisors / Head --}}
                    <div class="bg-slate-50/70 dark:bg-gray-800/40 rounded-2xl p-4 border border-slate-100 dark:border-gray-800 flex flex-col justify-between min-h-[110px] sm:min-h-[120px] transition-all hover:border-slate-200 dark:hover:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wider text-[11px]">Supervisors</span>
                        </div>
                        <div class="my-1">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $quickStats->supervisors ?? 0 }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                            <span>Lead & unit staff</span>
                        </div>
                    </div>

                    {{-- Tile 4: Interns --}}
                    <div class="bg-slate-50/70 dark:bg-gray-800/40 rounded-2xl p-4 border border-slate-100 dark:border-gray-800 flex flex-col justify-between min-h-[110px] sm:min-h-[120px] transition-all hover:border-slate-200 dark:hover:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wider text-[11px]">Interns</span>
                        </div>
                        <div class="my-1">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $quickStats->interns ?? 0 }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            <span>Active trainees</span>
                        </div>
                    </div>

                    {{-- Tile 5: Locked / Inactive --}}
                    <div class="col-span-2 sm:col-span-2 md:col-span-1 bg-slate-50/70 dark:bg-gray-800/40 rounded-2xl p-4 border border-slate-100 dark:border-gray-800 flex flex-col justify-between min-h-[110px] sm:min-h-[120px] transition-all hover:border-slate-200 dark:hover:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg {{ ($quickStats->locked ?? 0) > 0 ? 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400' : 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' }} flex items-center justify-center shrink-0">
                                @if(($quickStats->locked ?? 0) > 0)
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                @else
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                @endif
                            </div>
                            <span class="text-xs font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wider text-[11px]">Deactivated</span>
                        </div>
                        <div class="my-1">
                            <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $quickStats->locked ?? 0 }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px]">
                            @if(($quickStats->locked ?? 0) > 0)
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                <span class="text-rose-600 dark:text-rose-400 font-medium">Access locked</span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium">All in good standing</span>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            {{-- ALL USERS PANEL (Directory Tab) --}}
            <section x-show="activeTab === 'directory'" class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 sm:p-6 shadow-[0_1px_3px_rgba(0,0,0,0.02)] flex flex-col flex-1">
                {{-- Panel Header --}}
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white tracking-tight">All Users</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border border-slate-200/70 dark:border-gray-700">
                            {{ $users->total() }} {{ Str::plural('account', $users->total()) }}
                        </span>
                    </div>

                    {{-- Search Bar --}}
                    <form action="{{ route('admin.account') }}" method="GET" class="relative w-full md:w-80">
                        @if(request('role'))
                            <input type="hidden" name="role" value="{{ request('role') }}">
                        @endif
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-gray-500">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search across all pages..." 
                               @input.debounce.600ms="$el.form.submit()" 
                               class="w-full bg-slate-50 dark:bg-gray-800/80 border border-slate-200 dark:border-gray-700/80 rounded-xl pl-9 pr-8 py-2 text-xs sm:text-sm text-slate-800 dark:text-gray-200 placeholder-slate-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] transition-all font-medium">
                        @if(request('search'))
                            <a href="{{ route('admin.account', array_filter(['role' => request('role')])) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs font-bold text-slate-400 hover:text-rose-500 transition-colors" title="Clear search">
                                ✕
                            </a>
                        @endif
                    </form>
                </div>

                {{-- Filter Pills Bar --}}
                <div class="py-4 flex items-center justify-between gap-3 overflow-x-auto no-scrollbar border-b border-slate-100 dark:border-gray-800/60">
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span class="text-xs font-semibold text-slate-400 dark:text-gray-500 uppercase tracking-wider mr-1 text-[11px]">Filter:</span>
                        @php
                            $roleOptions = [
                                '' => 'All Roles',
                                'admin' => 'Admin',
                                'head_supervisor' => 'Head Supervisor',
                                'supervisor' => 'Supervisor',
                                'intern' => 'Intern',
                                'school_coordinator' => 'School Coordinator',
                            ];
                        @endphp
                        @foreach($roleOptions as $key => $label)
                            @php
                                $isActive = (request('role') === $key || (empty(request('role')) && $key === ''));
                                $url = route('admin.account', array_filter(['role' => $key, 'search' => request('search')]));
                            @endphp
                            <a href="{{ $url }}" 
                               class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all whitespace-nowrap {{ $isActive ? 'bg-[#0B5694] text-white shadow-sm' : 'bg-slate-100/80 dark:bg-gray-800 text-slate-600 dark:text-gray-400 hover:bg-slate-200/70 dark:hover:bg-gray-700 hover:text-slate-800 dark:hover:text-gray-200' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Desktop Table View --}}
                <div class="hidden md:block overflow-x-auto mt-2">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-gray-800/60 text-slate-400 dark:text-gray-500 text-[10px] font-bold uppercase tracking-wider">
                                <th class="px-5 py-3 rounded-l-xl">User</th>
                                <th class="px-5 py-3">System Role</th>
                                <th class="px-5 py-3">Department / School</th>
                                <th class="px-5 py-3 text-right rounded-r-xl">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800/80">
                            @forelse($users as $user)
                            @php
                                $details = $user->internDetails ?? $user->supervisorDetails ?? $user->schoolCoordinatorDetails;
                                $uInitials = strtoupper(substr($details->first_name ?? 'U', 0, 1) . substr($details->last_name ?? 'N', 0, 1));
                                $middleInitial = ($details?->middle_name) ? substr($details->middle_name, 0, 1) . '. ' : '';
                                $fullName = ($user->role->name === 'admin') 
                                    ? 'SmarTern Admin' 
                                    : (($details->first_name ?? 'User') . ' ' . $middleInitial . ($details->last_name ?? 'Account'));
                                $isHrHead = optional($user->supervisorDetails)->is_hr_head || $user->role_id->value === \App\Enums\UserRole::HEAD_SUPERVISOR->value;
                                $userDeactivated = $user->locked_until && now()->lessThan($user->locked_until);
                                $dept = $user->internDetails->office->name ?? $user->supervisorDetails->office->name ?? $user->schoolCoordinatorDetails->school ?? 'N/A';

                                $roleBadgeClass = match($user->role_id->value) {
                                    \App\Enums\UserRole::INTERN->value => 'bg-blue-50 text-blue-700 border-blue-200/60 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800/50',
                                    \App\Enums\UserRole::SUPERVISOR->value => 'bg-purple-50 text-purple-700 border-purple-200/60 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800/50',
                                    \App\Enums\UserRole::HEAD_SUPERVISOR->value => 'bg-violet-50 text-violet-700 border-violet-200/60 dark:bg-violet-900/30 dark:text-violet-300 dark:border-violet-800/50',
                                    \App\Enums\UserRole::ADMIN->value => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                    \App\Enums\UserRole::SCHOOL_COORDINATOR->value => 'bg-teal-50 text-teal-700 border-teal-200/60 dark:bg-teal-900/30 dark:text-teal-300 dark:border-teal-800/50',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                };
                            @endphp
                            <tr x-show="!deletingIds.includes('{{ $user->getRouteKey() }}')" 
                                x-transition:leave="transition ease-in duration-200" 
                                x-transition:leave-start="opacity-100 scale-100" 
                                x-transition:leave-end="opacity-0 scale-95" 
                                class="hover:bg-slate-50/80 dark:hover:bg-gray-800/50 transition-colors h-[68px]">
                                {{-- User Column --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-avatar :user="$user" size="9" border="1" circular="true" textSize="xs" />
                                        <div class="min-w-0">
                                            <div class="text-slate-800 dark:text-gray-100 font-bold text-sm leading-snug truncate">
                                                {{ $fullName }}
                                            </div>
                                            <div class="text-slate-400 dark:text-gray-500 text-xs truncate">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Role Column --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $roleBadgeClass }}">
                                            {{ $isHrHead ? 'Head Supervisor' : ucwords(str_replace('_', ' ', $user->role->name)) }}
                                        </span>
                                        @if($userDeactivated)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-600 border border-rose-200/60 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800">
                                                Deactivated
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Department Column --}}
                                <td class="px-5 py-3 text-left">
                                    <span class="text-xs font-medium text-slate-600 dark:text-gray-300 block max-w-[220px] truncate" title="{{ $dept }}">
                                        {{ $dept }}
                                    </span>
                                </td>

                                {{-- Actions Column --}}
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                @click="editData = { 
                                                    id: '{{ $user->getRouteKey() }}', 
                                                    email: '{{ $user->email }}',
                                                    role: '{{ $user->role->name }}',
                                                    role_id: '{{ $user->role_id->value === \App\Enums\UserRole::HEAD_SUPERVISOR->value ? \App\Enums\UserRole::SUPERVISOR->value : $user->role_id->value }}',
                                                    first_name: '{{ $details->first_name ?? '' }}',
                                                    middle_name: '{{ $details->middle_name ?? '' }}',
                                                    last_name: '{{ $details->last_name ?? '' }}',
                                                    office_id: '{{ $details->office_id ?? '' }}',
                                                    school: '{{ $details->school ?? '' }}',
                                                    gender: '{{ $user->internDetails->gender ?? '' }}',
                                                    gender_other_specify: '{{ $user->internDetails->gender_other_specify ?? '' }}',
                                                    phone_number: '{{ $details->phone_number ?? '' }}',
                                                    emergency_contact: '{{ $details->emergency_contact ?? '' }}',
                                                    initials: '{{ $uInitials }}',
                                                    is_hr_head: {{ $isHrHead ? 'true' : 'false' }},
                                                    profile_picture: '{{ $details?->profile_picture ? (str_starts_with($details->profile_picture, 'profiles/') || str_starts_with($details->profile_picture, 'profile-pictures/') ? Storage::disk('public')->url($details->profile_picture) : asset($details->profile_picture)) : '' }}'
                                                }; showEditModal = true" 
                                                class="p-2 text-slate-400 hover:text-[#0B5694] dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-gray-800 rounded-lg transition-colors cursor-pointer" 
                                                title="Edit user"
                                                aria-label="Edit user {{ $fullName }}">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                        </button>

                                        @if($userDeactivated)
                                            <button type="button" 
                                                    @click="confirmDelete('{{ $user->getRouteKey() }}'); isDeactivated = true" 
                                                    class="p-2 text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 rounded-lg transition-colors cursor-pointer" 
                                                    title="Reactivate account"
                                                    aria-label="Reactivate account {{ $fullName }}">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    @click="confirmDelete('{{ $user->getRouteKey() }}'); isDeactivated = false" 
                                                    class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition-colors cursor-pointer" 
                                                    title="Deactivate account"
                                                    aria-label="Deactivate account {{ $fullName }}">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <x-empty-state title="No users found." compact="true" />
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards View --}}
                <div class="md:hidden space-y-3 mt-4">
                    @forelse($users as $user)
                    @php
                        $details = $user->internDetails ?? $user->supervisorDetails ?? $user->schoolCoordinatorDetails;
                        $uInitials = strtoupper(substr($details->first_name ?? 'U', 0, 1) . substr($details->last_name ?? 'N', 0, 1));
                        $middleInitial = ($details?->middle_name) ? substr($details->middle_name, 0, 1) . '. ' : '';
                        $fullName = ($user->role->name === 'admin') 
                            ? 'SmarTern Admin' 
                            : (($details->first_name ?? 'User') . ' ' . $middleInitial . ($details->last_name ?? 'Account'));
                        $isHrHead = optional($user->supervisorDetails)->is_hr_head || $user->role_id->value === \App\Enums\UserRole::HEAD_SUPERVISOR->value;
                        $userDeactivated = $user->locked_until && now()->lessThan($user->locked_until);
                        $dept = $user->internDetails->office->name ?? $user->supervisorDetails->office->name ?? $user->schoolCoordinatorDetails->school ?? 'N/A';

                        $roleBadgeClass = match($user->role_id->value) {
                            \App\Enums\UserRole::INTERN->value => 'bg-blue-50 text-blue-700 border-blue-200/60 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800/50',
                            \App\Enums\UserRole::SUPERVISOR->value => 'bg-purple-50 text-purple-700 border-purple-200/60 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800/50',
                            \App\Enums\UserRole::HEAD_SUPERVISOR->value => 'bg-violet-50 text-violet-700 border-violet-200/60 dark:bg-violet-900/30 dark:text-violet-300 dark:border-violet-800/50',
                            \App\Enums\UserRole::ADMIN->value => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                            \App\Enums\UserRole::SCHOOL_COORDINATOR->value => 'bg-teal-50 text-teal-700 border-teal-200/60 dark:bg-teal-900/30 dark:text-teal-300 dark:border-teal-800/50',
                            default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                        };
                    @endphp
                    <div x-show="!deletingIds.includes('{{ $user->getRouteKey() }}')" 
                         x-transition:leave="transition ease-in duration-200" 
                         class="bg-slate-50/70 dark:bg-gray-800/50 p-4 rounded-2xl border border-slate-100 dark:border-gray-800 flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <x-avatar :user="$user" size="10" border="1" circular="true" textSize="xs" />
                                <div class="min-w-0">
                                    <div class="text-slate-800 dark:text-gray-100 font-bold text-sm leading-snug truncate">
                                        {{ $fullName }}
                                    </div>
                                    <div class="text-slate-400 dark:text-gray-500 text-xs truncate">
                                        {{ $user->email }}
                                    </div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border shrink-0 {{ $roleBadgeClass }}">
                                {{ $isHrHead ? 'Head Supervisor' : ucwords(str_replace('_', ' ', $user->role->name)) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-gray-400 pt-1">
                            <span class="font-medium text-slate-400 dark:text-gray-500">Unit / School:</span>
                            <span class="font-semibold text-slate-700 dark:text-gray-300 truncate max-w-[200px] text-right">{{ $dept }}</span>
                        </div>

                        @if($userDeactivated)
                            <div class="flex items-center gap-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                <span>Account is deactivated</span>
                            </div>
                        @endif

                        <div class="flex items-center gap-2 pt-2 border-t border-slate-200/60 dark:border-gray-700/60">
                            <button type="button" 
                                    @click="editData = { 
                                        id: '{{ $user->getRouteKey() }}', 
                                        email: '{{ $user->email }}',
                                        role: '{{ $user->role->name }}',
                                        role_id: '{{ $user->role_id->value === \App\Enums\UserRole::HEAD_SUPERVISOR->value ? \App\Enums\UserRole::SUPERVISOR->value : $user->role_id->value }}',
                                        first_name: '{{ $details->first_name ?? '' }}',
                                        middle_name: '{{ $details->middle_name ?? '' }}',
                                        last_name: '{{ $details->last_name ?? '' }}',
                                        office_id: '{{ $details->office_id ?? '' }}',
                                        school: '{{ $details->school ?? '' }}',
                                        gender: '{{ $user->internDetails->gender ?? '' }}',
                                        gender_other_specify: '{{ $user->internDetails->gender_other_specify ?? '' }}',
                                        phone_number: '{{ $details->phone_number ?? '' }}',
                                        emergency_contact: '{{ $details->emergency_contact ?? '' }}',
                                        initials: '{{ $uInitials }}',
                                        is_hr_head: {{ $isHrHead ? 'true' : 'false' }},
                                        profile_picture: '{{ $details?->profile_picture ? (str_starts_with($details->profile_picture, 'profiles/') || str_starts_with($details->profile_picture, 'profile-pictures/') ? Storage::disk('public')->url($details->profile_picture) : asset($details->profile_picture)) : '' }}'
                                    }; showEditModal = true" 
                                    class="flex-1 min-h-[44px] bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 hover:bg-slate-50 active:scale-95 transition shadow-sm cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                <span>Edit</span>
                            </button>

                            @if($userDeactivated)
                                <button type="button" 
                                        @click="confirmDelete('{{ $user->getRouteKey() }}'); isDeactivated = true" 
                                        class="flex-1 min-h-[44px] bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 active:scale-95 transition shadow-sm cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
                                    <span>Reactivate</span>
                                </button>
                            @else
                                <button type="button" 
                                        @click="confirmDelete('{{ $user->getRouteKey() }}'); isDeactivated = false" 
                                        class="flex-1 min-h-[44px] bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300 hover:text-rose-600 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 active:scale-95 transition shadow-sm cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    <span>Deactivate</span>
                                </button>
                            @endif
                        </div>
                    </div>
                    @empty
                        <div class="py-12 text-center">
                            <x-empty-state title="No users found." compact="true" />
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 dark:text-gray-400 font-medium">
                        Showing <span class="font-bold text-slate-700 dark:text-gray-200">{{ $users->firstItem() ?? 0 }}</span> to <span class="font-bold text-slate-700 dark:text-gray-200">{{ $users->lastItem() ?? 0 }}</span> of <span class="font-bold text-slate-700 dark:text-gray-200">{{ $users->total() }}</span> accounts
                    </div>

                    <div class="flex items-center gap-1.5">
                        @if ($users->onFirstPage())
                            <span class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-gray-800 text-slate-300 dark:text-gray-600 flex items-center justify-center cursor-not-allowed">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                            </span>
                        @else
                            <a href="{{ $users->previousPageUrl() }}" class="w-9 h-9 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700 flex items-center justify-center transition shadow-sm" title="Previous page">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                            </a>
                        @endif

                        <div class="flex items-center gap-1">
                            @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                                <a href="{{ $url }}" class="w-9 h-9 flex items-center justify-center rounded-lg text-xs font-semibold transition-all {{ $page == $users->currentPage() ? 'bg-[#0B5694] text-white shadow-sm' : 'bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700' }}">{{ $page }}</a>
                            @endforeach
                        </div>

                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}" class="w-9 h-9 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700 flex items-center justify-center transition shadow-sm" title="Next page">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        @else
                            <span class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-gray-800 text-slate-300 dark:text-gray-600 flex items-center justify-center cursor-not-allowed">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            </span>
                        @endif
                    </div>
                </div>
            </section>

            {{-- INTERN ACCOUNT CREATION PANEL (Onboarding Tab) --}}
            <section x-show="activeTab === 'onboarding'" x-cloak class="bg-white dark:bg-gray-900 rounded-[22px] border border-slate-200/80 dark:border-gray-800 p-5 sm:p-6 shadow-[0_1px_3px_rgba(0,0,0,0.02)] flex flex-col flex-1 animate-in fade-in duration-200">
                <div class="flex items-center justify-between pb-5 border-b border-slate-100 dark:border-gray-800">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white tracking-tight">Intern Account Creation</h2>
                            @if($pendingCount > 0)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                    {{ $pendingCount }} Pending
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 dark:text-gray-500 mt-0.5">Matched applicants ready for account generation</p>
                    </div>
                </div>

                @if(isset($pendingOnboarding) && $pendingOnboarding->isNotEmpty())
                    {{-- Desktop Table --}}
                    <div class="hidden md:block overflow-x-auto mt-4">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/80 dark:bg-gray-800/60 text-slate-400 dark:text-gray-500 text-[10px] font-bold uppercase tracking-wider">
                                    <th class="px-5 py-3 rounded-l-xl">Applicant</th>
                                    <th class="px-5 py-3">Email Address</th>
                                    <th class="px-5 py-3">Institution</th>
                                    <th class="px-5 py-3">Assigned Office</th>
                                    <th class="px-5 py-3 text-right rounded-r-xl">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-gray-800/80">
                                @foreach($pendingOnboarding as $applicant)
                                @php
                                    $middleInitial = ($applicant->middle_name) ? substr($applicant->middle_name, 0, 1) . '. ' : '';
                                    $fullName = $applicant->first_name . ' ' . $middleInitial . $applicant->last_name;
                                    $appInitials = strtoupper(substr($applicant->first_name, 0, 1) . substr($applicant->last_name, 0, 1));
                                @endphp
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-gray-800/50 transition-colors h-[68px]">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-blue-50 dark:bg-blue-900/30 text-[#0B5694] dark:text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-100 dark:border-blue-800/50">
                                                {{ $appInitials }}
                                            </div>
                                            <span class="text-sm font-bold text-slate-800 dark:text-gray-100">{{ $fullName }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-500 dark:text-gray-400">{{ $applicant->email }}</td>
                                    <td class="px-5 py-3 text-xs text-slate-600 dark:text-gray-300 font-medium">{{ $applicant->university }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-gray-800 text-slate-700 dark:text-gray-300 border border-slate-200 dark:border-gray-700">
                                            {{ $applicant->office_name }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <form action="{{ route('admin.accounts.onboarding-approve', $applicant->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="h-9 px-3.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-xs transition shadow-sm active:scale-95 inline-flex items-center gap-1.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                <span>Create Account</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="md:hidden space-y-3 mt-4">
                        @foreach($pendingOnboarding as $applicant)
                        @php
                            $middleInitial = ($applicant->middle_name) ? substr($applicant->middle_name, 0, 1) . '. ' : '';
                            $fullName = $applicant->first_name . ' ' . $middleInitial . $applicant->last_name;
                            $appInitials = strtoupper(substr($applicant->first_name, 0, 1) . substr($applicant->last_name, 0, 1));
                        @endphp
                        <div class="bg-slate-50/70 dark:bg-gray-800/50 p-4 rounded-2xl border border-slate-100 dark:border-gray-800 flex flex-col gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 text-[#0B5694] dark:text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-100 dark:border-blue-800/50">
                                    {{ $appInitials }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-slate-800 dark:text-gray-100 font-bold text-sm truncate">{{ $fullName }}</div>
                                    <div class="text-slate-400 dark:text-gray-500 text-xs truncate">{{ $applicant->email }}</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-gray-400 pt-1">
                                <span>Institution:</span>
                                <span class="font-semibold text-slate-700 dark:text-gray-300">{{ $applicant->university }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-gray-400">
                                <span>Assigned Office:</span>
                                <span class="font-semibold text-slate-700 dark:text-gray-300">{{ $applicant->office_name }}</span>
                            </div>

                            <form action="{{ route('admin.accounts.onboarding-approve', $applicant->id) }}" method="POST" class="pt-2 border-t border-slate-200/60 dark:border-gray-700/60">
                                @csrf
                                <button type="submit" class="w-full min-h-[44px] bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition active:scale-95 shadow-sm cursor-pointer">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span>Create Account</span>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center">
                        <x-empty-state title="No pending account creations." compact="true" />
                    </div>
                @endif
            </section>
        </div>

        {{-- MODALS --}}
        <div x-show="showAddModal || showImportModal || showEditModal || showDeleteModal" 
             class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6" 
             x-cloak>
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showAddModal = false; showImportModal = false; showEditModal = false; showDeleteModal = false"></div>
            
            {{-- Add User Modal --}}
            <div x-show="showAddModal" class="relative bg-white dark:bg-gray-900 rounded-[28px] max-w-4xl w-full p-6 sm:p-8 lg:p-10 shadow-2xl overflow-y-auto max-h-[90vh] border border-slate-100 dark:border-gray-800 custom-scrollbar z-10">
                <header class="mb-6 sm:mb-8">
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-gray-100 tracking-tight">Create New Account</h3>
                    <p class="text-slate-400 dark:text-gray-500 text-xs sm:text-sm mt-1 font-medium">Fill in user details to provision system credentials</p>
                </header>

                @if ($errors->any())
                    <div class="mb-6 p-5 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 rounded-2xl">
                        <div class="flex items-center gap-2 mb-3 text-rose-600 dark:text-rose-400">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span class="text-xs font-bold uppercase tracking-wider">Registration Failed</span>
                        </div>
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-xs text-rose-600 dark:text-rose-400 font-medium flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-rose-400"></span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="{{ route('admin.account.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" required 
                                class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium placeholder-slate-300 dark:placeholder-gray-600 text-slate-700 dark:text-gray-200">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">System Role</label>
                            <select name="role_id" x-model="selectedRole" required
                                class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-semibold text-slate-700 dark:text-gray-200 appearance-none cursor-pointer">
                                <option value="">Select a role...</option>
                                @foreach($roles->whereNotIn('name', ['admin', 'head_supervisor']) as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $role->name)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                                class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200"
                                placeholder="(Optional)">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Phone Number</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" required placeholder="09XXXXXXXXX"
                            class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200">
                    </div>

                    {{-- Dynamic Role Fields --}}
                    <div class="border-t border-slate-100 dark:border-gray-800 pt-6">
                        <template x-if="selectedRole == '3'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Assigned Office</label>
                                    <select name="office_id" required
                                        class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-semibold text-slate-700 dark:text-gray-200 appearance-none cursor-pointer">
                                        <option value="">Select office...</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-gray-800/60 rounded-xl border border-slate-200 dark:border-gray-700">
                                    <div class="relative inline-block w-12 h-6 transition duration-200 ease-in-out bg-slate-200 dark:bg-gray-700 rounded-full" :class="hasHrHead ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                                        <input type="hidden" name="is_hr_head" value="0">
                                        <input type="checkbox" name="is_hr_head" value="1" id="is_hr_head" {{ old('is_hr_head') ? 'checked' : '' }} class="absolute w-6 h-6 transition duration-200 ease-in-out bg-white dark:bg-gray-200 border-2 border-slate-200 dark:border-gray-600 rounded-full appearance-none checked:right-0 checked:border-[#0B5694] right-6 cursor-pointer" :class="hasHrHead ? 'cursor-not-allowed' : 'cursor-pointer'" :disabled="hasHrHead">
                                    </div>
                                    <div>
                                        <label for="is_hr_head" class="text-sm font-semibold text-slate-700 dark:text-gray-200 block">Designate as HR Head</label>
                                        <span class="text-[11px] text-slate-400 dark:text-gray-500 font-medium" x-show="hasHrHead">Only one HR Head is allowed.</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedRole == '5'">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">School / University</label>
                                    <input type="text" name="school" value="{{ old('school') }}" required
                                        class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Assigned Office</label>
                                    <select name="office_id" required
                                        class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-semibold text-slate-700 dark:text-gray-200 appearance-none cursor-pointer">
                                        <option value="">Select office...</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Gender</label>
                                    <select name="gender" required
                                        class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-semibold text-slate-700 dark:text-gray-200 appearance-none cursor-pointer">
                                        <option value="">Select gender...</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedRole == '4'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Affiliated School</label>
                                    <input type="text" name="school" value="{{ old('school') }}" required
                                        class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-100 dark:border-gray-800">
                        <button type="submit" class="flex-1 bg-[#0B5694] text-white py-3 rounded-xl font-bold text-sm shadow-sm hover:bg-[#084577] transition active:scale-[0.98] cursor-pointer">
                            Create User Account
                        </button>
                        <button type="button" @click="showAddModal = false" class="sm:w-28 bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400 py-3 rounded-xl font-semibold text-sm hover:bg-slate-200 dark:hover:bg-gray-700 transition cursor-pointer">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            {{-- Import Batch Modal --}}
            <div x-show="showImportModal" class="relative bg-white dark:bg-gray-900 rounded-[28px] max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 dark:border-gray-800 z-10">
                <header class="mb-5 text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-gray-100 tracking-tight">Import Accounts</h3>
                    <p class="text-slate-400 dark:text-gray-500 text-xs sm:text-sm mt-1 font-medium">Upload a CSV or XLSX file containing bulk user records</p>
                    
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('admin.accounts.template') }}" 
                           class="text-xs text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-semibold underline flex items-center gap-1 transition-all">
                            <svg class="w-3.5 h-3.5 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Starter Excel Template
                        </a>

                        <a href="data:text/plain;charset=utf-8,SmarTern%20-%20Batch%20User%20Account%20CSV%20Import%20Guide%0A%0AYour%20CSV%20file%20must%20follow%20the%20exact%2013%20columns%20sequence%20(indices%200%20to%2012)%20outlined%20below.%20You%20can%20include%20a%20header%20row.%0A%0ACOLUMNS%20REFERENCE%3A%0A1.%20first_name%20%3A%20%5BRequired%5D%20String.%0A2.%20middle_name%20%3A%20%5BOptional%5D%20String.%0A3.%20last_name%20%3A%20%5BRequired%5D%20String.%0A4.%20email%20%3A%20%5BRequired%5D%20Unique%20email.%0A5.%20school%20%3A%20%5BRequired%20for%20Interns/Coordinators%5D%20Name%20of%20the%20school.%20Use%20%22N/A%22%20for%20Supervisors.%0A6.%20office_name_or_id%20%3A%20%5BRequired%20for%20Interns/Supervisors%5D%20Name%20of%20target%20Office%20or%20database%20ID.%20Leave%20blank%20for%20Coordinators.%0A7.%20role_name_or_id%20%3A%20%5BRequired%5D%20%22intern%22%20(5)%20or%20%22supervisor%22%20(3)%20or%20%22coordinator%22%20(4).%20Admin%20and%20Head%20Supervisor%20roles%20are%20blocked.%0A8.%20gender%20%3A%20%5BOptional%20for%20Interns%5D%20%22male%22%2C%20%22female%22%2C%20or%20%22other%22.%20Leave%20blank%2520for%20staff.%0A9.%20gender_other%20%3A%20%5BRequired%20if%20gender%20is%20%22other%22%5D%20Specific%20gender%20text.%20Leave%2520blank%20otherwise.%0A10.%20phone_number%20%3A%20%5BOptional%5D%20e.g.%2C%2009123456789.%0A11.%20profile_picture%20%3A%20%5BOptional%5D%20Leave%20blank.%0A12.%20emergency_contact%20%3A%20%5BOptional%20for%20Interns%5D%20Emergency%20contact%20person%20name.%20Leave%20blank%2520for%20staff.%0A13.%20emergency_contact_number%20%3A%20%5BOptional%20for%20Interns%5D%20Contact%20number.%20Leave%2520blank%20for%2520staff.%0A%0ASTAFF%20VS%20INTERN%20EXPECTATIONS%3A%0A-%20Interns%20%3A%20Fill%20out%20all%20columns.%0A-%20Supervisors%20%3A%20Leave%20%22school%22%2C%20%22gender%22%2C%20%22gender_other%22%2C%20and%20both%20%22emergency_contact%22%20columns%20blank.%0A-%20School%20Coordinators%20%3A%20Leave%20%22office_name_or_id%22%2C%20%22gender%22%2C%20%22gender_other%22%2C%20and%20both%20%22emergency_contact%22%20columns%20blank.%0A%0ASAMPLE%20CSV%20LINE%20(INTERN)%3A%0AJane%2CMarie%2CSmith%2Cjane%40example.com%2CState%20University%2CMain%20Office%2Cintern%2Cfemale%2C%2C09123456789%2C%2CJane%20Smith%20(Mother)%2C09112223333%0A%0ASAMPLE%20CSV%20LINE%20(SUPERVISOR)%3A%0ARobert%2C%2CJohnson%2Crobert%40example.com%2CN/A%2CMain%20Office%2Csupervisor%2C%2C%2C%2C%2C" 
                           download="smartern_batch_import_guide.txt" 
                           class="text-xs text-[#0B5694] hover:text-[#084577] dark:text-blue-400 font-semibold underline flex items-center gap-1 transition-all">
                            <svg class="w-3.5 h-3.5 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            Import Guide (TXT)
                        </a>
                    </div>
                </header>

                <form action="{{ route('admin.accounts.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div class="group relative">
                        <input type="file" name="file" required @change="handleFileChange($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="border-2 border-dashed transition-colors rounded-2xl p-8 flex flex-col items-center justify-center text-center bg-slate-50/60 dark:bg-gray-800/40"
                             :class="fileName !== 'Click or drag CSV here' ? 'border-emerald-500 bg-emerald-50/20 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-gray-700 group-hover:border-emerald-400'">
                            <div class="p-3.5 rounded-xl shadow-sm mb-3 transition-all"
                                 :class="fileName !== 'Click or drag CSV here' ? 'bg-emerald-500 text-white shadow-emerald-500/20' : 'bg-white dark:bg-gray-800 text-emerald-500 border border-slate-100 dark:border-gray-700'">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            </div>
                            <p class="text-sm font-bold transition-colors"
                               :class="fileName !== 'Click or drag CSV here' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-gray-200'"
                               x-text="fileName"></p>
                            <p class="text-xs text-slate-400 dark:text-gray-500 mt-1 font-medium">Max file size: 5MB</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <button type="submit" class="flex-1 bg-emerald-600 text-white py-3 rounded-xl font-bold text-sm shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 transition active:scale-[0.98] cursor-pointer">
                            Start Import Process
                        </button>
                        <button type="button" @click="showImportModal = false" class="sm:w-28 bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400 py-3 rounded-xl font-semibold text-sm hover:bg-slate-200 dark:hover:bg-gray-700 transition cursor-pointer">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            {{-- Edit User Modal --}}
            <div x-show="showEditModal" class="relative bg-white dark:bg-gray-900 rounded-[28px] max-w-4xl w-full p-6 sm:p-8 lg:p-10 shadow-2xl overflow-y-auto max-h-[90vh] border border-slate-100 dark:border-gray-800 custom-scrollbar z-10">
                <header class="mb-6 sm:mb-8">
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-gray-100 tracking-tight" x-text="editData.isOnboarding ? 'Modify Onboarding Request' : 'Modify Account Details'">Modify Account</h3>
                    <p class="text-slate-400 dark:text-gray-500 text-xs sm:text-sm mt-1 font-medium" x-text="editData.isOnboarding ? 'Updating details for onboarding: ' + editData.email : 'Updating record for ' + editData.email"></p>
                </header>

                @if ($errors->any())
                    <div class="mb-6 p-5 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 rounded-2xl">
                        <div class="flex items-center gap-2 mb-3 text-rose-600 dark:text-rose-400">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span class="text-xs font-bold uppercase tracking-wider">Update Failed</span>
                        </div>
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-xs text-rose-600 dark:text-rose-400 font-medium flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-rose-400"></span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form :action="editData.isOnboarding ? '/admin/accounts/onboarding-update/' + editData.id : '/admin/accounts/' + editData.id" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" x-model="editData.id">
                    
                    <div class="flex flex-col sm:flex-row items-center gap-6 p-5 bg-slate-50 dark:bg-gray-800/60 rounded-2xl border border-slate-100 dark:border-gray-700">
                        <div class="relative group shrink-0">
                            <template x-if="editData.profile_picture">
                                <img :src="editData.profile_picture" class="w-20 h-20 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow-sm transition group-hover:opacity-75">
                            </template>
                            <template x-if="!editData.profile_picture">
                                <div class="w-20 h-20 rounded-full bg-[#084577] dark:bg-gray-700 flex items-center justify-center border-2 border-white dark:border-gray-700 text-lg font-bold text-white shadow-sm transition group-hover:opacity-75" x-text="editData.initials"></div>
                            </template>
                            <input type="file" name="profile_picture" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="absolute bottom-0 right-0 bg-[#0B5694] text-white p-1 rounded-full shadow pointer-events-none">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            </div>
                        </div>
                        <div class="text-center sm:text-left">
                            <h4 class="font-bold text-sm text-slate-800 dark:text-gray-100">Profile Photo</h4>
                            <p class="text-xs text-slate-400 dark:text-gray-500 mt-0.5">Click image to upload replacement. PNG or JPG, max 2MB.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Email Address</label>
                            <input type="email" name="email" required x-model="editData.email"
                                class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">System Role</label>
                            <input type="text" :value="editData.is_hr_head ? 'Head Supervisor' : editData.role.replace('_', ' ').toUpperCase()" disabled
                                class="w-full bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-semibold text-slate-500 dark:text-gray-400 cursor-not-allowed">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">First Name</label>
                            <input type="text" name="first_name" required x-model="editData.first_name"
                                class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Middle Name</label>
                            <input type="text" name="middle_name" x-model="editData.middle_name"
                                class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200"
                                placeholder="(Optional)">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Last Name</label>
                            <input type="text" name="last_name" required x-model="editData.last_name"
                                class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Phone Number</label>
                        <input type="text" name="phone_number" required x-model="editData.phone_number"
                            class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200">
                    </div>

                    {{-- Dynamic Edit Fields --}}
                    <div class="border-t border-slate-100 dark:border-gray-800 pt-6">
                        <template x-if="editData.role === 'supervisor' || editData.role === 'head_supervisor'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Assigned Office</label>
                                    <select name="office_id" required x-model="editData.office_id"
                                        class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-semibold text-slate-700 dark:text-gray-200 appearance-none cursor-pointer">
                                        <option value="">Select office...</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-gray-800/60 rounded-xl border border-slate-200 dark:border-gray-700">
                                    <div class="relative inline-block w-12 h-6 transition duration-200 ease-in-out bg-slate-200 dark:bg-gray-700 rounded-full" :class="(hasHrHead && !editData.is_hr_head) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                                        <input type="hidden" name="is_hr_head" value="0">
                                        <input type="checkbox" name="is_hr_head" value="1" id="edit_is_hr_head" x-model="editData.is_hr_head" class="absolute w-6 h-6 transition duration-200 ease-in-out bg-white dark:bg-gray-200 border-2 border-slate-200 dark:border-gray-600 rounded-full appearance-none checked:right-0 checked:border-[#0B5694] right-6 cursor-pointer" :class="(hasHrHead && !editData.is_hr_head) ? 'cursor-not-allowed' : 'cursor-pointer'" :disabled="hasHrHead && !editData.is_hr_head">
                                    </div>
                                    <div>
                                        <label for="edit_is_hr_head" class="text-sm font-semibold text-slate-700 dark:text-gray-200 block">Designate as HR Head</label>
                                        <span class="text-[11px] text-slate-400 dark:text-gray-500 font-medium" x-show="hasHrHead && !editData.is_hr_head">Only one HR Head is allowed.</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="editData.role === 'intern'">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">School / University</label>
                                    <input type="text" name="school" required x-model="editData.school"
                                        class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Assigned Office</label>
                                    <select name="office_id" required x-model="editData.office_id"
                                        class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-semibold text-slate-700 dark:text-gray-200 appearance-none cursor-pointer">
                                        <option value="">Select office...</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Gender</label>
                                    <select name="gender" required x-model="editData.gender"
                                        class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-semibold text-slate-700 dark:text-gray-200 appearance-none cursor-pointer">
                                        <option value="">Select gender...</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </template>

                        <template x-if="editData.role === 'school_coordinator'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase block mb-2 tracking-wider">Affiliated School</label>
                                    <input type="text" name="school" required x-model="editData.school"
                                        class="w-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0B5694]/20 focus:border-[#0B5694] outline-none transition-all font-medium text-slate-700 dark:text-gray-200">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-100 dark:border-gray-800">
                        <button type="submit" class="flex-1 bg-[#0B5694] text-white py-3 rounded-xl font-bold text-sm shadow-sm hover:bg-[#084577] transition active:scale-[0.98] cursor-pointer">
                            <span x-text="editData.isOnboarding ? 'Save Onboarding Changes' : 'Save Account Changes'"></span>
                        </button>
                        <button type="button" @click="showEditModal = false" class="sm:w-28 bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400 py-3 rounded-xl font-semibold text-sm hover:bg-slate-200 dark:hover:bg-gray-700 transition cursor-pointer">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            {{-- Delete/Deactivate Confirmation Modal --}}
            <div x-show="showDeleteModal" class="relative bg-white dark:bg-gray-900 rounded-[28px] max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 dark:border-gray-800 z-10">
                <header class="mb-5">
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-gray-100 tracking-tight" x-text="isDeactivated ? 'Confirm Reactivation' : 'Confirm Deactivation'">Confirm Action</h3>
                    <p class="text-slate-400 dark:text-gray-500 text-xs sm:text-sm mt-1 font-medium" x-text="isDeactivated ? 'This action will restore system access for the account.' : 'This action will temporarily lock the account and prevent login access.'"></p>
                </header>

                <div class="flex items-center gap-4 p-5 rounded-2xl border mb-6 text-left" :class="isDeactivated ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-800/40' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-100 dark:border-rose-800/40'">
                    <div class="w-12 h-12 bg-white dark:bg-gray-900 rounded-xl flex items-center justify-center shadow-sm border shrink-0" :class="isDeactivated ? 'text-emerald-600 border-emerald-200/60 dark:border-emerald-800' : 'text-rose-600 border-rose-200/60 dark:border-rose-800'">
                        <template x-if="isDeactivated">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
                        </template>
                        <template x-if="!isDeactivated">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </template>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm tracking-tight" :class="isDeactivated ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400'" x-text="isDeactivated ? 'Reactivation Process' : 'Access Restricted'">Action Process</h4>
                        <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-0.5" x-text="isDeactivated ? 'Are you sure you want to reactivate this user account?' : 'Are you sure you want to deactivate this user account?'"></p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <form id="delete-confirm-form" :action="'/admin/accounts/' + userToDelete" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" @click.prevent="triggerDelete()" class="w-full text-white py-3 rounded-xl font-bold text-sm shadow-sm transition active:scale-[0.98] cursor-pointer" :class="isDeactivated ? 'bg-emerald-600 shadow-emerald-600/20 hover:bg-emerald-700' : 'bg-rose-600 shadow-rose-600/20 hover:bg-rose-700'" x-text="isDeactivated ? 'Yes, Reactivate User' : 'Yes, Deactivate User'">
                            Confirm
                        </button>
                    </form>
                    <button type="button" @click="showDeleteModal = false" class="sm:w-28 bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400 py-3 rounded-xl font-semibold text-sm hover:bg-slate-200 dark:hover:bg-gray-700 transition cursor-pointer">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection
