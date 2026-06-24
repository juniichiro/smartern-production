@extends('layouts.app')

@section('title', 'Office Reports')

@section('content')
<x-page-layout 
    portalType="Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div class="flex flex-col h-full">
        {{-- Header Section --}}
        <x-page-header
            title="Office"
            highlight="Reports"
            subtitle="Data-driven performance insights for {{ Auth::user()->supervisorDetails->office->name }}"
        >
            <x-slot name="actions">
                <a href="{{ route('supervisor.reports.pdf') }}" 
                    class="flex items-center gap-3 bg-[#0B5694] dark:bg-blue-600 px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl text-xs font-black text-white shadow-xl shadow-blue-500/20 hover:bg-[#084577] dark:hover:bg-blue-700 transition uppercase tracking-widest w-fit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                    </svg>
                    Export PDF
                </a>
            </x-slot>
        </x-page-header>

        {{-- 1. TOP SECTION: Summary Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 mb-10">
            <div class="bg-white dark:bg-gray-900 p-5 md:p-8 rounded-[35px] border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group hover:border-blue-200 dark:hover:border-blue-800 transition-all">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50/50 dark:bg-blue-900/10 rounded-bl-full -z-0"></div>
                <div class="relative z-10">
                    <p class="text-[12px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Total Interns</p>
                    <h3 class="text-2xl sm:text-3xl md:text-5xl font-black text-gray-900 dark:text-gray-100 tracking-tighter">{{ $activeInternsCount }}</h3>
                    <p class="text-[12px] text-blue-500 dark:text-blue-400 mt-3 font-black uppercase tracking-widest">Assigned to Office</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-900 p-5 md:p-8 rounded-[35px] border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group hover:border-emerald-200 dark:hover:border-emerald-800 transition-all">
                <div class="relative z-10">
                    <p class="text-[12px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Office Success Rate</p>
                    <h3 class="text-2xl sm:text-3xl md:text-5xl font-black text-emerald-500 dark:text-emerald-400 tracking-tighter">{{ $completionPercentage }}%</h3>
                    <p class="text-[12px] text-emerald-600 dark:text-emerald-400 mt-3 font-black uppercase tracking-widest">Tasks Verified</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-900 p-5 md:p-8 rounded-[35px] border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group hover:border-orange-200 dark:hover:border-orange-800 transition-all">
                <div class="relative z-10">
                    <p class="text-[12px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Pending Tasks</p>
                    <h3 class="text-2xl sm:text-3xl md:text-5xl font-black text-orange-400 tracking-tighter">{{ $pendingTasksCount }}</h3>
                    <p class="text-[12px] text-orange-500 dark:text-orange-400 mt-3 font-black uppercase tracking-widest">Awaiting Action</p>
                </div>
            </div>
        </div>

        {{-- 2. MIDDLE SECTION: Visual Analytics (Graphs) --}}
        <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-5 gap-8 mb-10">
            {{-- Doughnut Chart --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 p-4 sm:p-6 md:p-10 rounded-[25px] md:rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 flex flex-col items-center">
                <h4 class="text-[12px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-8 w-full text-left">Task Distribution</h4>
                <div class="w-full h-48 sm:h-56 md:h-64 relative">
                    <canvas id="taskStatusChart"></canvas>
                </div>
                <div class="mt-8 flex flex-wrap justify-center gap-4 md:gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#0B5694] dark:bg-blue-600"></div>
                        <span class="text-[12px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Verified</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#FB923C]"></div>
                        <span class="text-[12px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Pending</span>
                    </div>
                </div>
            </div>

            {{-- Bar Chart --}}
            <div class="lg:col-span-3 bg-white dark:bg-gray-900 p-4 sm:p-6 md:p-10 rounded-[25px] md:rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800">
                <h4 class="text-[12px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-8">Performance Leaderboard</h4>
                <div class="w-full h-56 sm:h-64 md:h-72">
                    <canvas id="performanceBarChart"></canvas>
                </div>
            </div>
        </div>

        {{-- 2.5 TRENDS SECTION --}}
        <div class="bg-white dark:bg-gray-900 p-4 sm:p-6 md:p-10 rounded-[25px] md:rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 mb-10">
            <h4 class="text-[12px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-8">Office Performance & Attendance Trends</h4>
            <div class="w-full h-60 sm:h-72 md:h-80">
                <canvas id="officeTrendsChart"></canvas>
            </div>
        </div>

        {{-- 3. BOTTOM SECTION: Detailed Table Section --}}
        <div class="bg-white dark:bg-gray-900 rounded-[25px] md:rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-10 overflow-hidden">
            <div class="mb-6 md:mb-10 flex flex-col sm:flex-row sm:justify-between sm:items-end gap-3 md:gap-4">
                <div>
                    <h3 class="text-lg md:text-xl font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">Metric Registry</h3>
                    <p class="text-xs md:text-sm text-gray-400 dark:text-gray-500 mt-1 font-medium italic">Individual performance tracking for current term</p>
                </div>
            </div>
            
            <div class="border border-gray-100 dark:border-gray-800 rounded-3xl overflow-hidden shadow-inner">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-[12px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-4 md:px-8 py-3 md:py-6">Intern Details</th>
                                <th class="px-6 py-6 text-center">Tasks Done</th>
                                <th class="px-6 py-6 text-center">Efficiency</th>
                                <th class="px-10 py-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-50 dark:divide-gray-800 font-medium">
                            @foreach($internPerformance as $intern)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors group">
                                <td class="px-4 md:px-8 py-4 md:py-6 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <x-avatar :user="$intern" size="10" border="2" />
                                        <div>
                                            <p class="font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight leading-none group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                                                {{ $intern->first_name }} {{ $intern->last_name }}
                                            </p>
                                            <p class="text-[12px] text-gray-400 dark:text-gray-500 font-black mt-2 uppercase tracking-widest">ID: {{ $intern->student_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-[16px] px-6 py-6 text-center font-black text-gray-600 dark:text-gray-400 tracking-tighter whitespace-nowrap">
                                    {{ $intern->completed_tasks }} <span class="text-gray-300 dark:text-gray-700">/</span> {{ $intern->total_tasks }}
                                </td>
                                <td class="px-6 py-6 whitespace-nowrap">
                                    <div class="text-[16px] flex flex-col items-center">
                                        <span class="font-black text-gray-800 dark:text-gray-100 tracking-tighter">{{ round($intern->performance_rate) }}%</span>
                                        <div class="w-24 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full mt-2 overflow-hidden shadow-inner">
                                            <div class="bg-[#0B5694] dark:bg-blue-600 h-full rounded-full" style="width: {{ $intern->performance_rate }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 md:px-8 py-4 md:py-6 text-right whitespace-nowrap">
                                    <a href="{{ route('supervisor.interns.show', $intern->user) }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 sm:px-6 py-2 sm:py-2.5 rounded-xl text-[10px] sm:text-[11px] font-black text-[#0B5694] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 transition shadow-sm uppercase tracking-widest">View Profile</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        waitForChartAndInit();
    });

    window.addEventListener('dark-mode-toggled', () => {
        if (window.Chart) initReportCharts();
    });

    function waitForChartAndInit(retries = 0) {
        if (window.Chart) {
            initReportCharts();
        } else if (retries < 10) {
            setTimeout(() => waitForChartAndInit(retries + 1), 200);
        }
    }

    function initReportCharts() {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#ffffff' : '#1f2937';
        const secondaryTextColor = isDark ? '#d1d5db' : '#4b5563';
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';

        // Set global defaults
        Chart.defaults.color = textColor;
        Chart.defaults.font.family = "'Outfit', sans-serif";

        // Destroy existing instances if any
        if (window.taskStatusChart instanceof Chart) window.taskStatusChart.destroy();
        if (window.performanceBarChart instanceof Chart) window.performanceBarChart.destroy();
        if (window.officeTrendsChart instanceof Chart) window.officeTrendsChart.destroy();

        // Task Status Doughnut
        const ctxStatus = document.getElementById('taskStatusChart').getContext('2d');
        window.taskStatusChart = new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Verified', 'Pending'],
                datasets: [{
                    data: [{{ $completedTasksCount }}, {{ $pendingTasksCount }}],
                    backgroundColor: ['#0B5694', '#FB923C'],
                    borderWidth: 2,
                    borderColor: isDark ? '#111827' : '#ffffff'
                }]
            },
            options: {
                plugins: { 
                    legend: { display: false }
                },
                maintainAspectRatio: false,
                cutout: '80%',
                animation: { animateScale: true, duration: 2000 }
            }
        });

        // Performance Bar Chart
        const ctxBar = document.getElementById('performanceBarChart').getContext('2d');
        window.performanceBarChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: @js($internPerformance->pluck('last_name')),
                datasets: [{
                    label: 'Tasks Completed',
                    data: @js($internPerformance->pluck('completed_tasks')),
                    backgroundColor: '#0B5694',
                    borderRadius: 12,
                    barThickness: 30,
                    hoverBackgroundColor: '#084577'
                }]
            },
            options: {
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#1f2937' : '#ffffff',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: isDark ? '#374151' : '#e5e7eb',
                        borderWidth: 1
                    }
                },
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: gridColor }, 
                        ticks: { font: { size: 9, weight: '800' }, color: secondaryTextColor } 
                    },
                    x: { 
                        grid: { display: false }, 
                        ticks: { font: { size: 10, weight: '800' }, color: secondaryTextColor } 
                    }
                }
            }
        });

        // Performance & Attendance Trends Line Chart
        const ctxTrends = document.getElementById('officeTrendsChart').getContext('2d');
        window.officeTrendsChart = new Chart(ctxTrends, {
            type: 'line',
            data: {
                labels: @js($months),
                datasets: [
                    {
                        label: 'Completed Tasks',
                        data: @js($completedTasksTrends),
                        borderColor: '#0B5694',
                        backgroundColor: 'rgba(11, 86, 148, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#0B5694',
                        pointHoverRadius: 6,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Attendance Issues (Lates/Absents)',
                        data: @js($attendanceIssuesTrends),
                        borderColor: '#FB923C',
                        backgroundColor: 'rgba(251, 146, 60, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#FB923C',
                        pointHoverRadius: 6,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            color: textColor,
                            usePointStyle: true,
                            boxWidth: 8,
                            font: { weight: '800', size: 11 }
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#1f2937' : '#ffffff',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: isDark ? '#374151' : '#e5e7eb',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { font: { size: 9, weight: '800' }, color: secondaryTextColor }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: '800' }, color: secondaryTextColor }
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection
