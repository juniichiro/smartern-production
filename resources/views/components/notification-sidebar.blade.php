@props(['notifications' => []])

@php
    // Group notifications by human-readable date
    $grouped = collect($notifications)->groupBy(function($notif) {
        if ($notif->created_at->isToday()) return 'Today';
        if ($notif->created_at->isYesterday()) return 'Yesterday';
        return $notif->created_at->format('F d, Y');
    });
@endphp

<div x-data="notificationManager({{ Auth::id() }}, {{ collect($notifications)->where('is_read', false)->count() }})"
     x-init="
        $watch('desktopNotifCollapsed', value => {
            localStorage.setItem('notifCollapsed', value);
            document.body.classList.toggle('notif-collapsed', value);
        });
        document.body.classList.toggle('notif-collapsed', desktopNotifCollapsed);
     "
     @close-notifications.window="notifOpen = false"
     @toggle-notifications.window="notifOpen = !notifOpen; if(notifOpen) $dispatch('close-sidebar')">
    
    {{-- Notif Toggle (Desktop only) --}}
    <button @click="desktopNotifCollapsed = !desktopNotifCollapsed" 
            :class="{
                'bg-transparent border-none shadow-none text-[#0B5694] dark:text-blue-400': desktopNotifCollapsed,
                'bg-white border border-gray-100 shadow-lg text-[#0B5694] dark:bg-gray-900 dark:border-gray-800 dark:text-blue-400': !desktopNotifCollapsed
            }"
            class="hidden lg:flex fixed top-[2.5rem] right-6 z-[90] p-3 rounded-xl transition-all active:scale-95 group"
            title="Toggle Notifications">
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" 
                 :fill="desktopNotifCollapsed ? 'currentColor' : 'none'" 
                 stroke="currentColor" stroke-width="2.5" 
                 class="transition-colors group-hover:scale-110">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
            </svg>
            <template x-if="unreadCount > 0">
                <span class="absolute -top-1.5 -right-1.5 bg-rose-600 text-white text-[10px] min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full font-bold border-2 border-white dark:border-gray-900 shadow-[0_0_12px_rgba(225,29,72,0.5)] z-10"
                      x-text="unreadCount">
                </span>
            </template>
        </div>
    </button>

    {{-- Notif Overlay for Mobile --}}
    <div x-show="notifOpen" 
         @click="notifOpen = false" 
         class="fixed inset-0 bg-black/50 z-[95] lg:hidden backdrop-blur-sm transition-opacity"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <aside :class="{
                'translate-x-0': notifOpen,
                'translate-x-full': !notifOpen,
                'lg:translate-x-0': !desktopNotifCollapsed,
                'lg:translate-x-full': desktopNotifCollapsed
           }" 
           class="w-80 bg-white dark:bg-gray-900 border-l border-gray-100 dark:border-gray-800 p-8 fixed right-0 h-full text-left overflow-y-auto z-[100] transition-all duration-300 ease-in-out">
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-12 mt-4">
            <div>
                <h4 class="font-bold text-gray-400 dark:text-gray-300 text-xs uppercase tracking-wider">Notifications</h4>
                <template x-if="unreadCount > 0">
                    <button @click="clearNotifications()" class="text-[12px] font-bold text-blue-500 dark:text-blue-400 uppercase hover:underline mt-1">
                        Mark all as read
                    </button>
                </template>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                    </svg>
                    <template x-if="unreadCount > 0">
                        <span class="absolute -top-1.5 -right-1.5 bg-rose-600 text-white text-[10px] min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full font-bold border-2 border-white dark:border-gray-900 shadow-[0_0_12px_rgba(225,29,72,0.5)]"

                              x-text="unreadCount">
                        </span>
                    </template>
                </div>

                {{-- Collapse Toggle (Desktop - internal) --}}
                <button @click="desktopNotifCollapsed = true" 
                        class="hidden lg:flex p-1.5 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors group"
                        title="Hide Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover:translate-x-1 transition-transform">
                        <polyline points="13 17 18 12 13 7"></polyline><polyline points="6 17 11 12 6 7"></polyline>
                    </svg>
                </button>
            </div>
        </div>

    {{-- Notification List --}}
    <div class="space-y-12">
        @forelse($grouped as $date => $items)
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <span class="text-[10px] font-bold text-gray-300 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">{{ $date }}</span>

                    <div class="h-[1px] w-full bg-gray-50 dark:bg-gray-800/50"></div>
                </div>

                @foreach($items as $notif)
                    @php
                        $styles = match($notif->category) {
                            'task' => [
                                'bg' => 'bg-blue-50 dark:bg-blue-900/20', 
                                'text' => 'text-[#0B5694] dark:text-blue-400', 
                                'label' => 'Task Assignment'
                            ],
                            'announcement' => [
                                'bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 
                                'text' => 'text-[#059669] dark:text-emerald-400', 
                                'label' => 'Office Announcement'
                            ],
                            'system' => [
                                'bg' => 'bg-rose-50 dark:bg-rose-900/20', 
                                'text' => 'text-[#e11d48] dark:text-rose-400', 
                                'label' => 'System Alert'
                            ],
                            default => [
                                'bg' => 'bg-gray-50 dark:bg-gray-800/30', 
                                'text' => 'text-gray-500 dark:text-gray-300', 
                                'label' => 'Update'
                            ]
                        };
                    @endphp

                    <div onclick="handleNotificationClick('{{ $notif->getRouteKey() }}', '{{ csrf_token() }}')" 
                         class="flex gap-4 items-start group relative cursor-pointer transition-all active:scale-95">
                        
                        <template x-if="!allRead && !{{ $notif->is_read ? 'true' : 'false' }}">
                            <div class="absolute -left-3 top-5 w-1.5 h-1.5 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]"></div>
                        </template>

                        <div class="p-3 rounded-2xl shrink-0 transition-all border shadow-sm {{ $styles['text'] }}"
                            :class="(allRead || {{ $notif->is_read ? 'true' : 'false' }}) ? 'bg-gray-50 dark:bg-gray-800/20 border-gray-100 dark:border-gray-800 opacity-60' : '{{ $styles['bg'] }} border-transparent dark:border-gray-800/50 group-hover:shadow-md'">
                            
                            @if($notif->category == 'task')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            @elseif($notif->category == 'announcement')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                            @else
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1 gap-2">
                                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider truncate">{{ $styles['label'] }}</p>
                                <span class="text-[10px] text-gray-400 dark:text-gray-400 font-bold uppercase whitespace-nowrap">{{ $notif->created_at->diffForHumans(null, true, true) }}</span>
                            </div>
                            <p class="text-[14px] font-bold text-gray-800 dark:text-gray-100 leading-tight group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                                {{ $notif->message }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-center py-20 border-2 border-dashed border-gray-50 dark:border-gray-800 rounded-[35px]">
                <p class="text-gray-400 dark:text-gray-400 italic font-bold uppercase text-[10px] tracking-wider">No Recent Activity</p>
            </div>
        @endforelse
    </div>

    <a href="{{ route('notifications.index') }}" class="mt-16 block text-xs font-bold text-gray-400 dark:text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 transition-all w-full text-center py-4 border border-dashed border-gray-200 dark:border-gray-800 rounded-2xl uppercase tracking-wider">
        View All History
    </a>
</aside>
</div>