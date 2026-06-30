@extends('layouts.app')

@section('title', 'SmarTern System Documentation')

@section('content')
<x-page-layout 
    portalType="Admin" 
    userName="{{ Auth::user()->email }}" 
    userRole="Admin"
    :showNotifications="false"
>
    <div class="flex flex-col xl:flex-row gap-8 w-full min-h-[calc(100vh-120px)] text-gray-900 dark:text-gray-100" 
         x-data="{ 
             activeTab: 'overview',
             tabs: {
                 overview: 'Overview',
                 architecture: 'Architecture & Stack',
                 roles: 'User Roles & Workflow',
                 onboarding: 'Onboarding & Matching',
                 security: 'Security & Backup',
                 future: 'Future Roadmap'
             }
         }">
        
        <!-- Document Sidebar Navigation -->
        <div class="w-full xl:w-72 shrink-0">
            <div class="sticky top-6 space-y-4">
                <div class="px-3">
                    <h3 class="text-xs font-bold text-gray-400 dark:text-gray-550 uppercase tracking-widest">SmarTern Docs</h3>
                    <p class="text-lg font-extrabold text-[#0B5694] dark:text-blue-400 tracking-tight mt-1">System Manual</p>
                </div>
                
                <nav class="flex flex-col gap-1 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-3 rounded-2xl shadow-sm">
                    <template x-for="(label, key) in tabs" :key="key">
                        <button type="button" 
                                @click="activeTab = key; window.scrollTo({top: 0, behavior: 'smooth'})"
                                :class="activeTab === key ? 'bg-blue-50 dark:bg-blue-950/30 text-[#0B5694] dark:text-blue-400 font-bold border-l-4 border-[#0B5694] dark:border-blue-500' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 border-l-4 border-transparent'"
                                class="flex items-center justify-between px-4 py-3 text-xs uppercase tracking-wider transition-all text-left rounded-r-lg w-full focus:outline-none cursor-pointer">
                            <span x-text="label"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="opacity-60"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                    </template>
                </nav>

                <div class="bg-gray-100/50 dark:bg-gray-800/30 rounded-2xl p-5 border border-gray-150 dark:border-gray-800">
                    <h4 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Scope</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                        This documentation portal is restricted to <strong>Super Admin</strong> & <strong>Admin</strong> privileges.
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-grow max-w-4xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 sm:p-10 shadow-sm transition-colors duration-300">
            
            <!-- OVERVIEW -->
            <div x-show="activeTab === 'overview'" x-cloak class="space-y-8 text-left">
                <div>
                    <span class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-widest">Introduction</span>
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mt-2">System Overview</h2>
                    <div class="h-1.5 w-16 bg-[#0B5694] dark:bg-blue-600 rounded-full mt-4"></div>
                </div>

                <p class="text-gray-600 dark:text-gray-300 text-[15px] leading-relaxed font-medium">
                    Welcome to the <strong>SmarTern System Documentation</strong>. SmarTern is a unified web-based management system custom-built for the <strong>Schools Division Office of Quezon City (SDO-QC)</strong> to manage, matching, tracking, and evaluating student internships across different division offices and schools.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <div class="p-6 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                        <div class="w-10 h-10 bg-blue-50 dark:bg-blue-950/40 rounded-xl flex items-center justify-center text-[#0B5694] dark:text-blue-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">Automated Matching</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            Upload candidate spreadsheets and division office capacity files to match applicants with relevant offices using built-in algorithms.
                        </p>
                    </div>

                    <div class="p-6 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                        <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">Real-Time Geofenced Timekeeping</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            Interns log their attendance via QR scans verified against real-time geolocation coordinates matching their assigned office geofence.
                        </p>
                    </div>

                    <div class="p-6 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                        <div class="w-10 h-10 bg-purple-50 dark:bg-purple-950/40 rounded-xl flex items-center justify-center text-purple-600 dark:text-purple-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">Task & Journal Flow</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            Supervisors issue tasks, interns write daily journals, and coordinators overview academic compliance metrics.
                        </p>
                    </div>

                    <div class="p-6 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                        <div class="w-10 h-10 bg-amber-50 dark:bg-amber-950/40 rounded-xl flex items-center justify-center text-amber-600 dark:text-amber-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">Rigorous Audit Logging</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            Security engine flags login anomalies, tracks multi-session hijack attempts, and manages physical and automated system snapshots.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ARCHITECTURE -->
            <div x-show="activeTab === 'architecture'" x-cloak class="space-y-8 text-left">
                <div>
                    <span class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-widest">Stack & Structure</span>
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mt-2">Architecture & Technologies</h2>
                    <div class="h-1.5 w-16 bg-[#0B5694] dark:bg-blue-600 rounded-full mt-4"></div>
                </div>

                <p class="text-gray-600 dark:text-gray-300 text-[15px] leading-relaxed font-medium">
                    SmarTern is built with standard modern open-source web technologies prioritizing performance, strict backend integrity checks, and high design aesthetics.
                </p>

                <div class="space-y-6">
                    <div class="border-l-4 border-blue-500 pl-4 py-1">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">Backend System</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                            Built on <strong>Laravel 11+</strong> and PHP 8.2+. Uses Eloquent ORM, Cache facade for database load management, and Laravel Reverb / Pusher WebSockets for real-time channel broadcasting.
                        </p>
                    </div>

                    <div class="border-l-4 border-[#0B5694] pl-4 py-1">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">Frontend Layer</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                            HTML5 + Blade layouts styled using <strong>Tailwind CSS</strong>. Dynamic interactions, settings state persistence, and verification forms are handled by <strong>Alpine.js</strong>.
                        </p>
                    </div>

                    <div class="border-l-4 border-emerald-500 pl-4 py-1">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">Database & Security</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                            <strong>MySQL</strong> or <strong>PostgreSQL</strong> relational database. Security configurations enforce Google 2FA (TOTP/Email), SHA-256 backup verification, and unique device cookies mapping.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ROLES -->
            <div x-show="activeTab === 'roles'" x-cloak class="space-y-8 text-left">
                <div>
                    <span class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-widest">Access Control</span>
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mt-2">User Roles & Workflow</h2>
                    <div class="h-1.5 w-16 bg-[#0B5694] dark:bg-blue-600 rounded-full mt-4"></div>
                </div>

                <div class="space-y-8">
                    <div class="relative pl-8">
                        <div class="absolute left-0 top-1 w-3.5 h-3.5 rounded-full bg-red-500 shadow-sm border border-white"></div>
                        <h4 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">1. Admins & Super Admins</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                            Complete administrative control, global configuration parameters override, accounts registration import, manual database snapshot restoration, and security anomaly threat analysis resolution.
                        </p>
                    </div>

                    <div class="relative pl-8">
                        <div class="absolute left-0 top-1 w-3.5 h-3.5 rounded-full bg-purple-500 shadow-sm border border-white"></div>
                        <h4 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">2. Head Supervisors</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                            Oversees division offices, configures training hours templates, reviews interns assignment schedules, and triggers automated applicant matches.
                        </p>
                    </div>

                    <div class="relative pl-8">
                        <div class="absolute left-0 top-1 w-3.5 h-3.5 rounded-full bg-blue-500 shadow-sm border border-white"></div>
                        <h4 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">3. Supervisors</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                            Assigned to direct offices. Monitors daily log attendance, approves or revokes leaves requests, assigns work tasks, and verifies end-of-internship completion endorsements.
                        </p>
                    </div>

                    <div class="relative pl-8">
                        <div class="absolute left-0 top-1 w-3.5 h-3.5 rounded-full bg-teal-500 shadow-sm border border-white"></div>
                        <h4 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">4. School Coordinators</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                            Represent academic partners. Monitors progress templates of students, tracks hours logs accumulation, and exports internship PDF reports for university evaluations.
                        </p>
                    </div>

                    <div class="relative pl-8">
                        <div class="absolute left-0 top-1 w-3.5 h-3.5 rounded-full bg-emerald-500 shadow-sm border border-white"></div>
                        <h4 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">5. Interns</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                            Core target user. Onboards with digital resume parser, logs geofenced attendance, uploads daily task outputs and journals, and downloads verified certificates of completion.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ONBOARDING & MATCHING -->
            <div x-show="activeTab === 'onboarding'" x-cloak class="space-y-8 text-left">
                <div>
                    <span class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-widest">Matches & Intake</span>
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mt-2">Onboarding & Office Matching</h2>
                    <div class="h-1.5 w-16 bg-[#0B5694] dark:bg-blue-600 rounded-full mt-4"></div>
                </div>

                <div class="space-y-6">
                    <p class="text-gray-600 dark:text-gray-300 text-[15px] leading-relaxed font-medium">
                        One of SmarTern's critical functionalities is the automated parsing and placement matching engine. This minimizes manual HR workloads at SDO-QC.
                    </p>

                    <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-150 dark:border-gray-800 rounded-2xl p-6">
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">Algorithm Mechanics</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            The matching module ingests CSV/Excel files containing applicant profiles (including their skills and background) and mapping capacities of SDO offices. It matches candidates prioritizing division capacities, technical skillset keywords matching, and geographic convenience metrics.
                        </p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-150 dark:border-gray-800 rounded-2xl p-6">
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">Intake Flow</h4>
                        <ol class="list-decimal pl-4 space-y-2 text-xs text-gray-500 dark:text-gray-400 mt-2">
                            <li>Applicant submits details via onboarding public portal, uploading their resume.</li>
                            <li>The parsing engine parses skillset, university details, and contact coordinates.</li>
                            <li>Head Supervisors trigger matching batch previews, evaluate candidate profiles, and click <strong>Finalize Batch</strong> to provision student accounts.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- SECURITY -->
            <div x-show="activeTab === 'security'" x-cloak class="space-y-8 text-left">
                <div>
                    <span class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-widest">Monitoring & Backups</span>
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mt-2">Security, MFA & Backups</h2>
                    <div class="h-1.5 w-16 bg-[#0B5694] dark:bg-blue-600 rounded-full mt-4"></div>
                </div>

                <div class="space-y-6">
                    <p class="text-gray-600 dark:text-gray-300 text-[15px] leading-relaxed font-medium">
                        Since SDO-QC holds sensitive student records, SmarTern enforces comprehensive monitoring.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-6 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-2xl">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">Two-Factor Authentication</h4>
                            <p class="text-xs text-gray-550 dark:text-gray-400 leading-relaxed">
                                Users can enable Google 2FA (TOTP authenticator applications) or email verification OTPs through the system settings.
                            </p>
                        </div>

                        <div class="p-6 bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-2xl">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">Device Binding Lockouts</h4>
                            <p class="text-xs text-gray-550 dark:text-gray-400 leading-relaxed">
                                Frontend monitors local device profile cookies mapping. If duplicate session cookies are injected on different browsers, sessions are immediately closed on both ends.
                            </p>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-150 dark:border-gray-800 rounded-2xl p-6">
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">Database Snapshots</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            Admins can trigger manual file and database backups, verify Integrity SHA-256 hashes, upload external backup zip files, and run hot restores to roll back critical errors.
                        </p>
                    </div>
                </div>
            </div>

            <!-- FUTURE ROADMAP -->
            <div x-show="activeTab === 'future'" x-cloak class="space-y-8 text-left">
                <div>
                    <span class="text-[10px] font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-widest">Expansion</span>
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mt-2">Future Roadmap</h2>
                    <div class="h-1.5 w-16 bg-[#0B5694] dark:bg-blue-600 rounded-full mt-4"></div>
                </div>

                <div class="space-y-6">
                    <p class="text-gray-600 dark:text-gray-300 text-[15px] leading-relaxed font-medium">
                        SmarTern is architected to allow easy modifications as requirements grow. Here is how key systems can be expanded:
                    </p>

                    <div class="space-y-6">
                        <div class="bg-blue-50/30 dark:bg-blue-950/10 border border-blue-100/50 dark:border-blue-900/30 p-6 rounded-2xl">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">1. Matching Algorithm Upgrade</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                                Integrate vector embeddings to parse resumes with semantic models instead of simple keyword parsing. This will allow highly accurate matching based on natural descriptions.
                            </p>
                        </div>

                        <div class="bg-emerald-50/30 dark:bg-emerald-950/10 border border-emerald-100/50 dark:border-emerald-900/30 p-6 rounded-2xl">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">2. Geofence & QR Verification</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                                Currently verification uses browser geolocation coordinates. An upgrade can implement camera-based face verification with WebRTC, or bind the system to active directory local networks.
                            </p>
                        </div>

                        <div class="bg-purple-50/30 dark:bg-purple-950/10 border border-purple-100/50 dark:border-purple-900/30 p-6 rounded-2xl">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">3. Mobile Native App client</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                                Expose unified API routes with Laravel Sanctum token verification to deploy a light-weight React Native application for interns' clock-ins and push notifications.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-page-layout>
@endsection
