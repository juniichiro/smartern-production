@props(['portalType', 'userName', 'userRole', 'setupMode' => false])

@php
    $user = Auth::user();
    $details = $user->internDetails ?? $user->supervisorDetails ?? $user->schoolCoordinatorDetails;
    $displayName = $details ? ($details->first_name . ' ' . $details->last_name) : ($user->role->name === 'admin' ? 'SmarTern Admin' : 'User');
    
    // Determine the sidebar navigation to show based on the user's actual role
    // For the test account or super admin, show the active portal/view's links
    $sidebarType = ($user->email === 'test@smartern.com' || $user->role_id === \App\Enums\UserRole::SUPER_ADMIN)
        ? ($portalType ?? 'Admin')
        : match($user->role_id) {
            \App\Enums\UserRole::ADMIN => 'Admin',
            \App\Enums\UserRole::HEAD_SUPERVISOR => 'Head Supervisor',
            \App\Enums\UserRole::SUPERVISOR => 'Supervisor',
            \App\Enums\UserRole::SCHOOL_COORDINATOR => 'School Coordinator',
            \App\Enums\UserRole::INTERN => 'Intern',
            default => $portalType ?? 'User',
        };
@endphp

<div x-data="sidebarManager" 
x-init="
    $watch('desktopCollapsed', value => {
        localStorage.setItem('sidebarCollapsed', value);
        document.body.classList.toggle('sidebar-collapsed', value);
    });
    document.body.classList.toggle('sidebar-collapsed', desktopCollapsed);
"
@close-sidebar.window="sidebarOpen = false"
class="relative">
    {{-- Mobile Header --}}
    <div class="lg:hidden bg-[#0B5694] dark:bg-gray-950 text-white p-3 sm:p-4 flex justify-between items-center fixed top-0 left-0 right-0 z-[60] shadow-md transition-colors duration-300">
        {{-- Sidebar Toggle (Left) --}}
        <button @click="sidebarOpen = !sidebarOpen; if(sidebarOpen) $dispatch('close-notifications')" class="min-w-[44px] min-h-[44px] flex items-center justify-center p-2 rounded-lg hover:bg-white/10 dark:hover:bg-gray-800 transition cursor-pointer">
            <svg x-show="!sidebarOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            <svg x-show="sidebarOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>

        {{-- Center Logo/Title --}}
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/white-smartern.png') }}" alt="SmarTern Logo" class="w-7 h-7">
            <h1 class="font-bold text-lg tracking-normal text-white">SmarTern</h1>
        </div>

        {{-- Notification Toggle (Right) --}}
        <button @click="window.dispatchEvent(new CustomEvent('toggle-notifications')); sidebarOpen = false" class="min-w-[44px] min-h-[44px] flex items-center justify-center p-2 rounded-lg hover:bg-white/10 dark:hover:bg-gray-800 transition cursor-pointer relative" title="Notifications">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
            </svg>
            <span x-data="{ mobileUnread: {{ isset($notifications) ? $notifications->where('is_read', false)->count() : 0 }} }" 
                  @update-unread.window="mobileUnread = $event.detail.count"
                  x-show="mobileUnread > 0"
                  x-text="mobileUnread"
                  class="absolute -top-0.5 -right-0.5 bg-rose-600 text-white text-[9px] min-w-[16px] h-[16px] px-1 flex items-center justify-center rounded-full font-bold shadow-[0_0_10px_rgba(225,29,72,0.6)]"
                  x-cloak></span>
        </button>
    </div>

    {{-- Desktop Toggle Button (Visible when sidebar is collapsed) --}}
    <button x-show="desktopCollapsed" 
            @click="desktopCollapsed = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="hidden lg:flex fixed top-[2.5rem] left-6 z-[90] p-2 bg-transparent text-[#0B5694] dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all active:scale-95 group cursor-pointer"
            title="Expand Sidebar">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover:translate-x-1 transition-transform">
            <polyline points="13 17 18 12 13 7"></polyline><polyline points="6 17 11 12 6 7"></polyline>
        </svg>
    </button>

    {{-- Overlay for Mobile --}}
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-black/50 z-[70] lg:hidden backdrop-blur-sm transition-opacity"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    {{-- Sidebar Content --}}
    <aside :class="{
                'translate-x-0': sidebarOpen,
                '-translate-x-full': !sidebarOpen,
                'lg:translate-x-0': !desktopCollapsed,
                'lg:-translate-x-full': desktopCollapsed
           }" 
           class="w-72 lg:w-64 bg-[#0B5694] dark:bg-gray-900 text-white flex flex-col fixed h-full top-0 z-[80] shadow-xl text-left transition-all duration-300 ease-in-out border-r border-transparent dark:border-gray-800">
        
        <div class="flex-grow overflow-y-auto custom-scrollbar">
            {{-- Logo Section --}}
            <div class="p-8 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 bg-transparent rounded-lg flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/white-smartern.png') }}" alt="SmarTern Logo" class="w-10 h-10">
                    </div>
                    <div class="min-w-0 overflow-hidden">
                        <h1 class="font-bold text-lg leading-tight text-white">SmarTern</h1>
                        <p class="text-xs text-blue-200 dark:text-gray-400 truncate">{{ $sidebarType }}'s Portal</p>
                    </div>
                </div>

                {{-- Collapse Button (Desktop) --}}
                <button @click="desktopCollapsed = true" 
                        class="hidden lg:flex p-1.5 rounded-md hover:bg-white/10 dark:hover:bg-gray-800 text-blue-200 dark:text-gray-400 hover:text-white dark:hover:text-white transition-colors group cursor-pointer"
                        title="Collapse Sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-translate-x-1 transition-transform">
                        <polyline points="11 17 6 12 11 7"></polyline><polyline points="18 17 13 12 18 7"></polyline>
                    </svg>
                </button>
            </div>

        {{-- Profile Section --}}
        <div class="mx-4 mb-6 p-3 bg-white/10 dark:bg-gray-800/50 rounded-xl flex items-center gap-3 border border-white/5 dark:border-gray-700">
            <x-avatar :user="$user" size="10" border="2" />

            <div class="overflow-hidden">
                <p class="text-[14px] font-semibold truncate text-white">{{ $displayName }}</p>
                <p class="text-[11px] text-blue-200 dark:text-gray-400 uppercase tracking-normal leading-tight truncate" title="{{ html_entity_decode($userRole) }}">{{ html_entity_decode($userRole) }}</p>
                <p class="text-[10px] text-blue-300 dark:text-gray-400 font-mono tracking-wider mt-0.5 opacity-90">ID: {{ $user->display_id }}</p>
            </div>
        </div>

        {{-- Navigation Links --}}
        @if(!$setupMode)
        <nav class="px-4 space-y-1">
            @if($sidebarType === 'Admin')
                <x-sidebar.admin-links />
            @elseif($sidebarType === 'Intern')
                <x-sidebar.intern-links />
            @elseif($sidebarType === 'Supervisor')
                <x-sidebar.supervisor-links />
            @elseif($sidebarType === 'Head Supervisor')
                <x-sidebar.head-supervisor-links />
            @elseif($sidebarType === 'School Coordinator')
                <x-sidebar.coordinator-links />
            @else
                {{ $slot }}
            @endif
        </nav>
        @endif
    </div>

    {{-- Templates Section --}}
    @if(!$setupMode && ($sidebarType === 'Admin' || $sidebarType === 'Head Supervisor' || $sidebarType === 'Supervisor' || $sidebarType === 'Intern' || $sidebarType === 'School Coordinator' || $sidebarType === 'User'))
    <div class="px-4 mb-1">
        <a href="{{ route('templates.index') }}"
           class="flex items-center px-3 min-h-[44px] rounded-xl hover:bg-white/10 dark:hover:bg-gray-800 transition-colors cursor-pointer text-white font-medium">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                </svg>
                <span class="text-sm font-medium">Request Templates</span>
            </div>
        </a>
    </div>
    @endif

    {{-- Settings Section --}}
    @if($sidebarType === 'Admin' || $sidebarType === 'Head Supervisor' || $sidebarType === 'Supervisor' || $sidebarType === 'Intern' || $sidebarType === 'School Coordinator' || $sidebarType === 'User')
    <div class="px-4 mb-2">
        <div class="flex items-center px-3 min-h-[44px] rounded-xl hover:bg-white/10 dark:hover:bg-gray-800 transition-colors cursor-pointer text-white font-medium"
             @click="$dispatch('open-modal', 'system-settings-modal')">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <span class="text-sm font-medium">Settings</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Logout --}}
    <div class="p-4 border-t border-white/10 dark:border-gray-800 mt-auto">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 min-h-[44px] border border-white/30 dark:border-gray-700 rounded-xl text-sm font-semibold text-white hover:bg-red-500/20 hover:border-red-500/40 hover:text-red-100 dark:hover:text-red-400 transition-all active:scale-[0.98] cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>

    {{-- SYSTEM SETTINGS MODAL --}}
    <x-settings-modal />
</div>
