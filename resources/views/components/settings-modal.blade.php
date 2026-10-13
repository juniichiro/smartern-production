@php
    $user = Auth::user();
@endphp

<div 
    x-data="settingsModalManager()"
    x-init="init()"
    x-cloak
    x-show="isOpen"
    x-on:open-modal.window="if ($event.detail === 'system-settings-modal') openModal($event)"
    x-on:close-modal.window="if ($event.detail === 'system-settings-modal') closeModal()"
    x-on:keydown.escape.window="if (isOpen) closeModal()"
    x-on:keydown.tab="handleTabKey($event)"
    class="fixed inset-0 z-[150] overflow-hidden p-0 sm:p-4 md:p-6 flex items-end sm:items-center justify-center"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    aria-labelledby="settings-modal-title"
>
    {{-- Dimmed, blurred overlay backdrop --}}
    <div 
        x-show="isOpen"
        x-transition:enter="ease-out duration-200 motion-reduce:transition-none"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150 motion-reduce:transition-none"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeModal()"
        class="fixed inset-0 bg-slate-900/60 dark:bg-black/75 backdrop-blur-sm transition-opacity"
        aria-hidden="true"
    ></div>

    {{-- Modal Card Container --}}
    <div 
        x-show="isOpen"
        x-transition:enter="ease-out duration-200 motion-reduce:transition-none"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-150 motion-reduce:transition-none"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative w-full h-[100dvh] max-h-[100dvh] sm:h-[38.75rem] sm:max-h-[90vh] sm:max-h-[90dvh] sm:w-[90vw] lg:w-full lg:max-w-4xl bg-white dark:bg-gray-900 rounded-none sm:rounded-2xl shadow-2xl border-0 sm:border border-slate-200/80 dark:border-gray-800 flex flex-col sm:flex-row overflow-hidden text-left transform transition-all z-10"
        @click.stop
    >
        {{-- ========================================================================= --}}
        {{-- LEFT NAVIGATION PANEL (Desktop: >= 640px)                                  --}}
        {{-- ========================================================================= --}}
        <aside class="hidden sm:flex sm:w-44 md:w-48 lg:w-56 shrink-0 bg-slate-50/70 dark:bg-gray-950/50 border-r border-slate-200/80 dark:border-gray-800 flex-col justify-between p-4 sm:p-5">
            <div class="space-y-5">
                {{-- Header (Title only, no redundant subtitle) --}}
                <div class="px-1">
                    <h2 id="settings-modal-title" class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                        Settings
                    </h2>
                </div>

                {{-- Vertical Tab Navigation (role="tablist") --}}
                <nav 
                    role="tablist" 
                    aria-orientation="vertical" 
                    class="flex flex-col gap-1.5"
                    @keydown.up.prevent="focusPrevTab()"
                    @keydown.down.prevent="focusNextTab()"
                >
                    {{-- General Tab --}}
                    <button 
                        type="button" 
                        role="tab"
                        id="tab-general"
                        :aria-selected="activeTab === 'general'"
                        aria-controls="panel-general"
                        :tabindex="activeTab === 'general' ? 0 : -1"
                        @click="activeTab = 'general'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all text-left min-h-[2.75rem] cursor-pointer outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-400 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-950"
                        :class="activeTab === 'general' 
                            ? 'bg-blue-50/80 text-[#0B5694] dark:bg-blue-950/40 dark:text-blue-400 font-semibold' 
                            : 'text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-gray-200 hover:bg-slate-100/70 dark:hover:bg-gray-800/50 font-medium'"
                    >
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" y1="21" x2="4" y2="14"/>
                            <line x1="4" y1="10" x2="4" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12" y2="3"/>
                            <line x1="20" y1="21" x2="20" y2="16"/>
                            <line x1="20" y1="12" x2="20" y2="3"/>
                            <line x1="1" y1="14" x2="7" y2="14"/>
                            <line x1="9" y1="8" x2="15" y2="8"/>
                            <line x1="17" y1="16" x2="23" y2="16"/>
                        </svg>
                        <span>General</span>
                    </button>

                    {{-- Security & MFA Tab --}}
                    <button 
                        type="button" 
                        role="tab"
                        id="tab-security"
                        :aria-selected="activeTab === 'security'"
                        aria-controls="panel-security"
                        :tabindex="activeTab === 'security' ? 0 : -1"
                        @click="activeTab = 'security'"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all text-left min-h-[2.75rem] cursor-pointer outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-400 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-950"
                        :class="activeTab === 'security' 
                            ? 'bg-blue-50/80 text-[#0B5694] dark:bg-blue-950/40 dark:text-blue-400 font-semibold' 
                            : 'text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-gray-200 hover:bg-slate-100/70 dark:hover:bg-gray-800/50 font-medium'"
                    >
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <span>Security & MFA</span>
                    </button>
                </nav>
            </div>

            {{-- Footer (SmarTern v1.0) --}}
            <div class="pt-3 border-t border-slate-200/60 dark:border-gray-800/60 text-xs font-semibold text-slate-400 dark:text-gray-400 px-1">
                SmarTern v1.0
            </div>
        </aside>

        {{-- ========================================================================= --}}
        {{-- TOP NAVIGATION STRIP (Mobile: < 640px)                                     --}}
        {{-- ========================================================================= --}}
        {{-- Mobile Fixed Header: Title + Close Button --}}
        <div class="sm:hidden shrink-0 bg-white dark:bg-gray-900 border-b border-slate-200/80 dark:border-gray-800 px-4 py-3 pt-[max(0.75rem,env(safe-area-inset-top))] flex items-center justify-between z-10">
            <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                Settings
            </h2>
            <button 
                type="button" 
                @click="closeModal()" 
                class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors cursor-pointer min-h-[44px] min-w-[44px] flex items-center justify-center outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-400"
                aria-label="Close settings"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- Mobile Nav as Shared Segmented Control (Under Header) --}}
        <div class="sm:hidden shrink-0 bg-slate-50/80 dark:bg-gray-950/60 border-b border-slate-200/80 dark:border-gray-800 p-2.5">
            <div 
                role="tablist" 
                class="grid grid-cols-2 gap-1 p-1 bg-slate-200/70 dark:bg-gray-800 rounded-xl"
                @keydown.left.prevent="setMobileTab('general')"
                @keydown.right.prevent="setMobileTab('security')"
            >
                <button 
                    type="button" 
                    role="tab"
                    id="mobile-tab-general"
                    :aria-selected="activeTab === 'general'"
                    aria-controls="panel-general"
                    :tabindex="activeTab === 'general' ? 0 : -1"
                    @click="activeTab = 'general'"
                    class="py-2 px-3 rounded-lg text-xs font-semibold text-center transition-all cursor-pointer min-h-[44px] flex items-center justify-center outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-400"
                    :class="activeTab === 'general' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-xs' : 'text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    General
                </button>
                <button 
                    type="button" 
                    role="tab"
                    id="mobile-tab-security"
                    :aria-selected="activeTab === 'security'"
                    aria-controls="panel-security"
                    :tabindex="activeTab === 'security' ? 0 : -1"
                    @click="activeTab = 'security'"
                    class="py-2 px-3 rounded-lg text-xs font-semibold text-center transition-all cursor-pointer min-h-[44px] flex items-center justify-center outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-400"
                    :class="activeTab === 'security' ? 'bg-white dark:bg-gray-700 text-[#0B5694] dark:text-blue-400 shadow-xs' : 'text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    Security & MFA
                </button>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- RIGHT CONTENT DISPLAY AREA (Scrollable, Internal)                         --}}
        {{-- ========================================================================= --}}
        <div class="flex-1 flex flex-col min-w-0 min-h-0 bg-white dark:bg-gray-900 relative">
            {{-- Desktop Fixed Header (Title + Close Button) --}}
            <div class="hidden sm:flex shrink-0 items-center justify-between px-6 py-3.5 border-b border-slate-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 z-10">
                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight" x-text="activeTab === 'general' ? 'General' : 'Security & MFA'">
                    General
                </h3>
                <button 
                    type="button" 
                    @click="closeModal()" 
                    class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors cursor-pointer min-h-[44px] min-w-[44px] flex items-center justify-center outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-400"
                    aria-label="Close settings"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Scrollable Tab Contents --}}
            <div 
                x-ref="scrollBody"
                @scroll.passive="checkScroll($el)"
                class="flex-1 overflow-y-auto p-4 sm:p-6 custom-scrollbar min-h-0 relative"
            >
                {{-- ================================================================= --}}
                {{-- TAB PANEL 1: GENERAL (Appearance & Text Scaling)                  --}}
                {{-- ================================================================= --}}
                <div 
                    x-show="activeTab === 'general'" 
                    role="tabpanel" 
                    id="panel-general" 
                    aria-labelledby="tab-general"
                    class="space-y-4 sm:space-y-5"
                >
                    {{-- 1. Theme Selection (3 Options: Light, Dark, System) --}}
                    <div class="space-y-2">
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white">
                                Theme
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-gray-400">
                                Choose how the portal looks.
                            </p>
                        </div>

                        {{-- 3 Selectable Theme Cards --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5" role="radiogroup" aria-label="Theme selection">
                            {{-- Light Card --}}
                            <div 
                                role="radio"
                                data-theme-value="light"
                                :aria-checked="themeMode === 'light'"
                                :tabindex="themeMode === 'light' ? 0 : -1"
                                @click="setThemeMode('light')"
                                @keydown="handleThemeKeydown($event, 'light')"
                                title="Clean, high-contrast light theme"
                                class="relative rounded-xl p-3 transition-all cursor-pointer min-h-[3rem] flex items-center justify-between select-none outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-400 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                                :class="themeMode === 'light'
                                    ? 'border-2 border-[#0B5694] dark:border-blue-500 bg-blue-50/50 dark:bg-blue-950/25 shadow-xs'
                                    : 'border border-slate-200/90 dark:border-gray-800 bg-white dark:bg-gray-850 hover:border-slate-300 dark:hover:border-gray-700 hover:bg-slate-50/70 dark:hover:bg-gray-800/50'"
                            >
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-900/40 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white truncate">Light</span>
                                </div>
                                <template x-if="themeMode === 'light'">
                                    <div class="w-4 h-4 rounded-full bg-[#0B5694] dark:bg-blue-500 text-white flex items-center justify-center shrink-0">
                                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </template>
                            </div>

                            {{-- Dark Card --}}
                            <div 
                                role="radio"
                                data-theme-value="dark"
                                :aria-checked="themeMode === 'dark'"
                                :tabindex="themeMode === 'dark' ? 0 : -1"
                                @click="setThemeMode('dark')"
                                @keydown="handleThemeKeydown($event, 'dark')"
                                title="Easier on the eyes in low light"
                                class="relative rounded-xl p-3 transition-all cursor-pointer min-h-[3rem] flex items-center justify-between select-none outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-400 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                                :class="themeMode === 'dark'
                                    ? 'border-2 border-[#0B5694] dark:border-blue-500 bg-blue-50/50 dark:bg-blue-950/25 shadow-xs'
                                    : 'border border-slate-200/90 dark:border-gray-800 bg-white dark:bg-gray-850 hover:border-slate-300 dark:hover:border-gray-700 hover:bg-slate-50/70 dark:hover:bg-gray-800/50'"
                            >
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-[#0B5694] dark:text-blue-400 border border-blue-200/60 dark:border-blue-900/40 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white truncate">Dark</span>
                                </div>
                                <template x-if="themeMode === 'dark'">
                                    <div class="w-4 h-4 rounded-full bg-[#0B5694] dark:bg-blue-500 text-white flex items-center justify-center shrink-0">
                                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </template>
                            </div>

                            {{-- System Card --}}
                            <div 
                                role="radio"
                                data-theme-value="system"
                                :aria-checked="themeMode === 'system'"
                                :tabindex="themeMode === 'system' ? 0 : -1"
                                @click="setThemeMode('system')"
                                @keydown="handleThemeKeydown($event, 'system')"
                                title="Matches your device's appearance"
                                class="relative rounded-xl p-3 transition-all cursor-pointer min-h-[3rem] flex items-center justify-between select-none outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-400 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                                :class="themeMode === 'system'
                                    ? 'border-2 border-[#0B5694] dark:border-blue-500 bg-blue-50/50 dark:bg-blue-950/25 shadow-xs'
                                    : 'border border-slate-200/90 dark:border-gray-800 bg-white dark:bg-gray-850 hover:border-slate-300 dark:hover:border-gray-700 hover:bg-slate-50/70 dark:hover:bg-gray-800/50'"
                            >
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border border-slate-200 dark:border-gray-700 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                            <line x1="8" y1="21" x2="16" y2="21"/>
                                            <line x1="12" y1="17" x2="12" y2="21"/>
                                        </svg>
                                    </div>
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-xs font-bold text-slate-900 dark:text-white truncate">System</span>
                                        <span class="text-[10px] font-semibold text-slate-400 dark:text-gray-400 shrink-0" x-text="systemIsDark ? '(Dark)' : '(Light)'"></span>
                                    </div>
                                </div>
                                <template x-if="themeMode === 'system'">
                                    <div class="w-4 h-4 rounded-full bg-[#0B5694] dark:bg-blue-500 text-white flex items-center justify-center shrink-0">
                                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Text Display Scale (4 Steps: Small, Medium, Large, Extra large) --}}
                    <div class="space-y-2">
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white">
                                Text size
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-gray-400">
                                Changes text size across the portal.
                            </p>
                        </div>

                        {{-- 4 Selectable Scale Cards in 1 Row on Desktop, 2x2 on Mobile --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2" role="radiogroup" aria-label="Text scale selection">
                            @foreach([
                                'sm' => ['title' => 'Small', 'size' => '14px', 'sampleClass' => 'text-xs'],
                                'md' => ['title' => 'Medium', 'size' => '16px (Default)', 'sampleClass' => 'text-sm'],
                                'lg' => ['title' => 'Large', 'size' => '18px', 'sampleClass' => 'text-base'],
                                'xl' => ['title' => 'Extra large', 'size' => '20px', 'sampleClass' => 'text-lg'],
                            ] as $key => $meta)
                                <div 
                                    role="radio"
                                    data-size-value="{{ $key }}"
                                    :aria-checked="fontSize === '{{ $key }}'"
                                    :tabindex="fontSize === '{{ $key }}' ? 0 : -1"
                                    @click="setFontSize('{{ $key }}')"
                                    @keydown="handleFontSizeKeydown($event, '{{ $key }}')"
                                    class="relative rounded-xl p-2.5 transition-all cursor-pointer min-h-[3.75rem] flex flex-col justify-between select-none outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-400 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
                                    :class="fontSize === '{{ $key }}'
                                        ? 'border-2 border-[#0B5694] dark:border-blue-500 bg-blue-50/50 dark:bg-blue-950/25 shadow-xs'
                                        : 'border border-slate-200/90 dark:border-gray-800 bg-white dark:bg-gray-850 hover:border-slate-300 dark:hover:border-gray-700 hover:bg-slate-50/70 dark:hover:bg-gray-800/50'"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="font-extrabold text-slate-700 dark:text-gray-200 {{ $meta['sampleClass'] }} leading-none">Aa</span>
                                        <template x-if="fontSize === '{{ $key }}'">
                                            <div class="w-3.5 h-3.5 rounded-full bg-[#0B5694] dark:bg-blue-500 text-white flex items-center justify-center shrink-0">
                                                <svg class="w-2 h-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="mt-1.5 min-w-0">
                                        <div class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $meta['title'] }}</div>
                                        <div class="text-[10px] font-medium text-slate-400 dark:text-gray-400 truncate">{{ $meta['size'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 3. Preview Area (Realistic UI Component) --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white">
                                Preview
                            </h4>
                            <span 
                                class="text-[11px] font-semibold text-[#0B5694] dark:text-blue-400 bg-blue-50 dark:bg-blue-950/50 border border-blue-200/60 dark:border-blue-800/40 px-2 py-0.5 rounded-full"
                                x-text="fontSize === 'sm' ? '14px · Small' : (fontSize === 'md' ? '16px · Medium' : (fontSize === 'lg' ? '18px · Large' : '20px · Extra large'))"
                            ></span>
                        </div>

                        {{-- Compact Sample Preview Card (Non-interactive) --}}
                        <div 
                            class="rounded-xl border border-slate-200/90 dark:border-gray-800 bg-slate-50/70 dark:bg-gray-850/60 p-3 sm:p-3.5 space-y-2.5 pointer-events-none select-none"
                            aria-hidden="true"
                            aria-label="Sample preview"
                            data-region="live-preview"
                        >
                            <div class="flex items-center justify-between gap-2.5">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    {{-- Avatar --}}
                                    <div class="w-8 h-8 rounded-full bg-[#0B5694] dark:bg-blue-600 text-white font-bold flex items-center justify-center text-xs shadow-xs shrink-0">
                                        AR
                                    </div>
                                    <div class="min-w-0">
                                        <h5 class="text-xs font-bold text-slate-900 dark:text-white leading-tight truncate">
                                            Alex Rivera
                                        </h5>
                                        <p class="text-[11px] font-medium text-slate-500 dark:text-gray-400 truncate">
                                            Intern · Information Technology
                                        </p>
                                    </div>
                                </div>

                                {{-- Status Pill --}}
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Active</span>
                                </div>
                            </div>

                            <p class="text-[11px] font-medium text-slate-600 dark:text-gray-300 leading-snug">
                                Assigned to Manila Health Department · Office Geofence Verified
                            </p>

                            {{-- Button Pair (Non-interactive) --}}
                            <div class="flex items-center gap-2 pt-0.5">
                                <button 
                                    type="button" 
                                    tabindex="-1"
                                    aria-disabled="true"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white dark:bg-gray-800 text-slate-700 dark:text-gray-200 border border-slate-200 dark:border-gray-700 pointer-events-none"
                                >
                                    View details
                                </button>
                                <button 
                                    type="button" 
                                    tabindex="-1"
                                    aria-disabled="true"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#0B5694] dark:bg-blue-600 text-white pointer-events-none shadow-xs"
                                >
                                    Edit profile
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================================================================= --}}
                {{-- TAB PANEL 2: SECURITY & MFA                                       --}}
                {{-- ================================================================= --}}
                <div 
                    x-show="activeTab === 'security'" 
                    role="tabpanel" 
                    id="panel-security" 
                    aria-labelledby="tab-security"
                    class="space-y-5 sm:space-y-6"
                >
                    {{-- Pane Header --}}
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white sm:hidden tracking-tight">
                            Security & MFA
                        </h3>
                        <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-0.5">
                            Manage two-factor authentication and account protection methods.
                        </p>
                    </div>

                    {{-- Overall 2FA Status Banner --}}
                    <div 
                        class="rounded-2xl p-4 sm:p-5 border transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                        :class="mfaEnabled 
                            ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200/70 dark:border-emerald-900/40' 
                            : 'bg-amber-50/60 dark:bg-amber-950/20 border-amber-200/70 dark:border-amber-900/40'"
                    >
                        <div class="flex items-start gap-3">
                            <div 
                                class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                                :class="mfaEnabled ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400'"
                            >
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs sm:text-sm font-bold" :class="mfaEnabled ? 'text-emerald-900 dark:text-emerald-200' : 'text-amber-900 dark:text-amber-200'">
                                        Two-factor authentication
                                    </h4>
                                    <span 
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                        :class="mfaEnabled ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300'"
                                        x-text="mfaEnabled ? 'Active' : 'Inactive'"
                                    ></span>
                                </div>
                                <p 
                                    class="text-xs font-medium mt-1 leading-relaxed"
                                    :class="mfaEnabled ? 'text-emerald-800/80 dark:text-emerald-300/80' : 'text-amber-800/80 dark:text-amber-300/80'"
                                    x-text="mfaEnabled 
                                        ? 'Protected: A verification passcode is required during sign in from new devices.' 
                                        : 'Unprotected: Add a verification method below to safeguard your account against unauthorized logins.'"
                                ></p>
                            </div>
                        </div>

                        <template x-if="mfaEnabled && !confirmingDeactivateMfa">
                            <button 
                                type="button" 
                                @click="confirmingDeactivateMfa = true" 
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-100/60 dark:hover:bg-red-950/40 border border-red-200 dark:border-red-900/50 transition-colors shrink-0 self-start sm:self-auto cursor-pointer min-h-[38px]"
                            >
                                Deactivate 2FA
                            </button>
                        </template>
                    </div>

                    {{-- In-pane Confirmation for Deactivating 2FA (Replaces browser confirm) --}}
                    <div 
                        x-show="confirmingDeactivateMfa" 
                        x-transition 
                        class="rounded-2xl p-4 sm:p-5 bg-red-50/80 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 space-y-3"
                    >
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-red-900 dark:text-red-200">
                                    Deactivate two-factor authentication?
                                </h4>
                                <p class="text-xs font-medium text-red-700 dark:text-red-300 mt-0.5 leading-relaxed">
                                    This reduces your account security. Any user possessing your password will be able to log in without secondary verification.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 justify-end pt-1">
                            <button 
                                type="button" 
                                @click="confirmingDeactivateMfa = false" 
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors cursor-pointer min-h-[38px]"
                            >
                                Cancel
                            </button>
                            <button 
                                type="button" 
                                @click="executeDisableMfa()" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors cursor-pointer min-h-[38px] shadow-xs"
                            >
                                Yes, deactivate
                            </button>
                        </div>
                    </div>

                    {{-- Main Methods List (Idle Step) --}}
                    <div x-show="mfaSetupStep === 'idle'" class="space-y-3.5">
                        {{-- Method 1: Authenticator App (TOTP) --}}
                        <div class="rounded-2xl border border-slate-200/90 dark:border-gray-800 bg-white dark:bg-gray-850 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-[#0B5694] dark:text-blue-400 border border-blue-200/60 dark:border-blue-900/40 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                        <line x1="12" y1="18" x2="12.01" y2="18"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                            Authenticator application (TOTP)
                                        </h4>
                                        <span 
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                            :class="totpActive ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40' : 'bg-slate-100 dark:bg-gray-800 text-slate-500 dark:text-gray-400'"
                                            x-text="totpActive ? 'Active' : 'Not configured'"
                                        ></span>
                                    </div>
                                    <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-1 max-w-lg leading-relaxed">
                                        Use verification passcodes generated by an app on your phone (Google Authenticator, Microsoft Authenticator, or 1Password).
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 self-start sm:self-auto flex items-center gap-2">
                                <template x-if="!totpActive">
                                    <button 
                                        type="button" 
                                        @click="fetchTotpSetup()" 
                                        class="px-4 py-2 rounded-xl bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white text-xs font-semibold transition-all shadow-xs cursor-pointer min-h-[38px] active:scale-95"
                                    >
                                        Set up app
                                    </button>
                                </template>
                                <template x-if="totpActive && !confirmingDisableTotp">
                                    <button 
                                        type="button" 
                                        @click="confirmingDisableTotp = true" 
                                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 border border-red-200 dark:border-red-900/50 transition-colors cursor-pointer min-h-[38px]"
                                    >
                                        Remove app
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- In-pane Confirmation for Disabling TOTP --}}
                        <div 
                            x-show="confirmingDisableTotp" 
                            x-transition 
                            class="rounded-2xl p-4 bg-red-50/80 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 space-y-3"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                </div>
                                <div>
                                    <h5 class="text-xs font-bold text-red-900 dark:text-red-200">Remove Authenticator App?</h5>
                                    <p class="text-xs font-medium text-red-700 dark:text-red-300 mt-0.5">You will no longer be able to use your authenticator app to log in.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 justify-end">
                                <button type="button" @click="confirmingDisableTotp = false" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 transition-colors cursor-pointer">Cancel</button>
                                <button type="button" @click="executeDisableTotp()" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors cursor-pointer">Yes, remove</button>
                            </div>
                        </div>

                        {{-- Method 2: Email Verification (OTP) --}}
                        <div class="rounded-2xl border border-slate-200/90 dark:border-gray-800 bg-white dark:bg-gray-850 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border border-slate-200 dark:border-gray-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                        <polyline points="22,6 12,13 2,6"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                            Email verification (OTP)
                                        </h4>
                                        <span 
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                            :class="emailActive ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40' : 'bg-slate-100 dark:bg-gray-800 text-slate-500 dark:text-gray-400'"
                                            x-text="emailActive ? 'Active' : 'Not configured'"
                                        ></span>
                                    </div>
                                    <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-1 max-w-lg leading-relaxed">
                                        Receive one-time passcodes in your inbox ({{ $user->email ?? 'your email' }}). Helpful backup if you ever lose your authenticator phone.
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 self-start sm:self-auto">
                                <template x-if="!emailActive">
                                    <button 
                                        type="button" 
                                        @click="fetchEmailSetup()" 
                                        class="px-4 py-2 rounded-xl bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white text-xs font-semibold transition-all shadow-xs cursor-pointer min-h-[38px] active:scale-95"
                                    >
                                        Set up email
                                    </button>
                                </template>
                                <template x-if="emailActive">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400 border border-slate-200/80 dark:border-gray-700">
                                        Always backup
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Setup Sub-view: TOTP Setup --}}
                    <div x-show="mfaSetupStep === 'totp_setup'" x-transition class="space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-gray-800">
                            <button 
                                type="button" 
                                @click="mfaSetupStep = 'idle'" 
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer min-h-[36px]"
                            >
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                                <span>Back to verification methods</span>
                            </button>
                            <span class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">
                                Step 1 of 2
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            {{-- Step 1 & 2 Instructions + Code Inputs --}}
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                        1. Scan QR code or copy key
                                    </h4>
                                    <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-1">
                                        Scan the QR code with your authenticator app, or copy this secret text key:
                                    </p>
                                </div>

                                {{-- Secret Key with 1-click Copy --}}
                                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-gray-950 border border-slate-200 dark:border-gray-800">
                                    <span class="font-mono text-xs font-bold text-[#0B5694] dark:text-blue-400 select-all tracking-wider flex-1 truncate px-2" x-text="totpSecret"></span>
                                    <button 
                                        type="button" 
                                        @click="copySecret()" 
                                        class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-xs font-semibold text-slate-700 dark:text-gray-300 hover:bg-slate-50 transition-colors shrink-0 flex items-center gap-1.5 cursor-pointer min-h-[34px]"
                                        aria-label="Copy secret key"
                                    >
                                        <template x-if="!copiedSecret">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                        </template>
                                        <template x-if="copiedSecret">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                        </template>
                                        <span x-text="copiedSecret ? 'Copied!' : 'Copy key'"></span>
                                    </button>
                                </div>

                                <div class="pt-2">
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                        2. Enter 6-digit confirmation code
                                    </h4>
                                    <p class="text-xs font-medium text-slate-500 dark:text-gray-400 mt-1">
                                        Type the code generated by your app to verify setup:
                                    </p>
                                </div>

                                {{-- 6 Digit Inputs --}}
                                <div class="grid grid-cols-6 gap-1.5 sm:gap-2 max-w-xs">
                                    @for($i = 0; $i < 6; $i++)
                                        <input 
                                            type="text"
                                            x-ref="totpInput{{ $i }}"
                                            x-model="totpDigits[{{ $i }}]"
                                            @input="focusNext($el, {{ $i }}, 'totp')"
                                            @keydown="handleKeydown($event, {{ $i }}, 'totp')"
                                            @paste="handlePaste($event, 'totp')"
                                            inputmode="numeric"
                                            pattern="[0-9]*"
                                            maxlength="2"
                                            aria-label="Digit {{ $i + 1 }}"
                                            class="w-full h-11 sm:h-12 bg-slate-50 dark:bg-gray-800 border border-slate-300 dark:border-gray-700 rounded-xl text-center text-lg font-extrabold text-[#0B5694] dark:text-blue-400 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 focus:border-[#0B5694] outline-none transition-all"
                                        >
                                    @endfor
                                </div>

                                <button 
                                    type="button" 
                                    @click="verifyTotp()" 
                                    :disabled="submitting || totpDigits.join('').length < 6" 
                                    class="w-full bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white py-3 rounded-xl font-semibold text-xs uppercase tracking-wider transition-all disabled:opacity-50 shadow-xs cursor-pointer active:scale-98 min-h-[44px]"
                                >
                                    <span x-text="submitting ? 'Verifying...' : 'Confirm & Link App'"></span>
                                </button>
                            </div>

                            {{-- High-Contrast QR Code Card --}}
                            <div class="flex flex-col items-center justify-center p-5 bg-slate-50 dark:bg-gray-950 rounded-2xl border border-slate-200/80 dark:border-gray-800">
                                <div class="bg-white p-3.5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-center" x-html="totpQrCode"></div>
                                <p class="text-[11px] font-semibold text-slate-400 dark:text-gray-400 uppercase tracking-wider mt-3">
                                    Scan with Camera
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Setup Sub-view: Email Setup --}}
                    <div x-show="mfaSetupStep === 'email_setup'" x-transition class="space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-gray-800">
                            <button 
                                type="button" 
                                @click="mfaSetupStep = 'idle'" 
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer min-h-[36px]"
                            >
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                                <span>Back to verification methods</span>
                            </button>
                            <span class="text-xs font-bold text-slate-400 dark:text-gray-500 uppercase tracking-wider">
                                Email Verification
                            </span>
                        </div>

                        <div class="max-w-md mx-auto space-y-4 text-center py-2">
                            <p class="text-xs font-medium text-slate-600 dark:text-gray-300">
                                To confirm access, we will send a 6-digit verification code to:
                            </p>
                            <div class="bg-slate-50 dark:bg-gray-950 py-2.5 px-4 rounded-xl border border-slate-200 dark:border-gray-800 font-bold text-xs sm:text-sm text-[#0B5694] dark:text-blue-400 select-all">
                                {{ $user->email ?? 'your email' }}
                            </div>

                            <button 
                                type="button" 
                                @click="sendEmailCode()" 
                                :disabled="emailSending" 
                                class="bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold text-xs uppercase tracking-wider transition-all disabled:opacity-50 shadow-xs active:scale-95 cursor-pointer min-h-[40px]"
                            >
                                <span x-text="emailSending ? 'Sending Code...' : (emailSent ? 'Resend Verification Code' : 'Send Verification Code')"></span>
                            </button>

                            <template x-if="statusMessage">
                                <p 
                                    class="text-xs font-semibold text-center mt-2" 
                                    :class="statusType === 'success' ? 'text-emerald-600 dark:text-emerald-400' : (statusType === 'info' ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400')" 
                                    x-text="statusMessage"
                                ></p>
                            </template>

                            <div x-show="emailSent" x-transition class="text-left space-y-3 pt-3">
                                <p class="text-xs font-medium text-slate-600 dark:text-gray-300 text-center">
                                    Enter the 6-digit code received in your inbox:
                                </p>
                                <div class="grid grid-cols-6 gap-1.5 sm:gap-2 max-w-xs mx-auto">
                                    @for($i = 0; $i < 6; $i++)
                                        <input 
                                            type="text"
                                            x-ref="emailInput{{ $i }}"
                                            x-model="emailDigits[{{ $i }}]"
                                            @input="focusNext($el, {{ $i }}, 'email')"
                                            @keydown="handleKeydown($event, {{ $i }}, 'email')"
                                            @paste="handlePaste($event, 'email')"
                                            inputmode="numeric"
                                            pattern="[0-9]*"
                                            maxlength="2"
                                            aria-label="Email digit {{ $i + 1 }}"
                                            class="w-full h-11 sm:h-12 bg-slate-50 dark:bg-gray-800 border border-slate-300 dark:border-gray-700 rounded-xl text-center text-lg font-extrabold text-[#0B5694] dark:text-blue-400 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 focus:border-[#0B5694] outline-none transition-all"
                                        >
                                    @endfor
                                </div>

                                <button 
                                    type="button" 
                                    @click="verifyEmail()" 
                                    :disabled="submitting || emailDigits.join('').length < 6" 
                                    class="w-full bg-[#0B5694] hover:bg-[#084577] dark:bg-blue-600 dark:hover:bg-blue-700 text-white py-3 rounded-xl font-semibold text-xs uppercase tracking-wider transition-all disabled:opacity-50 shadow-xs active:scale-98 cursor-pointer min-h-[44px]"
                                >
                                    <span x-text="submitting ? 'Verifying...' : 'Confirm & Enable'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bottom Fade Gradient Overlay (Sticky inside scroll container) --}}
                <div 
                    class="pointer-events-none sticky bottom-0 left-0 right-0 h-6 -mb-6 bg-gradient-to-t from-white dark:from-gray-900 to-transparent transition-opacity duration-200"
                    :class="canScrollDown ? 'opacity-100' : 'opacity-0'"
                    aria-hidden="true"
                ></div>
            </div>

            {{-- Sticky Footer (inside right pane, always visible on General tab) --}}
            <div 
                x-show="activeTab === 'general'"
                class="shrink-0 px-4 sm:px-6 py-3 bg-white dark:bg-gray-900 border-t border-slate-200/80 dark:border-gray-800 transition-shadow pb-[max(0.75rem,env(safe-area-inset-bottom))]"
                :class="canScrollDown ? 'shadow-[0_-4px_12px_rgba(0,0,0,0.05)] dark:shadow-[0_-4px_12px_rgba(0,0,0,0.25)]' : ''"
            >
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-gray-400 min-w-0">
                        <span class="truncate">Changes are saved automatically</span>
                        <span 
                            x-show="showSaveIndicator" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="inline-flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400 shrink-0" 
                            aria-live="polite"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Saved</span>
                        </span>
                    </div>

                    <button 
                        type="button" 
                        @click="resetToDefaults()" 
                        class="text-xs font-semibold text-slate-500 hover:text-[#0B5694] dark:text-gray-400 dark:hover:text-blue-400 underline underline-offset-2 transition-colors cursor-pointer shrink-0 min-h-[44px] px-2 flex items-center outline-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0B5694] dark:focus-visible:ring-blue-400 rounded-md"
                    >
                        Reset to defaults
                    </button>
                </div>

                {{-- Inline Undo Banner (Appears if user resets to default) --}}
                <div 
                    x-show="showUndoBanner" 
                    x-transition 
                    class="mt-2.5 p-2.5 rounded-xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/70 dark:border-blue-900/50 flex items-center justify-between text-xs text-[#0B5694] dark:text-blue-300"
                >
                    <span>Display preferences reset to default.</span>
                    <button 
                        type="button" 
                        @click="undoReset()" 
                        class="font-bold underline ml-3 hover:text-[#084577] dark:hover:text-blue-200 cursor-pointer"
                    >
                        Undo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function settingsModalManager() {
    return {
        isOpen: false,
        activeTab: 'general',
        triggerElement: null,

        // Layout & Scroll State
        canScrollDown: false,
        themeOptions: ['light', 'dark', 'system'],
        fontSizeOptions: ['sm', 'md', 'lg', 'xl'],

        // Theme & Scaling
        themeMode: 'system',
        systemIsDark: window.matchMedia('(prefers-color-scheme: dark)').matches,
        fontSize: 'md',
        showSaveIndicator: false,
        saveIndicatorTimer: null,
        showUndoBanner: false,
        undoTimer: null,
        previousSettings: null,

        // MFA States
        mfaEnabled: @js((bool)($user?->google2fa_enabled)),
        totpActive: @js((bool)($user?->google2fa_enabled && $user?->google2fa_secret && strlen($user?->google2fa_secret) >= 16)),
        emailActive: @js((bool)($user?->google2fa_enabled)),

        // MFA Setup & Actions
        mfaSetupStep: 'idle',
        totpSecret: '',
        totpQrCode: '',
        totpDigits: ['', '', '', '', '', ''],
        emailDigits: ['', '', '', '', '', ''],
        copiedSecret: false,

        // In-pane confirmations
        confirmingDeactivateMfa: false,
        confirmingDisableTotp: false,

        // Async status
        emailSending: false,
        emailSent: false,
        statusMessage: '',
        statusType: 'success',
        submitting: false,
        csrfToken: '',

        init() {
            const meta = document.querySelector('meta[name=csrf-token]');
            this.csrfToken = meta ? meta.getAttribute('content') : '';

            // Load themeMode
            const savedTheme = localStorage.getItem('themeMode');
            if (savedTheme) {
                this.themeMode = savedTheme;
            } else if ('darkMode' in localStorage) {
                this.themeMode = localStorage.getItem('darkMode') === 'true' ? 'dark' : 'light';
            } else {
                this.themeMode = 'system';
            }

            // Load fontSize
            this.fontSize = localStorage.getItem('fontSize') || 'md';

            // Watch OS preference changes
            try {
                const mql = window.matchMedia('(prefers-color-scheme: dark)');
                this.systemIsDark = mql.matches;
                const updateSystem = (e) => {
                    this.systemIsDark = e.matches;
                    if (this.themeMode === 'system') {
                        this.applyTheme(false);
                    }
                };
                if (mql.addEventListener) {
                    mql.addEventListener('change', updateSystem);
                } else if (mql.addListener) {
                    mql.addListener(updateSystem);
                }
            } catch (e) {}

            // Watch tab switch and font size changes to re-evaluate scroll overflow
            this.$watch('activeTab', () => {
                this.$nextTick(() => {
                    if (this.$refs.scrollBody) {
                        this.$refs.scrollBody.scrollTop = 0;
                        this.checkScroll(this.$refs.scrollBody);
                    }
                });
            });
            this.$watch('fontSize', () => {
                this.$nextTick(() => {
                    this.checkScroll(this.$refs.scrollBody);
                });
            });

            // Sync initially without triggering noisy events
            this.applyTheme(false);
            this.applyFontSize(false);
        },

        openModal(event) {
            this.triggerElement = event?.target || document.activeElement;
            this.isOpen = true;
            this.mfaSetupStep = 'idle';
            this.confirmingDeactivateMfa = false;
            this.confirmingDisableTotp = false;
            document.body.classList.add('overflow-y-hidden');

            this.$nextTick(() => {
                this.checkScroll(this.$refs.scrollBody);
                const focusable = this.getFocusables()[0];
                if (focusable) focusable.focus();
            });
        },

        closeModal() {
            this.isOpen = false;
            document.body.classList.remove('overflow-y-hidden');
            if (this.triggerElement && typeof this.triggerElement.focus === 'function') {
                this.triggerElement.focus();
            }
        },

        checkScroll(el) {
            const target = el || this.$refs.scrollBody;
            if (!target) return;
            this.canScrollDown = (target.scrollHeight - target.scrollTop - target.clientHeight) > 8;
        },

        getFocusables() {
            const selector = 'a[href], button:not([disabled]):not([tabindex="-1"]), input:not([disabled]):not([type="hidden"]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
            return [...this.$el.querySelectorAll(selector)].filter(el => !el.hasAttribute('disabled') && el.offsetParent !== null && !el.classList.contains('pointer-events-none'));
        },

        handleTabKey(e) {
            const focusables = this.getFocusables();
            if (focusables.length === 0) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (e.shiftKey) {
                if (document.activeElement === first) {
                    last.focus();
                    e.preventDefault();
                }
            } else {
                if (document.activeElement === last) {
                    first.focus();
                    e.preventDefault();
                }
            }
        },

        setMobileTab(tab) {
            this.activeTab = tab;
            this.$nextTick(() => {
                const el = document.getElementById('mobile-tab-' + tab);
                if (el) el.focus();
            });
        },

        focusPrevTab() {
            this.activeTab = (this.activeTab === 'security') ? 'general' : 'security';
            this.$nextTick(() => {
                const el = document.getElementById('tab-' + this.activeTab);
                if (el) el.focus();
            });
        },

        focusNextTab() {
            this.activeTab = (this.activeTab === 'general') ? 'security' : 'general';
            this.$nextTick(() => {
                const el = document.getElementById('tab-' + this.activeTab);
                if (el) el.focus();
            });
        },

        handleThemeKeydown(e, currentMode) {
            if (['ArrowRight', 'ArrowDown'].includes(e.key)) {
                e.preventDefault();
                const idx = this.themeOptions.indexOf(currentMode);
                const next = this.themeOptions[(idx + 1) % this.themeOptions.length];
                this.setThemeMode(next);
                this.$nextTick(() => {
                    const el = this.$el.querySelector(`[data-theme-value="${next}"]`);
                    if (el) el.focus();
                });
            } else if (['ArrowLeft', 'ArrowUp'].includes(e.key)) {
                e.preventDefault();
                const idx = this.themeOptions.indexOf(currentMode);
                const prev = this.themeOptions[(idx - 1 + this.themeOptions.length) % this.themeOptions.length];
                this.setThemeMode(prev);
                this.$nextTick(() => {
                    const el = this.$el.querySelector(`[data-theme-value="${prev}"]`);
                    if (el) el.focus();
                });
            } else if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                this.setThemeMode(currentMode);
            }
        },

        handleFontSizeKeydown(e, currentSize) {
            if (['ArrowRight', 'ArrowDown'].includes(e.key)) {
                e.preventDefault();
                const idx = this.fontSizeOptions.indexOf(currentSize);
                const next = this.fontSizeOptions[(idx + 1) % this.fontSizeOptions.length];
                this.setFontSize(next);
                this.$nextTick(() => {
                    const el = this.$el.querySelector(`[data-size-value="${next}"]`);
                    if (el) el.focus();
                });
            } else if (['ArrowLeft', 'ArrowUp'].includes(e.key)) {
                e.preventDefault();
                const idx = this.fontSizeOptions.indexOf(currentSize);
                const prev = this.fontSizeOptions[(idx - 1 + this.fontSizeOptions.length) % this.fontSizeOptions.length];
                this.setFontSize(prev);
                this.$nextTick(() => {
                    const el = this.$el.querySelector(`[data-size-value="${prev}"]`);
                    if (el) el.focus();
                });
            } else if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                this.setFontSize(currentSize);
            }
        },

        setThemeMode(mode) {
            this.themeMode = mode;
            localStorage.setItem('themeMode', mode);
            this.applyTheme(true);
            this.triggerSavedFeedback();
        },

        applyTheme(notify = true) {
            const isDark = this.themeMode === 'dark' || (this.themeMode === 'system' && this.systemIsDark);
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
            localStorage.setItem('darkMode', isDark ? 'true' : 'false');

            if (notify) {
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { themeMode: this.themeMode, isDark } }));
                window.dispatchEvent(new CustomEvent('dark-mode-toggled'));
            }
        },

        setFontSize(size) {
            this.fontSize = size;
            localStorage.setItem('fontSize', size);
            this.applyFontSize(true);
            this.triggerSavedFeedback();
        },

        applyFontSize(notify = true) {
            document.documentElement.classList.remove('font-size-sm', 'font-size-md', 'font-size-lg', 'font-size-xl');
            document.documentElement.classList.add('font-size-' + this.fontSize);

            if (notify) {
                window.dispatchEvent(new CustomEvent('font-size-changed', { detail: this.fontSize }));
            }
        },

        triggerSavedFeedback() {
            this.showSaveIndicator = true;
            clearTimeout(this.saveIndicatorTimer);
            this.saveIndicatorTimer = setTimeout(() => {
                this.showSaveIndicator = false;
            }, 2000);
        },

        resetToDefaults() {
            this.previousSettings = {
                themeMode: this.themeMode,
                fontSize: this.fontSize
            };

            this.setThemeMode('system');
            this.setFontSize('md');

            this.showUndoBanner = true;
            clearTimeout(this.undoTimer);
            this.undoTimer = setTimeout(() => {
                this.showUndoBanner = false;
            }, 7000);

            window.showToast?.('Display preferences reset to default.', 'info');
        },

        undoReset() {
            if (this.previousSettings) {
                this.setThemeMode(this.previousSettings.themeMode);
                this.setFontSize(this.previousSettings.fontSize);
                this.showUndoBanner = false;
                window.showToast?.('Display preferences restored.', 'success');
            }
        },

        copySecret() {
            if (!this.totpSecret) return;
            navigator.clipboard.writeText(this.totpSecret).then(() => {
                this.copiedSecret = true;
                setTimeout(() => { this.copiedSecret = false; }, 2000);
            }).catch(() => {
                // Fallback
                const ta = document.createElement('textarea');
                ta.value = this.totpSecret;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                this.copiedSecret = true;
                setTimeout(() => { this.copiedSecret = false; }, 2000);
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
                    'X-CSRF-TOKEN': this.csrfToken
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
                    window.showToast?.(res.body.message || 'Invalid verification code.', 'error');
                }
            })
            .catch(() => { this.submitting = false; });
        },

        executeDisableTotp() {
            this.confirmingDisableTotp = false;
            fetch('{{ route('mfa.disable-totp-ajax') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
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
                    'X-CSRF-TOKEN': this.csrfToken
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
                    'X-CSRF-TOKEN': this.csrfToken
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
                    window.showToast?.(res.body.message || 'Invalid code.', 'error');
                }
            })
            .catch(() => { this.submitting = false; });
        },

        executeDisableMfa() {
            this.confirmingDeactivateMfa = false;
            fetch('{{ route('mfa.disable-mfa-ajax') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
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
        },

        handlePaste(e, type) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            if (!pasted) return;

            const digits = type === 'totp' ? this.totpDigits : this.emailDigits;
            for (let i = 0; i < 6; i++) {
                digits[i] = pasted[i] || '';
            }

            const lastIndex = Math.min(pasted.length - 1, 5);
            const targetEl = this.$refs[type + 'Input' + lastIndex];
            if (targetEl) targetEl.focus();
        }
    };
}
</script>
