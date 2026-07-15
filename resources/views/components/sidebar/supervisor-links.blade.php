@php
    $current = Route::currentRouteName();
@endphp

    <a href="{{ route('supervisor.dashboard') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'supervisor.dashboard' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
        Dashboard
    </a>

    <a href="{{ route('supervisor.schedule.index') }}" class="flex items-center gap-3 px-4 py-3 {{ str_contains($current, 'supervisor.schedule') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
        Schedule
    </a>

    <a href="{{ route('supervisor.interns') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'supervisor.interns' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Office Management
    </a>

    <a href="{{ route('supervisor.office-matching') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'supervisor.office-matching' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="16" x2="22" y1="11" y2="11"/></svg>
        Intern Placement
    </a>

    <a href="{{ route('supervisor.tasks') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'supervisor.tasks' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="16" x="4" y="4" rx="2"/><path d="m9 12 2 2 4-4"/><path d="M12 4v16"/><path d="M4 12h16"/></svg>
        Task Monitoring
    </a>

    <a href="{{ route('supervisor.announcements') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'supervisor.announcements' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        Announcements
    </a>

    <a href="{{ route('supervisor.attendance') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'supervisor.attendance' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Attendance logs
    </a>

    {{-- NEW: Endorsements Link for Certificate Management --}}
    <a href="{{ route('supervisor.certificates.index') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'supervisor.certificates.index' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><circle cx="12" cy="8" r="4"/></svg>
        Endorsements
    </a>

    <a href="{{ route('supervisor.reports') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'supervisor.reports' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 20-4-4h-3V4h9v16Z"/><path d="M3 20v-7a4 4 0 0 1 4-4h3"/><path d="m7 16-4-4 4-4"/><path d="M15 7h2"/></svg>
        Reports
    </a>