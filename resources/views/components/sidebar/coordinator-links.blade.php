@php
    $current = Route::currentRouteName();
@endphp

<a href="{{ route(name: 'coordinator.dashboard') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'coordinator.dashboard' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
    Dashboard
</a>

<a href="{{ route(name: 'coordinator.interns') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'coordinator.interns' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
    Intern monitoring
</a>

<a href="{{ route(name: 'coordinator.schedule.index') }}" class="flex items-center gap-3 px-4 py-3 {{ str_starts_with($current, 'coordinator.schedule') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    Training Schedule
</a>

<a href="{{ route(name: 'coordinator.certificates') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'coordinator.certificates' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
    Certificates
</a>