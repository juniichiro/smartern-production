@php
    $current = Route::currentRouteName();
@endphp

<a href="{{ route('intern.dashboard') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'intern.dashboard' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
            Dashboard
        </a>

        <a href="{{ route('intern.schedule.index') }}" class="flex items-center gap-3 px-4 py-3 {{ str_contains($current, 'intern.schedule') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
            Schedule
        </a>

        <a href="{{ route('intern.announcements') }}" class="flex items-center gap-3 px-4 py-3 {{ str_starts_with($current, 'intern.announcements') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            Announcements
        </a>

        <a href="{{ route('intern.attendance') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'intern.attendance' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3"/><path d="M7 12h3"/></svg>
            Attendance
        </a>

        <a href="{{ route('intern.progress') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'intern.progress' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
            Progress
        </a>

        <a href="{{ route('intern.certificates.index') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'intern.certificates.index' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
            Certificate
        </a>

        <a href="{{ route('intern.profile') }}" class="flex items-center gap-3 px-4 py-3 {{ $current === 'intern.profile' ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            Internship Info
        </a>