@php
    $user = Auth::user();
    $isDeptSupervisor = $user->supervisorDetails && $user->supervisorDetails->office_id;

    $deptActive = Route::is('supervisor.tasks*') || Route::is('supervisor.announcements*') || Route::is('supervisor.attendance*') || Route::is('supervisor.certificates.*') || Route::is('supervisor.interns*');
    $divisionActive = Route::is('head-supervisor.schedule.*') || Route::is('head-supervisor.matching*') || Route::is('head-supervisor.onboarding.*') || Route::is('head-supervisor.offices.*') || Route::is('head-supervisor.interns*') || Route::is('head-supervisor.my-interns*') || Route::is('head-supervisor.analytics*') || Route::is('head-supervisor.report*') || Route::is('head-supervisor.certificates.*');
@endphp

<div x-data="{ 
    openDept: @js($deptActive || true), 
    openDivision: @js($divisionActive || true) 
}" class="space-y-1">
    {{-- 1. Dashboard --}}
    <a href="{{ route('head-supervisor.dashboard') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('head-supervisor.dashboard') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
        Dashboard
    </a>

    {{-- Supervisor Functions (If assigned to an office) --}}
    @if($isDeptSupervisor)
    <div>
        <div @click="openDept = !openDept" class="flex items-center justify-between pt-4 pb-2 px-4 cursor-pointer group">
            <p class="text-[10px] font-bold uppercase tracking-wider text-blue-200/50 dark:text-gray-500 group-hover:text-white transition-colors">My Department</p>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-blue-200/50 transition-transform duration-200" :class="openDept ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
        </div>

        <div x-show="openDept" x-collapse x-cloak class="space-y-1">
            <a href="{{ route('supervisor.interns') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('supervisor.interns*') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Intern Directory
            </a>

            <a href="{{ route('supervisor.tasks') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('supervisor.tasks*') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="16" x="4" y="4" rx="2"/><path d="m9 12 2 2 4-4"/><path d="M12 4v16"/><path d="M4 12h16"/></svg>
                Task Monitoring
            </a>

            <a href="{{ route('supervisor.announcements') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('supervisor.announcements*') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Announcements
            </a>

            <a href="{{ route('supervisor.attendance') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('supervisor.attendance*') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Attendance Logs
            </a>

            <a href="{{ route('supervisor.certificates.index') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('supervisor.certificates.*') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><circle cx="12" cy="8" r="4"/></svg>
                Endorsements
            </a>
        </div>
    </div>
    @endif

    <div>
        <div @click="openDivision = !openDivision" class="flex items-center justify-between pt-4 pb-2 px-4 cursor-pointer group">
            <p class="text-[10px] font-bold uppercase tracking-wider text-blue-200/50 dark:text-gray-500 group-hover:text-white transition-colors">Division Tools</p>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-blue-200/50 transition-transform duration-200" :class="openDivision ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
        </div>

        <div x-show="openDivision" x-collapse x-cloak class="space-y-1">
            {{-- 2. Schedule --}}
            <a href="{{ route('head-supervisor.schedule.index') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('head-supervisor.schedule.*') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                Schedule
            </a>

            {{-- 3. Office Matching --}}
            <a href="{{ route('head-supervisor.matching') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('head-supervisor.matching*') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><circle cx="10" cy="13" r="2"/><path d="m20 20-1.5-1.5"/></svg>
                Office Matching
            </a>

            {{-- 4. Onboarding --}}
            <a href="{{ route('head-supervisor.onboarding.index') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('head-supervisor.onboarding.*') || Route::is('head-supervisor.offices.*') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
                Onboarding
            </a>

            {{-- 5. My Interns --}}
            @if($isDeptSupervisor)
            <a href="{{ route('head-supervisor.my-interns') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('head-supervisor.my-interns*') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                My Interns
            </a>
            @endif

            {{-- 6. Interns --}}
            <a href="{{ route('head-supervisor.interns') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('head-supervisor.interns*') && !Route::is('head-supervisor.my-interns*') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Interns (All)
            </a>

            {{-- 6. Analytics & Reports --}}
            <a href="{{ route('head-supervisor.analytics') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('head-supervisor.analytics*') || Route::is('head-supervisor.report*') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
                Analytics & Reports
            </a>

            {{-- 8. Certificates --}}
            <a href="{{ route('head-supervisor.certificates.index') }}" class="flex items-center gap-3 px-4 py-3 {{ Route::is('head-supervisor.certificates.index') ? 'bg-white/20' : 'hover:bg-white/10' }} rounded-lg text-sm font-medium transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                Certificates
            </a>
        </div>
    </div>
</div>
