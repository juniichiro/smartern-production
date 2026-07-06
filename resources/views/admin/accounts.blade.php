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
    })" class="flex flex-col flex-1">
        {{-- Header --}}
        <x-page-header 
            title="Account Management" 
            subtitle="System Access Control & Batch Processing" 
        >
            <x-slot:actions>
                {{-- TAB NAVIGATION --}}
                <nav class="flex items-center gap-1 p-1 bg-gray-50/50 dark:bg-gray-800/50 rounded-2xl w-fit border border-gray-100 dark:border-gray-800 shrink-0">
                    <button @click="setTab('directory')" 
                            :class="activeTab === 'directory' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" 
                            class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all whitespace-nowrap active:scale-95">
                        User Directory
                    </button>
                    <button @click="setTab('onboarding')" 
                            :class="activeTab === 'onboarding' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-sm border-gray-100 dark:border-gray-600' : 'text-gray-400 dark:text-slate-500 border-transparent hover:text-gray-600 dark:hover:text-gray-300'" 
                            class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all whitespace-nowrap active:scale-95">
                        Onboarding Approvals
                        @if(isset($pendingOnboarding) && $pendingOnboarding->isNotEmpty())
                            <span class="ml-2 px-2 py-0.5 rounded-full text-[9px] bg-red-500 text-white font-extrabold">{{ $pendingOnboarding->count() }}</span>
                        @endif
                    </button>
                </nav>
            </x-slot:actions>
        </x-page-header>

        {{-- SKELETON LOADING VIEW --}}
        @php
            $accountSkeletonCount = $users->isEmpty() ? 5 : min($users->count(), 10);
        @endphp
        
        <div x-show="loading" x-transition:leave="transition ease-in duration-300 pointer-events-none" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            <div x-show="activeTab === 'directory'">
                <x-table-container title="User Directory" subtitle="Hydrating authenticated records...">
                    <x-slot name="headerAction">
                        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 w-full sm:w-auto">
                            <div class="h-[44px] sm:w-36 bg-gray-200 dark:bg-gray-700 rounded-xl animate-shimmer"></div>
                            <div class="h-[44px] sm:w-32 bg-gray-200 dark:bg-gray-700 rounded-xl animate-shimmer"></div>
                        </div>
                    </x-slot>

                    <div class="mb-8">
                        <div class="h-[52px] sm:h-[56px] bg-gray-200 dark:bg-gray-700 rounded-2xl animate-shimmer max-w-md"></div>
                    </div>

                    <div class="hidden md:block">
                        <div class="h-12 bg-gray-200/50 dark:bg-gray-700/30 rounded-t-2xl animate-shimmer mb-1"></div>
                        <x-skeleton type="list" :rows="min($accountSkeletonCount, 5)" />
                    </div>

                    <div class="md:hidden space-y-4">
                        @for($i = 0; $i < min($accountSkeletonCount, 4); $i++)
                            <div class="bg-gray-200/30 dark:bg-gray-700/20 p-6 rounded-2xl animate-shimmer border border-gray-100/50 dark:border-gray-700/30">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="h-4 w-2/3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                        <div class="h-3 w-1/2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                    </div>
                                </div>
                                <div class="flex gap-2 mb-4">
                                    <div class="h-6 w-20 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                                    <div class="h-6 w-24 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                                </div>
                                <div class="flex gap-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                                    <div class="flex-1 h-[44px] bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                                    <div class="flex-1 h-[44px] bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                        <div class="h-4 w-44 bg-gray-200 dark:bg-gray-700 rounded animate-shimmer"></div>
                        <div class="flex items-center gap-2">
                            <div class="w-[44px] h-[44px] bg-gray-200 dark:bg-gray-700 rounded-xl animate-shimmer"></div>
                            <div class="flex items-center gap-1 bg-gray-200/50 dark:bg-gray-700/30 p-1.5 rounded-2xl">
                                <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                                <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                                <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                            </div>
                            <div class="w-[44px] h-[44px] bg-gray-200 dark:bg-gray-700 rounded-xl animate-shimmer"></div>
                        </div>
                    </div>
                </x-table-container>
            </div>

            <div x-show="activeTab === 'onboarding'" x-cloak>
                <div class="bg-white dark:bg-gray-900 rounded-[25px] sm:rounded-[30px] border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div class="p-6 sm:p-8 lg:p-10 border-b border-gray-50 dark:border-gray-800">
                        <div class="h-5 w-52 bg-gray-200 dark:bg-gray-700 rounded animate-shimmer"></div>
                        <div class="h-4 w-36 bg-gray-200 dark:bg-gray-700 rounded animate-shimmer mt-2"></div>
                    </div>
                    <div class="p-4 sm:p-6 lg:p-10">
                        <div class="hidden md:block">
                            <div class="h-12 bg-gray-200/50 dark:bg-gray-700/30 rounded-t-2xl animate-shimmer mb-1"></div>
                            <x-skeleton type="list" :rows="3" />
                        </div>
                        <div class="md:hidden space-y-4">
                            @for($i = 0; $i < 2; $i++)
                                <div class="bg-gray-200/30 dark:bg-gray-700/20 p-6 rounded-2xl animate-shimmer border border-gray-100/50 dark:border-gray-700/30">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                                        <div class="flex-1 space-y-2">
                                            <div class="h-4 w-1/2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                            <div class="h-3 w-1/3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 mb-4">
                                        <div class="h-6 w-28 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                                        <div class="h-6 w-20 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                                    </div>
                                    <div class="flex gap-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                                        <div class="flex-1 h-[44px] bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                                        <div class="flex-1 h-[44px] bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex flex-col flex-1 relative z-20">
            <div x-show="activeTab === 'directory'" class="flex flex-col flex-1 animate-in fade-in duration-300">
                <x-table-container 
                    title="User Directory" 
                    subtitle="Searching across all pages..."
                >
            <x-slot name="headerAction">
                <button @click="showImportModal = true" class="w-full sm:w-auto bg-emerald-500 text-white px-5 sm:px-6 min-h-[44px] rounded-xl text-sm font-bold flex items-center justify-center gap-2 hover:bg-emerald-600 transition shadow-lg shadow-emerald-500/20 active:scale-95 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Import Batch
                </button>
                
                <button @click="showAddModal = true" class="w-full sm:w-auto bg-[#0B5694] text-white px-5 sm:px-6 min-h-[44px] rounded-xl text-sm font-bold flex items-center justify-center gap-2 hover:bg-[#084577] transition shadow-lg shadow-blue-500/20 active:scale-95 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add User
                </button>
            </x-slot>

            {{-- Search Bar --}}
            <form action="{{ route('admin.account') }}" method="GET" class="mb-8 relative max-w-md text-left">
                <button type="submit" class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 dark:text-gray-500 focus:outline-none transition-colors z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </button>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search across all pages..." 
                    @input.debounce.800ms="$el.form.submit()"
                    class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl pl-12 pr-6 py-3.5 sm:py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500 font-medium text-gray-700 dark:text-gray-200">
                
                @if(request('search'))
                    <a href="{{ route('admin.account') }}" class="absolute inset-y-0 right-4 flex items-center text-xs text-gray-400 hover:text-red-500 font-bold uppercase tracking-wider">Clear</a>
                @endif
            </form>

            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto border border-gray-100 dark:border-gray-800 rounded-2xl custom-scrollbar">
                <table class="w-full text-left min-w-[800px]">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-[10px] lg:text-xs font-bold uppercase tracking-wider">

                            <th class="px-6 lg:px-8 py-5">User</th>
                            <th class="px-6 lg:px-8 py-5">Email Address</th>
                            <th class="px-6 lg:px-8 py-5">System Role</th>
                            <th class="px-6 lg:px-8 py-5 text-center">Department/School</th>
                            <th class="px-6 lg:px-8 py-5 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @forelse($users as $user)
                        @php
                            $details = $user->internDetails ?? $user->supervisorDetails ?? $user->schoolCoordinatorDetails;
                            $uInitials = strtoupper(substr($details->first_name ?? 'U', 0, 1) . substr($details->last_name ?? 'N', 0, 1));
                            $middleInitial = ($details?->middle_name) ? substr($details->middle_name, 0, 1) . '. ' : '';
                            $fullName = ($user->role->name === 'admin') 
                                ? 'SmarTern Admin' 
                                : (($details->first_name ?? 'User') . ' ' . $middleInitial . ($details->last_name ?? 'Account'));
                            $isHrHead = optional($user->supervisorDetails)->is_hr_head || $user->role_id == 2;
                        @endphp
                        <tr x-show="!deletingIds.includes('{{ $user->getRouteKey() }}')" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group/row">
                            <td class="px-6 lg:px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <x-avatar :user="$user" size="10" border="1" circular="true" textSize="xs" />
                                    <div class="text-gray-800 dark:text-gray-200 font-bold text-[13px] lg:text-[14px] uppercase tracking-normal">{{ $fullName }}</div>
                                </div>
                            </td>
                            <td class="px-6 lg:px-8 py-6 text-gray-500 dark:text-gray-400 font-medium text-xs lg:text-sm">{{ $user->email }}</td>
                            <td class="px-6 lg:px-8 py-6 text-left">
                                @php
                                    $userDeactivated = $user->locked_until && now()->lessThan($user->locked_until);
                                @endphp
                                <div class="flex flex-wrap gap-2 items-center">
                                    <span class="px-4 py-1.5 rounded-full font-bold uppercase tracking-wider text-[10px] {{ $user->role_id == 5 ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">
                                        {{ $isHrHead ? 'head supervisor' : str_replace('_', ' ', $user->role->name) }}
                                    </span>
                                    @if($userDeactivated)
                                        <span class="px-4 py-1.5 rounded-full font-bold uppercase tracking-wider text-[10px] bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800">
                                            Deactivated
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider text-[11px]">
                                {{ $user->internDetails->office->name ?? $user->supervisorDetails->office->name ?? $user->schoolCoordinatorDetails->school ?? 'N/A' }}
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex justify-center gap-6">
                                    <button @click="editData = { 
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
                                    }; showEditModal = true" class="text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 transition transform hover:scale-110 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                    </button>
                                    @if($userDeactivated)
                                        <button @click="confirmDelete('{{ $user->getRouteKey() }}'); isDeactivated = true" class="text-emerald-400 dark:text-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400 transition transform hover:scale-110 cursor-pointer" title="Reactivate Account">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
                                        </button>
                                    @else
                                        <button @click="confirmDelete('{{ $user->getRouteKey() }}'); isDeactivated = false" class="text-red-400 dark:text-red-500 hover:text-red-600 dark:hover:text-red-400 transition transform hover:scale-110 cursor-pointer" title="Deactivate Account">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-12">
                                <x-empty-state title="No users found." compact="true" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden space-y-4">
                @forelse($users as $user)
                @php
                    $details = $user->internDetails ?? $user->supervisorDetails ?? $user->schoolCoordinatorDetails;
                    $uInitials = strtoupper(substr($details->first_name ?? 'U', 0, 1) . substr($details->last_name ?? 'N', 0, 1));
                    $middleInitial = ($details?->middle_name) ? substr($details->middle_name, 0, 1) . '. ' : '';
                    $fullName = ($user->role->name === 'admin') 
                        ? 'SmarTern Admin' 
                        : (($details->first_name ?? 'User') . ' ' . $middleInitial . ($details->last_name ?? 'Account'));
                    $isHrHead = optional($user->supervisorDetails)->is_hr_head || $user->role_id == 2;
                @endphp
                <div x-show="!deletingIds.includes('{{ $user->getRouteKey() }}')" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="bg-gray-50 dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-4 mb-4">
                        <x-avatar :user="$user" size="12" border="2" circular="true" textSize="xs" />
                        <div>
                            <div class="text-gray-800 dark:text-gray-200 font-bold text-base leading-tight uppercase tracking-tight">{{ $fullName }}</div>
                            <div class="text-gray-500 dark:text-gray-400 font-medium text-xs mt-1">{{ $user->email }}</div>
                        </div>
                    </div>
                    
                    @php
                        $userDeactivated = $user->locked_until && now()->lessThan($user->locked_until);
                    @endphp
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="px-3 py-1 rounded-full font-black uppercase tracking-widest text-[9px] {{ $user->role_id == 5 ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                            {{ $isHrHead ? 'head supervisor' : str_replace('_', ' ', $user->role->name) }}
                        </span>
                        @if($userDeactivated)
                            <span class="px-3 py-1 rounded-full font-black bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 text-[9px] border border-rose-100 dark:border-rose-800 uppercase tracking-wider">
                                Deactivated
                            </span>
                        @endif
                        <span class="px-3 py-1 rounded-full font-black bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400 text-[9px] border border-gray-100 dark:border-gray-800 uppercase tracking-wider">
                            {{ $user->internDetails->office->name ?? $user->supervisorDetails->office->name ?? $user->schoolCoordinatorDetails->school ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="flex gap-4 pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
                        <button @click="editData = { 
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
                        }; showEditModal = true" class="flex-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-blue-600 dark:text-blue-400 min-h-[44px] rounded-xl text-[10px] font-bold uppercase tracking-wider transition active:scale-95 flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                            Edit
                        </button>
                        @if($userDeactivated)
                            <button @click="confirmDelete('{{ $user->getRouteKey() }}'); isDeactivated = true" class="flex-1 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/30 text-emerald-600 dark:text-emerald-500 min-h-[44px] rounded-xl text-[10px] font-bold uppercase tracking-wider transition active:scale-95 flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
                                Reactivate
                            </button>
                        @else
                            <button @click="confirmDelete('{{ $user->getRouteKey() }}'); isDeactivated = false" class="flex-1 bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/30 text-rose-600 dark:text-rose-500 min-h-[44px] rounded-xl text-[10px] font-bold uppercase tracking-wider transition active:scale-95 flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Deactivate
                            </button>
                        @endif
                    </div>
                </div>
                @empty
                    <x-empty-state title="No users found." compact="true" />
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-6 relative z-30">
                <div class="text-[11px] text-gray-400 dark:text-gray-500 font-black uppercase tracking-[0.2em] italic">
                    Showing <b>{{ $users->firstItem() ?? 0 }}</b> to <b>{{ $users->lastItem() ?? 0 }}</b> of {{ $users->total() }}
                </div>

                <div class="flex items-center gap-2">
                    @if ($users->onFirstPage())
                        <span class="p-3.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-300 dark:text-gray-600 cursor-not-allowed flex items-center justify-center"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="p-3.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-[#0B5694] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition shadow-sm flex items-center justify-center cursor-pointer"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></a>
                    @endif

                    <div class="flex items-center gap-1 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-1.5 rounded-2xl shadow-sm overflow-x-auto no-scrollbar">
                        @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}" class="min-w-[44px] min-h-[44px] flex-shrink-0 flex items-center justify-center rounded-xl text-xs font-bold transition-all {{ $page == $users->currentPage() ? 'bg-[#0B5694] text-white shadow-lg' : 'text-gray-400 dark:text-gray-500 hover:text-[#0B5694] dark:hover:text-blue-400' }}">{{ $page }}</a>
                        @endforeach
                    </div>

                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="p-3.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-[#0B5694] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition shadow-sm flex items-center justify-center cursor-pointer"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></a>
                    @else
                        <span class="p-3.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-300 dark:text-gray-600 cursor-not-allowed flex items-center justify-center"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></span>
                    @endif
                </div>
            </div>
        </x-table-container>
        </div>

        {{-- Pending Onboarding Accounts Section --}}
        <div x-show="activeTab === 'onboarding'" x-cloak class="relative z-30 flex flex-col flex-1 animate-in fade-in duration-300">
            <x-table-container 
                title="Pending Onboarding Approvals" 
                subtitle="Applications matching completed. Ready for account creation."
            >
                @if(isset($pendingOnboarding) && $pendingOnboarding->isNotEmpty())
                    {{-- Desktop Table --}}
                    <div class="hidden md:block overflow-x-auto border border-gray-100 dark:border-gray-800 rounded-2xl custom-scrollbar font-medium">
                        <table class="w-full text-left min-w-[800px]">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-[11px] font-bold uppercase tracking-wider">
                                    <th class="px-8 py-5">Applicant</th>
                                    <th class="px-8 py-5">Email Address</th>
                                    <th class="px-8 py-5">Institution</th>
                                    <th class="px-8 py-5 text-center">Assigned Office</th>
                                    <th class="px-8 py-5 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                @foreach($pendingOnboarding as $applicant)
                                @php
                                    $middleInitial = ($applicant->middle_name) ? substr($applicant->middle_name, 0, 1) . '. ' : '';
                                    $fullName = $applicant->first_name . ' ' . $middleInitial . $applicant->last_name;
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group/row">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4 font-bold">
                                            <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 flex items-center justify-center font-bold text-sm border border-blue-100 dark:border-blue-800/50 uppercase">
                                                {{ strtoupper(substr($applicant->first_name, 0, 1) . substr($applicant->last_name, 0, 1)) }}
                                            </div>
                                            <div class="text-gray-800 dark:text-gray-200 text-[14px] uppercase tracking-normal">{{ $fullName }}</div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-gray-500 dark:text-gray-400 text-[13px]">{{ $applicant->email }}</td>
                                    <td class="px-8 py-6 text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider text-[11px]">{{ $applicant->university }}</td>
                                    <td class="px-8 py-6 text-center text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider text-[11px]">
                                        {{ $applicant->office_name }}
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex justify-center gap-4">
                                            <form action="{{ route('admin.accounts.onboarding-approve', $applicant->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl text-xs font-bold transition active:scale-95 flex items-center gap-1 shadow-sm cursor-pointer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                    Approve
                                                </button>
                                            </form>
                                            <button @click="editData = { 
                                                id: '{{ $applicant->id }}', 
                                                email: '{{ $applicant->email }}',
                                                role: 'intern',
                                                first_name: '{{ $applicant->first_name }}',
                                                middle_name: '{{ $applicant->middle_name ?? '' }}',
                                                last_name: '{{ $applicant->last_name }}',
                                                office_id: '{{ $applicant->office_id }}',
                                                school: '{{ $applicant->university }}',
                                                gender: '{{ strtolower($applicant->gender ?? 'male') }}',
                                                gender_other_specify: '{{ $applicant->gender_other_specify ?? '' }}',
                                                phone_number: '{{ $applicant->phone_number ?? '09000000000' }}',
                                                emergency_contact: '{{ $applicant->emergency_contact ?? '' }}',
                                                initials: '{{ strtoupper(substr($applicant->first_name, 0, 1) . substr($applicant->last_name, 0, 1)) }}',
                                                isOnboarding: true,
                                                profile_picture: '{{ $applicant->profile_picture ? (str_starts_with($applicant->profile_picture, 'profiles/') || str_starts_with($applicant->profile_picture, 'profile-pictures/') ? Storage::disk('public')->url($applicant->profile_picture) : asset($applicant->profile_picture)) : '' }}'
                                            }; showEditModal = true" class="bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/30 text-blue-600 dark:text-blue-400 px-4 py-2 rounded-xl text-xs font-bold transition active:scale-95 flex items-center gap-1 shadow-sm cursor-pointer">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="md:hidden space-y-4">
                        @foreach($pendingOnboarding as $applicant)
                        @php
                            $middleInitial = ($applicant->middle_name) ? substr($applicant->middle_name, 0, 1) . '. ' : '';
                            $fullName = $applicant->first_name . ' ' . $middleInitial . $applicant->last_name;
                        @endphp
                        <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-4 mb-4 font-bold">
                                <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 flex items-center justify-center font-bold text-base border border-blue-100 dark:border-blue-800/50 uppercase">
                                    {{ strtoupper(substr($applicant->first_name, 0, 1) . substr($applicant->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-gray-800 dark:text-gray-200 text-base leading-tight uppercase tracking-tight">{{ $fullName }}</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs mt-1 font-medium">{{ $applicant->email }}</div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 mb-4 font-bold text-[9px] border-gray-100 dark:border-gray-800 uppercase tracking-wider">
                                <span class="px-3 py-1 rounded-full bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                                    {{ $applicant->university }}
                                </span>
                                <span class="px-3 py-1 rounded-full bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                                    {{ $applicant->office_name }}
                                </span>
                            </div>
                            <div class="flex gap-4 pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
                                <form action="{{ route('admin.accounts.onboarding-approve', $applicant->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white min-h-[44px] rounded-xl text-xs font-bold uppercase tracking-wider transition active:scale-95 flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        Approve
                                    </button>
                                </form>
                                <button @click="editData = { 
                                    id: '{{ $applicant->id }}', 
                                    email: '{{ $applicant->email }}',
                                    role: 'intern',
                                    first_name: '{{ $applicant->first_name }}',
                                    middle_name: '{{ $applicant->middle_name ?? '' }}',
                                    last_name: '{{ $applicant->last_name }}',
                                    office_id: '{{ $applicant->office_id }}',
                                    school: '{{ $applicant->university }}',
                                    gender: '{{ strtolower($applicant->gender ?? 'male') }}',
                                    gender_other_specify: '{{ $applicant->gender_other_specify ?? '' }}',
                                    phone_number: '{{ $applicant->phone_number ?? '09000000000' }}',
                                    emergency_contact: '{{ $applicant->emergency_contact ?? '' }}',
                                    initials: '{{ strtoupper(substr($applicant->first_name, 0, 1) . substr($applicant->last_name, 0, 1)) }}',
                                    isOnboarding: true,
                                    profile_picture: '{{ $applicant->profile_picture ? (str_starts_with($applicant->profile_picture, 'profiles/') || str_starts_with($applicant->profile_picture, 'profile-pictures/') ? Storage::disk('public')->url($applicant->profile_picture) : asset($applicant->profile_picture)) : '' }}'
                                }; showEditModal = true" class="flex-1 bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/30 text-blue-600 dark:text-blue-400 min-h-[44px] rounded-xl text-xs font-bold uppercase tracking-wider transition active:scale-95 flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                    Edit
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state title="No pending onboarding approvals." compact="true" />
                @endif
            </x-table-container>
        </div>
    </div>

    {{-- MODALS --}}
    <div x-show="showAddModal || showImportModal || showEditModal || showDeleteModal" 
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6" 
         x-cloak>
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showAddModal = false; showImportModal = false; showEditModal = false; showDeleteModal = false"></div>
        
        {{-- Add User Modal --}}
        <div x-show="showAddModal" class="relative bg-white dark:bg-gray-900 rounded-[30px] sm:rounded-[35px] max-w-4xl w-full p-6 sm:p-8 lg:p-12 shadow-2xl overflow-y-auto max-h-[90vh] border border-gray-100 dark:border-gray-800 custom-scrollbar">
            <header class="mb-6 sm:mb-8 lg:mb-10">
                <h3 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 dark:text-gray-100 tracking-tight uppercase">Create New Account</h3>
                <p class="text-gray-400 dark:text-gray-500 text-xs sm:text-sm mt-2 font-medium italic">Fill in the details to register a new user</p>
            </header>

            @if ($errors->any())
                <div class="mb-8 p-6 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 rounded-3xl">
                    <div class="flex items-center gap-3 mb-4 text-rose-600 dark:text-rose-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span class="text-sm font-bold uppercase tracking-wider">Registration Failed</span>

                    </div>
                    <ul class="space-y-2">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs text-rose-500 dark:text-rose-400 font-bold flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-rose-400"></div>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('admin.account.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 lg:space-y-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                            class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium placeholder-gray-300 dark:placeholder-gray-600 text-gray-700 dark:text-gray-200">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">System Role</label>
                        <select name="role_id" x-model="selectedRole" required
                            class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-bold text-gray-700 dark:text-gray-200 appearance-none cursor-pointer">
                            <option value="">Select a role...</option>
                            @foreach($roles->whereNotIn('name', ['admin', 'head_supervisor']) as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ str_replace('_', ' ', $role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 lg:gap-8">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                            class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Middle Name</label>
                        <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                            class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200"
                            placeholder="(Optional)">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                            class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" required placeholder="09XXXXXXXXX"
                        class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                </div>

                {{-- Dynamic Fields --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 border-t border-gray-50 dark:border-gray-800 pt-8">
                    <template x-if="selectedRole == '3'">
                        <div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Assigned Office</label>
                                <select name="office_id" required
                                    class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-bold text-gray-700 dark:text-gray-200 appearance-none cursor-pointer">
                                    <option value="">Select office...</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 mt-6 md:mt-0">
                                <div class="relative inline-block w-12 h-6 transition duration-200 ease-in-out bg-gray-200 dark:bg-gray-700 rounded-full" :class="hasHrHead ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                                    <input type="hidden" name="is_hr_head" value="0">
                                    <input type="checkbox" name="is_hr_head" value="1" id="is_hr_head" {{ old('is_hr_head') ? 'checked' : '' }} class="absolute w-6 h-6 transition duration-200 ease-in-out bg-white dark:bg-gray-200 border-2 border-gray-200 dark:border-gray-600 rounded-full appearance-none checked:right-0 checked:border-[#0B5694] right-6" :class="hasHrHead ? 'cursor-not-allowed' : 'cursor-pointer'" :disabled="hasHrHead">
                                </div>
                                <div>
                                    <label for="is_hr_head" class="text-sm font-bold text-gray-700 dark:text-gray-200 block">Designate as HR Head</label>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium italic" x-show="hasHrHead">Only one HR Head is allowed.</span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="selectedRole == '5'">
                        <div class="col-span-full space-y-6 lg:space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">School / University</label>
                                    <input type="text" name="school" value="{{ old('school') }}" required
                                        class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Assigned Office</label>
                                    <select name="office_id" required
                                        class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-bold text-gray-700 dark:text-gray-200 appearance-none cursor-pointer">
                                        <option value="">Select office...</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Gender</label>
                                    <select name="gender" required
                                        class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-bold text-gray-700 dark:text-gray-200 appearance-none cursor-pointer">
                                        <option value="">Select gender...</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="selectedRole == '4'">
                        <div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Affiliated School</label>
                                <input type="text" name="school" value="{{ old('school') }}" required
                                    class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 pt-6">
                    <button type="submit" class="flex-1 bg-[#0B5694] text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-wider shadow-lg shadow-blue-500/20 hover:bg-[#084577] transition active:scale-[0.98]">
                        Create User Account
                    </button>
                    <button type="button" @click="showAddModal = false" class="sm:w-32 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        {{-- Import Batch Modal --}}
        <div x-show="showImportModal" class="relative bg-white dark:bg-gray-900 rounded-[30px] sm:rounded-[35px] max-w-xl w-full p-5 sm:p-8 lg:p-12 shadow-2xl border border-gray-100 dark:border-gray-800">
            <header class="mb-6 sm:mb-8 text-left">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight uppercase">Import Intern Accounts</h3>
                <p class="text-gray-400 dark:text-gray-500 text-xs sm:text-sm mt-2 font-medium italic">Upload a CSV file containing multiple user details</p>
                
                <div class="mt-4 flex flex-col gap-2">
                    <a href="data:text/csv;charset=utf-8,first_name,middle_name,last_name,email,school,office_name_or_id,role_name_or_id,gender,gender_other_specify,phone_number,profile_picture,emergency_contact%0AJohn,Adam,Doe,john.doe@example.com,State University,Main Office,intern,male,,09123456789,,Jane Doe (Mother)" 
                       download="smartern_batch_users_template.csv" 
                       class="text-xs text-emerald-500 hover:text-emerald-600 dark:text-emerald-400 dark:hover:text-emerald-300 font-bold underline flex items-center gap-1 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="inline"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download Starter CSV Template
                    </a>
                </div>
            </header>

            <form action="{{ route('admin.accounts.import') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <div class="group relative">
                    <input type="file" name="file" required @change="handleFileChange($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div class="border-2 border-dashed transition-colors rounded-[25px] p-10 flex flex-col items-center justify-center text-center bg-gray-50/50 dark:bg-gray-800/50"
                         :class="fileName !== 'Click or drag CSV here' ? 'border-emerald-500 bg-emerald-50/10 dark:bg-emerald-900/10' : 'border-gray-200 dark:border-gray-700 group-hover:border-emerald-400'">
                        <div class="p-4 rounded-2xl shadow-sm mb-4 transition-all"
                             :class="fileName !== 'Click or drag CSV here' ? 'bg-emerald-500 text-white shadow-emerald-500/20' : 'bg-white dark:bg-gray-800 text-emerald-500'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        </div>
                        <p class="text-sm font-black uppercase tracking-tighter transition-colors"
                           :class="fileName !== 'Click or drag CSV here' ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-200'"
                           x-text="fileName"></p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 font-medium italic">Max file size: 5MB</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="flex-1 bg-emerald-500 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-wider shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 transition active:scale-[0.98]">
                        Start Import Process
                    </button>
                    <button type="button" @click="showImportModal = false" class="sm:w-32 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        {{-- Edit User Modal --}}
        <div x-show="showEditModal" class="relative bg-white dark:bg-gray-900 rounded-[30px] sm:rounded-[35px] max-w-4xl w-full p-5 sm:p-8 lg:p-12 shadow-2xl overflow-y-auto max-h-[90vh] border border-gray-100 dark:border-gray-800 custom-scrollbar">
            <header class="mb-6 sm:mb-8 lg:mb-10">
                <h3 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 dark:text-gray-100 tracking-tight uppercase" x-text="editData.isOnboarding ? 'Modify Onboarding Request' : 'Modify Account'">Modify Account</h3>
                <p class="text-gray-400 dark:text-gray-500 text-xs sm:text-sm mt-2 font-medium italic" x-text="editData.isOnboarding ? 'Updating details for onboarding request: ' + editData.email : 'Updating details for ' + editData.email"></p>
            </header>

            @if ($errors->any())
                <div class="mb-8 p-6 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 rounded-3xl">
                    <div class="flex items-center gap-3 mb-4 text-rose-600 dark:text-rose-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span class="text-sm font-bold uppercase tracking-wider">Update Failed</span>
                    </div>
                    <ul class="space-y-2">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs text-rose-500 dark:text-rose-400 font-bold flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-rose-400"></div>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form :action="editData.isOnboarding ? '/admin/accounts/onboarding-update/' + editData.id : '/admin/accounts/' + editData.id" method="POST" enctype="multipart/form-data" class="space-y-6 lg:space-y-8">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" x-model="editData.id">
                
                <div class="flex flex-col sm:flex-row items-center gap-8 p-6 bg-gray-50 dark:bg-gray-800 rounded-[25px] border border-gray-100 dark:border-gray-700 mb-8">
                    <div class="relative group">
                        <template x-if="editData.profile_picture">
                            <img :src="editData.profile_picture" class="w-24 h-24 rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-md transition group-hover:opacity-75">
                        </template>
                        <template x-if="!editData.profile_picture">
                            <div class="w-24 h-24 rounded-full bg-[#084577] dark:bg-gray-700 flex items-center justify-center border-4 border-white dark:border-gray-700 text-xl font-bold text-white shadow-md transition group-hover:opacity-75" x-text="editData.initials"></div>
                        </template>
                        <input type="file" name="profile_picture" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                    <div class="text-center sm:text-left">
                        <h4 class="text-lg font-bold text-gray-800 dark:text-gray-200">Profile Image</h4>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 font-medium">Click image to upload new photo. Supported: JPG, PNG</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Email Address</label>
                        <input type="email" name="email" required x-model="editData.email"
                            class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">System Role</label>
                        <template x-if="editData.isOnboarding">
                            <div>
                                <div class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl px-6 py-4 text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest" x-text="editData.role.replace('_', ' ')"></div>
                                <input type="hidden" name="role_id" value="5">
                            </div>
                        </template>
                        <template x-if="!editData.isOnboarding">
                            <select name="role_id" x-model="editData.role_id" required @change="editData.role = $el.options[$el.selectedIndex].text.toLowerCase().replace(' ', '_')"
                                class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-bold text-gray-700 dark:text-gray-200 appearance-none cursor-pointer">
                                @foreach($roles->whereNotIn('name', ['super_admin', 'head_supervisor']) as $role)
                                    @if($role->name !== 'admin' || Auth::user()->role_id->value === \App\Enums\UserRole::SUPER_ADMIN->value)
                                        <option value="{{ $role->id }}">{{ str_replace('_', ' ', $role->label ?? $role->name) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 lg:gap-8">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">First Name</label>
                        <input type="text" name="first_name" required x-model="editData.first_name"
                            class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Middle Name</label>
                        <input type="text" name="middle_name" x-model="editData.middle_name"
                            class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Last Name</label>
                        <input type="text" name="last_name" required x-model="editData.last_name"
                            class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Phone Number</label>
                    <input type="text" name="phone_number" required x-model="editData.phone_number"
                        class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                </div>

                {{-- Dynamic Edit Fields --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 border-t border-gray-50 dark:border-gray-800 pt-8">
                    <template x-if="editData.role === 'supervisor' || editData.role === 'head_supervisor'">
                        <div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Assigned Office</label>
                                <select name="office_id" required x-model="editData.office_id"
                                    class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-bold text-gray-700 dark:text-gray-200 appearance-none cursor-pointer">
                                    <option value="">Select office...</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 mt-6 md:mt-0">
                                <div class="relative inline-block w-12 h-6 transition duration-200 ease-in-out bg-gray-200 dark:bg-gray-700 rounded-full" :class="(hasHrHead && !editData.is_hr_head) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                                    <input type="hidden" name="is_hr_head" value="0">
                                    <input type="checkbox" name="is_hr_head" value="1" id="edit_is_hr_head" x-model="editData.is_hr_head" class="absolute w-6 h-6 transition duration-200 ease-in-out bg-white dark:bg-gray-200 border-2 border-gray-200 dark:border-gray-600 rounded-full appearance-none checked:right-0 checked:border-[#0B5694] right-6" :class="(hasHrHead && !editData.is_hr_head) ? 'cursor-not-allowed' : 'cursor-pointer'" :disabled="hasHrHead && !editData.is_hr_head">
                                </div>
                                <div>
                                    <label for="edit_is_hr_head" class="text-sm font-bold text-gray-700 dark:text-gray-200 block">Designate as HR Head</label>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium italic" x-show="hasHrHead && !editData.is_hr_head">Only one HR Head is allowed.</span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="editData.role === 'intern'">
                        <div class="col-span-full space-y-6 lg:space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">School / University</label>
                                    <input type="text" name="school" required x-model="editData.school"
                                        class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Assigned Office</label>
                                    <select name="office_id" required x-model="editData.office_id"
                                        class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-bold text-gray-700 dark:text-gray-200 appearance-none cursor-pointer">
                                        <option value="">Select office...</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Gender</label>
                                    <select name="gender" required x-model="editData.gender"
                                        class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-bold text-gray-700 dark:text-gray-200 appearance-none cursor-pointer">
                                        <option value="">Select gender...</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="editData.role === 'school_coordinator'">
                        <div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-[0.2em]">Affiliated School</label>
                                <input type="text" name="school" required x-model="editData.school"
                                    class="w-full bg-gray-50 dark:bg-gray-800 border rounded-2xl px-6 py-4 text-sm focus:ring-2 focus:ring-[#0B5694] outline-none transition-all font-medium text-gray-700 dark:text-gray-200">
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 pt-6">
                    <button type="submit" class="flex-1 bg-[#0B5694] text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-wider shadow-lg shadow-blue-500/20 hover:bg-[#084577] transition active:scale-[0.98]">
                        <span x-text="editData.isOnboarding ? 'Save Onboarding Changes' : 'Save Account Changes'"></span>
                    </button>
                    <button type="button" @click="showEditModal = false" class="sm:w-32 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        {{-- Delete/Deactivate Modal --}}
        <div x-show="showDeleteModal" class="relative bg-white dark:bg-gray-900 rounded-[30px] sm:rounded-[35px] max-w-xl w-full p-5 sm:p-8 lg:p-12 shadow-2xl border border-gray-100 dark:border-gray-800">
            <header class="mb-6 sm:mb-8">
                <h3 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 dark:text-gray-100 tracking-tight uppercase" x-text="isDeactivated ? 'Confirm Reactivation' : 'Confirm Deactivation'">Confirm Action</h3>
                <p class="text-gray-400 dark:text-gray-500 text-xs sm:text-sm mt-2 font-medium italic" x-text="isDeactivated ? 'This action will restore system access for the account.' : 'This action will temporarily lock the account and prevent login access.'"></p>
            </header>

            <div class="flex items-center gap-6 p-6 rounded-[25px] border mb-8 text-left" :class="isDeactivated ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-100 dark:border-emerald-500/30' : 'bg-rose-50 dark:bg-rose-500/10 border-rose-100 dark:border-rose-500/30'">
                <div class="w-14 h-14 bg-white dark:bg-gray-900 rounded-2xl flex items-center justify-center shadow-sm border" :class="isDeactivated ? 'text-emerald-500 border-emerald-100 dark:border-emerald-500/30' : 'text-rose-500 border-rose-100 dark:border-rose-500/30'">
                    <template x-if="isDeactivated">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
                    </template>
                    <template x-if="!isDeactivated">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </template>
                </div>
                <div>
                    <h4 class="font-bold uppercase tracking-tight" :class="isDeactivated ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'" x-text="isDeactivated ? 'Reactivation Process' : 'Access Restricted'">Action Process</h4>
                    <p class="text-xs font-medium italic" :class="isDeactivated ? 'text-emerald-500/70 dark:text-emerald-500/80' : 'text-rose-500/70 dark:text-rose-500/80'" x-text="isDeactivated ? 'Are you sure you want to reactivate this user account?' : 'Are you sure you want to deactivate this user account?'"></p>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <form id="delete-confirm-form" :action="'/admin/accounts/' + userToDelete" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" @click.prevent="triggerDelete()" class="w-full text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-wider shadow-lg transition active:scale-[0.98]" :class="isDeactivated ? 'bg-emerald-500 shadow-emerald-500/20 hover:bg-emerald-600' : 'bg-rose-500 shadow-rose-500/20 hover:bg-rose-600'" x-text="isDeactivated ? 'Yes, Reactivate User' : 'Yes, Deactivate User'">
                        Confirm
                    </button>
                </form>
                <button @click="showDeleteModal = false" class="sm:w-32 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</x-page-layout>
@endsection
