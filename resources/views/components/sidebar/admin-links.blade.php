@php
    $current = Route::currentRouteName();
@endphp

<a href="{{ route('admin.dashboard') }}" 
   class="flex items-center gap-3 px-4 min-h-[44px] {{ $current === 'admin.dashboard' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
    Dashboard
</a>

<a href="{{ route('admin.account') }}" 
   class="flex items-center gap-3 px-4 min-h-[44px] {{ $current === 'admin.account' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Account Management
</a>

<a href="{{ route('admin.security') }}" 
   class="flex items-center gap-3 px-4 min-h-[44px] {{ $current === 'admin.security' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
    Security Monitor
</a>

<a href="{{ route('admin.offices') }}" 
   class="flex items-center gap-3 px-4 min-h-[44px] {{ $current === 'admin.offices' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
    Office Geofencing
</a>

<a href="{{ route('admin.configuration') }}" class="flex items-center gap-3 px-4 min-h-[44px] {{ $current === 'admin.configuration' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
    System Configuration
</a>
