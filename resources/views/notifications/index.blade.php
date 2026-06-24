@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
@php
    use App\Enums\UserRole;

    $user = Auth::user();
    $role = $user->role_id; // Casts to UserRole enum

    $portalType = 'User';
    $userName = $user->name;
    $userRole = 'Member';

    if ($role === UserRole::INTERN) {
        $portalType = 'Intern';
        $userName = $user->internDetails->first_name ?? $user->name;
        $userRole = $user->internDetails->office->name ?? 'Intern';
    } elseif ($role === UserRole::SUPERVISOR) {
        $portalType = 'Supervisor';
        $userName = $user->supervisorDetails->first_name ?? $user->name;
        $userRole = $user->supervisorDetails->office->name ?? 'Supervisor';
    } elseif ($role === UserRole::HEAD_SUPERVISOR) {
        $portalType = 'Head Supervisor';
        $userName = $user->name;
        $userRole = 'Head Supervisor';
    } elseif ($role === UserRole::SCHOOL_COORDINATOR) {
        $portalType = 'Coordinator';
        $userName = $user->name;
        $userRole = 'Coordinator';
    } elseif ($role === UserRole::ADMIN || $role === UserRole::SUPER_ADMIN) {
        $portalType = 'Admin';
        $userName = $user->name;
        $userRole = 'System Admin';
    }

    $hasUnread = $notificationsList->contains('is_read', false);
@endphp

<x-page-layout 
    :portalType="$portalType" 
    :userName="$userName" 
    :userRole="$userRole"
>
    <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 800)" class="flex flex-col flex-1">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6 mb-12 shrink-0">
            <x-page-header 
                title="Notifications" 
                subtitle="Manage and track all alerts and activity updates" 
            />

            @if($hasUnread)
                <form action="{{ route('notifications.markAllRead') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 border border-blue-100 dark:border-blue-800/50 hover:bg-blue-100 dark:hover:bg-blue-900/40 px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition-all cursor-pointer">
                        Mark All As Read
                    </button>
                </form>
            @endif
        </div>

        {{-- SKELETON LOADING VIEW --}}
        <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="space-y-6">
            @for($i = 0; $i < 5; $i++)
                <x-card-container padding="p-8">
                    <div class="flex gap-6 items-start">
                        <x-skeleton type="text" class="w-12 h-12 rounded-2xl shrink-0" />
                        <div class="flex-1">
                            <x-skeleton type="text" class="w-1/4 mb-3" />
                            <x-skeleton type="text" class="w-3/4" />
                        </div>
                    </div>
                </x-card-container>
            @endfor
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col flex-1">
            <div class="space-y-6">
                @forelse($notificationsList as $notif)
                    @php
                        $notifId = $notif->getRouteKey();
                        $category = $notif->category;
                        $isRead = $notif->is_read;

                        $cardBgClass = $isRead 
                            ? 'bg-white/60 dark:bg-gray-900/40 border-gray-100 dark:border-gray-800 opacity-75' 
                            : 'bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md hover:border-[#0B5694]/20 dark:hover:border-blue-900/30';

                        $iconClass = $isRead 
                            ? 'bg-gray-50 dark:bg-gray-850/50 border-gray-100 dark:border-gray-800 text-gray-400 dark:text-gray-500' 
                            : match($category) {
                                'task' => 'bg-blue-50 dark:bg-blue-900/20 border-transparent text-[#0B5694] dark:text-blue-400',
                                'announcement' => 'bg-emerald-50 dark:bg-emerald-900/20 border-transparent text-emerald-600 dark:text-emerald-400',
                                'system' => 'bg-rose-50 dark:bg-rose-900/20 border-transparent text-rose-600 dark:text-rose-400',
                                default => 'bg-gray-50 dark:bg-gray-850 border-transparent text-gray-500 dark:text-gray-400'
                            };

                        $categoryLabel = match($category) {
                            'task' => 'Task Assignment',
                            'announcement' => 'Office Announcement',
                            'system' => 'System Alert',
                            default => 'General Notification'
                        };
                    @endphp

                    <div class="flex items-stretch gap-2.5 sm:gap-4 group">
                        <div @click="handleNotificationClick('{{ $notifId }}', '{{ csrf_token() }}')" 
                             class="flex-1 flex gap-3 xs:gap-5 items-start p-4 xs:p-6 md:p-8 border rounded-[20px] xs:rounded-[30px] transition-all duration-300 cursor-pointer select-none active:scale-[0.99] {{ $cardBgClass }}">
                            
                            {{-- Category Icon --}}
                            <div class="p-2.5 xs:p-3.5 rounded-xl xs:rounded-2xl border shrink-0 {{ $iconClass }}">
                                @if($category === 'task')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                @elseif($category === 'announcement')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                                @else
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                @endif
                            </div>
 
                            {{-- Text Content --}}
                            <div class="flex-1 min-w-0 text-left">
                                <div class="flex flex-wrap justify-between items-center gap-2 mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-550">{{ $categoryLabel }}</span>
                                        @if(!$isRead)
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]"></span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-550 font-bold uppercase">{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm md:text-base font-bold text-gray-700 dark:text-gray-200 leading-normal group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                                    {{ $notif->message }}
                                </p>
                            </div>
                        </div>
 
                        {{-- Mark as Read Button (for individual unread items) --}}
                        @if(!$isRead)
                            <form action="{{ route('notifications.read', $notif) }}" method="POST" class="flex shrink-0">
                                @csrf
                                <button type="submit" 
                                        class="h-full px-3.5 sm:px-5 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[16px] sm:rounded-[24px] hover:bg-emerald-50 hover:border-emerald-100 hover:text-emerald-500 dark:hover:bg-emerald-950/20 dark:hover:border-emerald-900/50 dark:hover:text-emerald-400 text-gray-400 transition-all cursor-pointer flex items-center justify-center" 
                                        title="Mark as read">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="flex-1 flex flex-col items-center justify-center min-h-[40vh]">
                        <x-empty-state 
                            title="No Notifications" 
                            subtitle="You're all caught up. Any system updates or assignments will show up here."
                            compact="false"
                        />
                    </div>
                @endforelse
            </div>

            @if($notificationsList->hasPages())
                <div class="mt-12 flex justify-end">
                    {{ $notificationsList->links('vendor.pagination.tailwind-custom') }}
                </div>
            @endif
        </div>
    </div>
</x-page-layout>
@endsection
