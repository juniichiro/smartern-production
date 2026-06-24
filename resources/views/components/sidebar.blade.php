@props(['portalType', 'userName', 'userRole'])

@php
    $user = Auth::user();
    $details = $user->internDetails ?? $user->supervisorDetails ?? $user->schoolCoordinatorDetails;
    $displayName = $details ? ($details->first_name . ' ' . $details->last_name) : ($user->role->name === 'admin' ? 'SmarTern Admin' : 'User');
    
    // Determine the sidebar navigation to show based on the user's actual role
    // This ensures Head Supervisors don't lose their division links on supervisor pages
    $sidebarType = match($user->role_id) {
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
                <p class="text-[11px] text-blue-200 dark:text-gray-400 uppercase tracking-normal leading-tight">{{ $userRole }}</p>
                <p class="text-[10px] text-blue-300 dark:text-gray-400 font-mono tracking-wider mt-0.5 opacity-90">ID: {{ $user->display_id }}</p>
            </div>
        </div>

        {{-- Navigation Links --}}
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
    </div>

    {{-- Templates Section --}}
    @if($sidebarType === 'Admin' || $sidebarType === 'Head Supervisor' || $sidebarType === 'Supervisor' || $sidebarType === 'Intern' || $sidebarType === 'School Coordinator' || $sidebarType === 'User')
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
<x-modal name="system-settings-modal" maxWidth="4xl">
    <div class="flex flex-col md:flex-row text-gray-900 dark:text-gray-100 min-h-[400px] md:min-h-[500px]"
         x-data="{
             activeTab: 'general',
             darkMode: localStorage.getItem('darkMode') === 'true' || 
                      (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
             fontSize: localStorage.getItem('fontSize') || 'md',
             
             // MFA States
             mfaEnabled: @js((bool)$user->google2fa_enabled),
             totpActive: @js((bool)($user->google2fa_enabled && $user->google2fa_secret && strlen($user->google2fa_secret) >= 16)),
             emailActive: @js((bool)$user->google2fa_enabled),
             
             // MFA Form actions
             mfaSetupStep: 'idle',
             totpSecret: '',
             totpQrCode: '',
             totpDigits: ['', '', '', '', '', ''],
             emailDigits: ['', '', '', '', '', ''],
             
             emailSending: false,
             emailSent: false,
             statusMessage: '',
             statusType: 'success',
             submitting: false,
             
             init() {
                 this.$watch('darkMode', val => {
                     localStorage.setItem('darkMode', val);
                     if (val) document.documentElement.classList.add('dark');
                     else document.documentElement.classList.remove('dark');
                     window.dispatchEvent(new CustomEvent('dark-mode-toggled'));
                 });
                 
                 this.$watch('fontSize', val => {
                     localStorage.setItem('fontSize', val);
                     document.documentElement.classList.remove('font-size-sm', 'font-size-md', 'font-size-lg', 'font-size-xl');
                     document.documentElement.classList.add('font-size-' + val);
                 });
             },
             
             fetchTotpSetup() {
                 this.mfaSetupStep = 'totp_setup';
                 this.statusMessage = '';
                 fetch('{{ route('mfa.setup-data') }}')
                     .then(res => res.json())
                     .then(data => {
                         this.totpSecret = data.secret;
                         this.totpQrCode = data.qrCodeInline;
                     });
             },
             
             verifyTotp() {
                 this.submitting = true;
                 const code = this.totpDigits.join('');
                 fetch('{{ route('mfa.enable-totp-ajax') }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     },
                     body: JSON.stringify({ one_time_password: code })
                 })
                 .then(res => res.json().then(data => ({ ok: res.ok, body: data })))
                 .then(res => {
                     this.submitting = false;
                     if (res.ok) {
                         this.mfaEnabled = true;
                         this.totpActive = true;
                         this.emailActive = true;
                         this.mfaSetupStep = 'idle';
                         this.totpDigits = ['', '', '', '', '', ''];
                         window.showToast?.('Authenticator App enabled successfully.', 'success');
                     } else {
                         window.showToast?.(res.body.message, 'error');
                     }
                 })
                 .catch(() => { this.submitting = false; });
             },
             
             disableTotp() {
                 if (!confirm('Are you sure you want to disable the Authenticator App? You will lose this verification option.')) return;
                 
                 fetch('{{ route('mfa.disable-totp-ajax') }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     }
                 })
                 .then(res => {
                     if (res.ok) {
                         this.totpActive = false;
                         if (!this.totpActive && !this.emailActive) {
                             this.mfaEnabled = false;
                         }
                         window.showToast?.('Authenticator App disabled successfully.', 'success');
                     }
                 });
             },
             
             fetchEmailSetup() {
                 this.mfaSetupStep = 'email_setup';
                 this.statusMessage = '';
                 this.emailSent = false;
             },
             
             sendEmailCode() {
                 if (this.emailSending) return;
                 this.emailSending = true;
                 this.statusMessage = 'Sending code...';
                 this.statusType = 'info';
                 
                 fetch('{{ route('mfa.send-email-code') }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     }
                 })
                 .then(res => res.json().then(data => ({ ok: res.ok, body: data })))
                 .then(res => {
                     this.emailSending = false;
                     if (res.ok) {
                         this.emailSent = true;
                         this.statusMessage = res.body.message;
                         this.statusType = 'success';
                     } else {
                         this.statusMessage = res.body.message || 'Failed to send code.';
                         this.statusType = 'error';
                     }
                 })
                 .catch(() => {
                     this.emailSending = false;
                     this.statusMessage = 'Failed to send code.';
                     this.statusType = 'error';
                 });
             },
             
             verifyEmail() {
                 this.submitting = true;
                 const code = this.emailDigits.join('');
                 fetch('{{ route('mfa.enable-email-ajax') }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     },
                     body: JSON.stringify({ one_time_password: code })
                 })
                 .then(res => res.json().then(data => ({ ok: res.ok, body: data })))
                 .then(res => {
                     this.submitting = false;
                     if (res.ok) {
                         this.mfaEnabled = true;
                         this.emailActive = true;
                         this.mfaSetupStep = 'idle';
                         this.emailDigits = ['', '', '', '', '', ''];
                         window.showToast?.('Email Verification enabled successfully.', 'success');
                     } else {
                         window.showToast?.(res.body.message, 'error');
                     }
                 })
                 .catch(() => { this.submitting = false; });
             },
             
             disableMfa() {
                 if (!confirm('Are you sure you want to disable Two-Factor Authentication completely? This reduces your account security.')) return;
                 
                 fetch('{{ route('mfa.disable-mfa-ajax') }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     }
                 })
                 .then(res => {
                     if (res.ok) {
                         this.mfaEnabled = false;
                         this.totpActive = false;
                         this.emailActive = false;
                         this.mfaSetupStep = 'idle';
                         window.showToast?.('Two-Factor Authentication deactivated.', 'success');
                     }
                 });
             },
             
             focusNext(el, index, type) {
                 el.value = el.value.replace(/\D/g, '');
                 if (el.value.length > 1) {
                     el.value = el.value.slice(-1);
                 }
                 const digits = type === 'totp' ? this.totpDigits : this.emailDigits;
                 digits[index] = el.value;
                 
                 if (el.value && index < 5) {
                     const nextEl = this.$refs[type + 'Input' + (index + 1)];
                     if (nextEl) {
                         nextEl.focus();
                         nextEl.select();
                     }
                 }
             },
             
             handleKeydown(e, index, type) {
                 const digits = type === 'totp' ? this.totpDigits : this.emailDigits;
                 const refPrefix = type === 'totp' ? 'totpInput' : 'emailInput';
                 
                 if (e.key === 'Backspace') {
                     if (!digits[index] && index > 0) {
                         digits[index - 1] = '';
                         const prevEl = this.$refs[refPrefix + (index - 1)];
                         if (prevEl) {
                             prevEl.focus();
                             prevEl.select();
                         }
                         e.preventDefault();
                     } else {
                         digits[index] = '';
                     }
                 }
             }
         }"
    >
        <!-- LEFT SIDEBAR PANEL -->
        <div class="w-full md:w-56 bg-gray-50/50 dark:bg-gray-950/40 p-4 border-b md:border-b-0 md:border-r border-gray-200/60 dark:border-gray-800/80 flex flex-col justify-between shrink-0">
            <div class="space-y-6">
                <div class="px-3 text-left">
                    <h3 class="text-xs font-extrabold text-gray-800 dark:text-gray-200 uppercase tracking-wider">Settings</h3>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-1">System Preferences</p>
                </div>
                <nav class="flex flex-row md:flex-col overflow-x-auto md:overflow-x-visible no-scrollbar">
                    <button type="button" 
                            @click="activeTab = 'general'"
                            :class="activeTab === 'general' ? 'border-l-4 border-[#0B5694] dark:border-blue-500 font-bold text-[#0B5694] dark:text-blue-400 bg-gray-100/60 dark:bg-gray-900/30' : 'border-l-4 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50/50 dark:hover:bg-gray-900/10'"
                            class="flex items-center gap-3 px-4 py-3 text-[10px] uppercase tracking-wider transition-all text-left whitespace-nowrap md:w-full border-t border-b border-transparent focus:outline-none shrink-0 rounded-r-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                        General Settings
                    </button>
                    <button type="button" 
                            @click="activeTab = 'security'"
                            :class="activeTab === 'security' ? 'border-l-4 border-[#0B5694] dark:border-blue-500 font-bold text-[#0B5694] dark:text-blue-400 bg-gray-100/60 dark:bg-gray-900/30' : 'border-l-4 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50/50 dark:hover:bg-gray-900/10'"
                            class="flex items-center gap-3 px-4 py-3 text-[10px] uppercase tracking-wider transition-all text-left whitespace-nowrap md:w-full border-t border-b border-transparent focus:outline-none shrink-0 rounded-r-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        Security & MFA
                    </button>
                </nav>
            </div>
            <div class="hidden md:block pt-4 border-t border-gray-200/40 dark:border-gray-800/60 text-[9px] text-gray-400 dark:text-gray-550 font-bold uppercase tracking-wider text-left pl-3">
                SmarTern v2.0
            </div>
        </div>

        <!-- RIGHT CONTENT DISPLAY -->
        <div class="flex-1 bg-white dark:bg-gray-900 p-5 sm:p-6 md:p-8 flex flex-col justify-between min-h-[400px] md:min-h-[500px]">
            <div>
                <!-- Right Header -->
                <div class="flex justify-between items-start mb-6">
                    <div class="text-left">
                        <h4 class="text-lg font-bold text-gray-800 dark:text-gray-100 tracking-tight"
                            x-text="activeTab === 'general' ? 'General Settings' : 'Two-Factor Security'"></h4>
                        <p class="text-[11px] text-gray-400 dark:text-gray-550 font-bold uppercase tracking-wider mt-1"
                           x-text="activeTab === 'general' ? 'Visual preferences & scaling' : 'Account access verification methods'"></p>
                    </div>
                    <button type="button" @click="$dispatch('close-modal', 'system-settings-modal')" class="p-1.5 text-gray-405 hover:text-gray-800 dark:hover:text-gray-200 transition-colors focus:outline-none cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <!-- Tab content: General -->
                <div x-show="activeTab === 'general'" class="space-y-6" x-transition>
                    <!-- Appearance Selection -->
                    <div class="flex items-center justify-between py-4 border-b border-gray-150 dark:border-gray-800 text-left">
                        <div class="space-y-1">
                            <h5 class="text-xs font-bold text-gray-850 dark:text-gray-200">Theme</h5>
                            <p class="text-[11px] text-gray-400 dark:text-gray-555 font-medium">Switch between light and dark visual interfaces</p>
                        </div>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-600 dark:text-gray-400">
                                <input type="radio" name="darkMode" :checked="!darkMode" @change="darkMode = false" class="text-[#0B5694] dark:text-blue-500 focus:ring-[#0B5694] dark:bg-gray-850 dark:border-gray-700">
                                Light Mode
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-600 dark:text-gray-400">
                                <input type="radio" name="darkMode" :checked="darkMode" @change="darkMode = true" class="text-[#0B5694] dark:text-blue-500 focus:ring-[#0B5694] dark:bg-gray-850 dark:border-gray-700">
                                Dark Mode
                            </label>
                        </div>
                    </div>

                    <!-- Font Scaling Selection -->
                    <div class="flex flex-col gap-3 py-4 border-b border-gray-150 dark:border-gray-800 text-left">
                        <div>
                            <h5 class="text-xs font-bold text-gray-850 dark:text-gray-200">Text Display Scale</h5>
                            <p class="text-[11px] text-gray-400 dark:text-gray-555 font-medium">Adjust the readable text sizing of all elements</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 sm:gap-6 mt-1">
                            @foreach(['sm' => 'Small', 'md' => 'Medium (Default)', 'lg' => 'Large', 'xl' => 'X-Large'] as $sizeKey => $sizeLabel)
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-600 dark:text-gray-400">
                                    <input type="radio" name="fontSize" :checked="fontSize === '{{ $sizeKey }}'" @change="fontSize = '{{ $sizeKey }}'" class="text-[#0B5694] dark:text-blue-500 focus:ring-[#0B5694] dark:bg-gray-850 dark:border-gray-700">
                                    {{ $sizeLabel }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Font Scaling Preview Box -->
                    <div class="py-4 text-left">
                        <div class="pl-4 border-l-2 border-[#0B5694] dark:border-blue-500">
                            <p class="text-[10px] font-extrabold text-[#0B5694] dark:text-blue-400 uppercase tracking-wider">Preview Content</p>
                            <p class="text-gray-550 dark:text-gray-450 italic mt-2 font-medium leading-relaxed">
                                "Adjust font size to see how it scales text readability across all dashboard panels."
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tab content: Security -->
                <div x-show="activeTab === 'security'" class="space-y-6" x-transition>
                    <!-- Status Banner -->
                    <div class="py-3 px-4 rounded-xl border flex items-center justify-between transition-colors duration-200 text-left"
                         :class="mfaEnabled ? 'bg-emerald-50/30 dark:bg-emerald-950/10 border-emerald-100/50 dark:border-emerald-900/30' : 'bg-amber-50/30 dark:bg-amber-950/10 border-amber-100/50 dark:border-amber-900/30'">
                        <div class="flex items-center gap-3">
                            <div class="w-1.5 h-1.5 rounded-full shadow-sm"
                                 :class="mfaEnabled ? 'bg-emerald-500' : 'bg-amber-500'"></div>
                            <div>
                                <h5 class="text-xs font-bold" :class="mfaEnabled ? 'text-emerald-850 dark:text-emerald-400' : 'text-amber-850 dark:text-amber-400'">
                                    Two-Factor Authentication (2FA)
                                </h5>
                                <p class="text-[10px] text-gray-450 dark:text-gray-450 font-semibold mt-0.5" 
                                   x-text="mfaEnabled ? 'Protected: Verification codes are required on login.' : 'Unprotected: Add a verification method to secure your account login.'"></p>
                            </div>
                        </div>
                        
                        <template x-if="mfaEnabled">
                            <button type="button" @click="disableMfa()" class="text-xs font-bold text-red-500 hover:text-red-755 dark:hover:text-red-400 uppercase tracking-wider focus:outline-none transition-colors">
                                Deactivate
                            </button>
                        </template>
                    </div>

                    <!-- Setup Options List -->
                    <div x-show="mfaSetupStep === 'idle'" class="divide-y divide-gray-150 dark:divide-gray-800" x-transition>
                        <!-- Authenticator App Row -->
                        <div class="flex justify-between items-start py-5 gap-4 text-left">
                            <div class="space-y-1">
                                <h5 class="font-bold text-gray-800 dark:text-gray-100 text-xs tracking-tight">Authenticator Application (TOTP)</h5>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold max-w-lg leading-relaxed">
                                    Use verification codes generated by an app on your phone (like Google Authenticator or Microsoft Authenticator).
                                </p>
                            </div>
                            <div class="flex items-center gap-4 shrink-0 mt-0.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider" 
                                      :class="totpActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'">
                                    <span x-text="totpActive ? 'Active' : 'Inactive'"></span>
                                </span>
                                <template x-if="!totpActive">
                                    <button type="button" @click="fetchTotpSetup()" class="text-xs font-bold text-[#0B5694] dark:text-blue-400 hover:underline tracking-wider focus:outline-none uppercase">Configure</button>
                                </template>
                                <template x-if="totpActive">
                                    <button type="button" @click="disableTotp()" class="text-xs font-bold text-red-500 hover:underline tracking-wider focus:outline-none uppercase">Remove</button>
                                </template>
                            </div>
                        </div>

                        <!-- Email Row -->
                        <div class="flex justify-between items-start py-5 gap-4 text-left">
                            <div class="space-y-1">
                                <h5 class="font-bold text-gray-800 dark:text-gray-100 text-xs tracking-tight">Email Verification (OTP)</h5>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold max-w-lg leading-relaxed">
                                    Receive verification codes in your inbox. Useful if you lose access to your authenticator application.
                                </p>
                            </div>
                            <div class="flex items-center gap-4 shrink-0 mt-0.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider" 
                                      :class="emailActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'">
                                    <span x-text="emailActive ? 'Active' : 'Inactive'"></span>
                                </span>
                                <template x-if="!emailActive">
                                    <button type="button" @click="fetchEmailSetup()" class="text-xs font-bold text-[#0B5694] dark:text-blue-400 hover:underline tracking-wider focus:outline-none uppercase">Configure</button>
                                </template>
                                <template x-if="emailActive">
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">Always Backup</span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Setup Sub-view: TOTP Setup -->
                    <div x-show="mfaSetupStep === 'totp_setup'" class="space-y-4" x-transition>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-150 dark:border-gray-800">
                            <button type="button" @click="mfaSetupStep = 'idle'" class="flex items-center gap-2 text-xs font-bold text-gray-455 hover:text-gray-650 dark:hover:text-gray-200 uppercase tracking-wider focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                Back to Methods
                            </button>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Authenticator Setup</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                            <div class="space-y-4 text-left">
                                <p class="text-[11px] text-gray-505 dark:text-gray-400 font-medium leading-relaxed">
                                    1. Scan the QR code using your Authenticator App, or type the following text key:
                                </p>
                                <div class="bg-gray-50 dark:bg-gray-950 px-4 py-2.5 rounded border border-gray-200 dark:border-gray-800 font-mono text-xs text-[#0B5694] dark:text-blue-400 font-bold select-all tracking-wider text-center" 
                                     x-text="totpSecret"></div>
                                
                                <p class="text-[11px] text-gray-550 dark:text-gray-400 font-medium leading-relaxed">
                                    2. Type the generated 6-digit confirmation code:
                                </p>
                                
                                <div class="grid grid-cols-6 gap-2">
                                    @for($i = 0; $i < 6; $i++)
                                        <input type="text"
                                               x-ref="totpInput{{ $i }}"
                                               x-model="totpDigits[{{ $i }}]"
                                               @input="focusNext($el, {{ $i }}, 'totp')"
                                               @keydown="handleKeydown($event, {{ $i }}, 'totp')"
                                               inputmode="numeric"
                                               pattern="[0-9]*"
                                               maxlength="2"
                                               class="w-full h-12 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl text-center text-xl font-extrabold text-[#0B5694] dark:text-blue-400 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 focus:border-[#0B5694] outline-none transition-all">
                                    @endfor
                                </div>
                                
                                <button type="button" 
                                        @click="verifyTotp()"
                                        :disabled="submitting"
                                        class="w-full bg-[#0B5694] dark:bg-blue-600 text-white py-3 rounded font-bold text-xs uppercase tracking-wider hover:bg-[#084577] dark:hover:bg-blue-700 disabled:opacity-50 transition active:scale-[0.98] shadow-sm">
                                    Confirm & Link App
                                </button>
                            </div>
                            
                            <div class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-gray-950 rounded border border-gray-150 dark:border-gray-850">
                                <div class="bg-white p-3 rounded shadow-sm border flex items-center justify-center" x-html="totpQrCode"></div>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mt-3">Scan with Camera</p>
                            </div>
                        </div>
                    </div>

                    <!-- Setup Sub-view: Email Setup -->
                    <div x-show="mfaSetupStep === 'email_setup'" class="space-y-4" x-transition>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-150 dark:border-gray-800">
                            <button type="button" @click="mfaSetupStep = 'idle'" class="flex items-center gap-2 text-xs font-bold text-gray-455 hover:text-gray-650 dark:hover:text-gray-200 uppercase tracking-wider focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                Back to Methods
                            </button>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Verification Request</span>
                        </div>

                        <div class="max-w-md mx-auto space-y-4 text-center">
                            <p class="text-[11px] text-gray-505 dark:text-gray-400 font-medium">
                                To confirm access, we will send a one-time verification code to:
                            </p>
                            <div class="bg-gray-50 dark:bg-gray-950 py-2.5 px-4 rounded border border-gray-150 dark:border-gray-850 font-bold text-xs text-[#0B5694] dark:text-blue-400 select-all">
                                {{ Auth::user()->email }}
                            </div>
                            
                            <button type="button" 
                                    @click="sendEmailCode()"
                                    :disabled="emailSending"
                                    class="bg-[#0B5694] dark:bg-blue-600 text-white px-5 py-2.5 rounded font-bold text-xs uppercase tracking-wider hover:bg-[#084577] dark:hover:bg-blue-700 disabled:opacity-50 transition shadow-sm active:scale-95">
                                <span x-text="emailSending ? 'Sending Code...' : (emailSent ? 'Resend Verification Code' : 'Send Verification Code')"></span>
                            </button>

                            <template x-if="statusMessage">
                                <p class="text-xs font-bold text-center mt-2" 
                                   :class="statusType === 'success' ? 'text-emerald-600 dark:text-emerald-400' : (statusType === 'info' ? 'text-blue-500' : 'text-red-500')" 
                                   x-text="statusMessage"></p>
                            </template>

                            <div x-show="emailSent" x-transition class="text-left space-y-3 pt-2">
                                <p class="text-[11px] text-gray-550 dark:text-gray-400 font-medium text-center">
                                    Enter the 6-digit verification code you received:
                                </p>
                                <div class="grid grid-cols-6 gap-2">
                                    @for($i = 0; $i < 6; $i++)
                                        <input type="text"
                                               x-ref="emailInput{{ $i }}"
                                               x-model="emailDigits[{{ $i }}]"
                                               @input="focusNext($el, {{ $i }}, 'email')"
                                               @keydown="handleKeydown($event, {{ $i }}, 'email')"
                                               inputmode="numeric"
                                               pattern="[0-9]*"
                                               maxlength="2"
                                               class="w-full h-12 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl text-center text-xl font-extrabold text-[#0B5694] dark:text-blue-400 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 focus:border-[#0B5694] outline-none transition-all">
                                    @endfor
                                </div>
                                
                                <button type="button" 
                                        @click="verifyEmail()"
                                        :disabled="submitting"
                                        class="w-full bg-[#0B5694] dark:bg-blue-600 text-white py-3 rounded font-bold text-xs uppercase tracking-wider hover:bg-[#084577] dark:hover:bg-blue-700 disabled:opacity-50 transition active:scale-[0.98] shadow-sm">
                                    Confirm & Enable
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-modal>
</div>
