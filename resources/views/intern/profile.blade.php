@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<x-page-layout
    portalType="Intern"
    userName="{{ $intern->first_name }}"
    userRole="{{ $intern->office->name ?? 'ICT Unit' }}"
>
    <div x-data="{ 
        loading: true,
        isPhotoModalOpen: false,
        photoModalStep: 'guide',
        cropper: null,
        processProfileFile(file) {
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                if (window.showToast) window.showToast('Please upload a valid image file (JPG, JPEG, or PNG).', 'error');
                this.$refs.profileInput.value = '';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                if (window.showToast) window.showToast('The image is too large. Please upload a photo smaller than 2MB.', 'error');
                this.$refs.profileInput.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                this.photoModalStep = 'crop';
                this.$nextTick(() => {
                    const image = this.$refs.cropperTarget;
                    image.onload = () => {
                        if (this.cropper) {
                            this.cropper.destroy();
                        }
                        this.cropper = new Cropper(image, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 0.8,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                        });
                    };
                    image.src = e.target.result;
                });
            };
            reader.readAsDataURL(file);
        },
        handleProfileUpload(event) {
            this.processProfileFile(event.target.files[0]);
        },
        handleProfileDrop(event) {
            this.processProfileFile(event.dataTransfer.files[0]);
        },
        closePhotoModal() {
            this.isPhotoModalOpen = false;
            this.photoModalStep = 'guide';
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
            this.$refs.profileInput.value = '';
        },
        cropAndSave() {
            if (!this.cropper) return;
            this.cropper.getCroppedCanvas({
                width: 300,
                height: 300,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            }).toBlob((blob) => {
                if (!blob) return;
                
                const croppedFile = new File([blob], 'profile_picture.png', { type: 'image/png' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                this.$refs.profileInput.files = dataTransfer.files;
                
                document.getElementById('avatarForm').submit();
            }, 'image/png');
        }
    }" x-init="setTimeout(() => loading = false, 800)" class="flex flex-col flex-1">

        <x-page-header title="My Profile" subtitle="Your Internship Overview" />

        {{-- SKELETON LOADING VIEW --}}
        <div x-show="loading" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex flex-col flex-1">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                <div class="xl:col-span-4">
                    <x-card-container class="h-[600px]" padding="p-10">
                        <div class="flex flex-col items-center">
                            <x-skeleton type="avatar" class="w-32 h-32 mb-8" />
                            <x-skeleton type="text" class="w-3/4 mb-4" />
                            <x-skeleton type="text" class="w-1/2 mb-10" />
                            <x-skeleton type="block" class="h-12 w-full mb-4" />
                            <x-skeleton type="block" class="h-12 w-full" />
                        </div>
                    </x-card-container>
                </div>
                <div class="xl:col-span-8 space-y-8">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <x-skeleton type="metric" />
                        <x-skeleton type="metric" />
                        <x-skeleton type="metric" />
                    </div>
                    <x-card-container class="h-64" padding="p-10">
                        <x-skeleton type="text" class="w-1/4 mb-6" />
                        <x-skeleton type="list" rows="3" />
                    </x-card-container>
                </div>
            </div>
        </div>

        {{-- ACTUAL CONTENT VIEW --}}
        <div x-show="!loading" x-cloak x-transition:enter="transition ease-out duration-500 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col flex-1">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">

                {{-- LEFT COLUMN: Identity & Assignment --}}
                <div class="xl:col-span-4 space-y-8">

                    {{-- Identity Card --}}
                    <x-card-container padding="p-10">
                        <div class="flex flex-col items-center text-center">
                            <div class="mb-6 relative group/avatar">
                                <form action="{{ route('intern.profile.avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                                    @csrf
                                    <x-avatar :user="$intern" size="32" circular="false" border="4" textSize="4xl" />
                                    
                                    <div @click="isPhotoModalOpen = true; photoModalStep = 'guide'" class="absolute inset-0 bg-black/60 text-white rounded-[35px] flex flex-col items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-opacity duration-300 cursor-pointer border-4 border-white/20 dark:border-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mb-1"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                                        <span class="text-[9px] font-black uppercase tracking-widest">Update Photo</span>
                                        <input type="file" x-ref="profileInput" name="profile_picture" class="hidden" @change="handleProfileUpload">
                                    </div>
                                </form>
                            </div>

                            <h3 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight mb-1">{{ $intern->first_name }} {{ $intern->last_name }}</h3>
                            <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-4">Student ID: {{ $intern->student_id ?? 'N/A' }}</p>

                            <div class="flex flex-col items-center gap-2 mb-6 w-full">
                                <div class="flex items-center gap-2 text-[11px] font-bold text-gray-500 dark:text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    {{ $intern->user->email }}
                                </div>
                                <div class="flex items-center gap-2 text-[11px] font-bold text-gray-500 dark:text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    {{ $intern->phone_number ?? 'No Phone' }}
                                </div>
                            </div>

                            <div class="flex flex-wrap justify-center gap-2 mb-8">
                                <span class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 text-[9px] font-bold uppercase tracking-wider rounded-full border border-blue-100 dark:border-blue-800">
                                    {{ $intern->school ?? 'FEU Tech' }}
                                </span>
                                <span class="px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[9px] font-bold uppercase tracking-wider rounded-full border border-emerald-100 dark:border-emerald-800">
                                    {{ optional($intern->certificate)->status === 'published' ? 'Completed' : 'Active' }}
                                </span>
                            </div>

                            <div class="space-y-4 w-full">
                                @if($supervisor)
                                    <div x-data="{
                                            showEmail: false,
                                            email: '{{ $supervisor->user->email }}',
                                            copyToClipboard() {
                                                navigator.clipboard.writeText(this.email);
                                                window.showToast('Email copied to clipboard', 'success', 'profile-contact');
                                            }
                                         }"
                                         class="relative group/contact">
                                        <button @click="showEmail = !showEmail"
                                                class="flex items-center justify-center gap-3 w-full px-6 py-4 bg-[#0B5694] dark:bg-blue-600 text-white rounded-2xl text-[10px] font-bold uppercase tracking-wider hover:bg-[#084577] dark:hover:bg-blue-700 transition shadow-lg shadow-blue-500/20 active:scale-[0.98]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                            <span x-text="showEmail ? 'Hide Contact' : 'Contact Supervisor'"></span>
                                        </button>

                                        <x-toast-display context="profile-contact" class="!absolute !inset-x-0 !top-full !mt-2 !bottom-auto" />

                                        <div x-show="showEmail"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 -translate-y-2"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             x-cloak
                                             class="mt-3 p-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-xl flex flex-col gap-3">
                                            <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider text-center">Supervisor Email</p>
                                            <div class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-100 dark:border-gray-800 text-center">
                                                <code class="text-sm font-bold text-[#0B5694] dark:text-blue-400 tracking-normal" x-text="email"></code>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button @click="copyToClipboard()" class="px-4 py-2.5 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-xl text-[9px] font-bold uppercase tracking-wider transition flex items-center justify-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                                                    Copy
                                                </button>
                                                <a :href="'mailto:' + email" class="px-4 py-2.5 bg-[#0B5694] dark:bg-blue-600 hover:bg-[#084577] dark:hover:bg-blue-700 text-white rounded-xl text-[9px] font-bold uppercase tracking-wider transition flex items-center justify-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                                                    Open Mail
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center gap-3 w-full px-6 py-4 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 rounded-2xl text-[10px] font-bold uppercase tracking-wider border border-dashed border-gray-200 dark:border-gray-700">
                                        No Supervisor Assigned
                                    </div>
                                @endif

                                <div class="space-y-3 pt-4 border-t border-gray-50 dark:border-gray-800">
                                    <div class="flex flex-col items-center justify-center gap-1 w-full px-6 py-4 border border-rose-100/30 dark:border-rose-900/20 bg-rose-50/10 dark:bg-rose-950/5 text-rose-500/70 dark:text-rose-400/50 rounded-2xl transition hover:bg-rose-50/30 dark:hover:bg-rose-900/10 group">
                                        <div class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-rose-500/50 group-hover:text-rose-500 transition-colors"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                            <span class="text-[10px] font-bold uppercase tracking-wider opacity-70">Emergency Contact</span>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">{{ $intern->emergency_contact ?? 'Not Set' }}</span>
                                            @if($intern->emergency_contact_number)
                                                <span class="text-[10px] font-bold tracking-wider text-gray-400 dark:text-gray-500 mt-0.5">{{ $intern->emergency_contact_number }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-card-container>

                    {{-- Internship Assignment Card --}}
                    <x-card-container title="Internship Assignment" padding="p-10">
                        <div class="space-y-8">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-2xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="16" x="4" y="4" rx="2"/><path d="M9 22V12h6v10"/><path d="M8 2h8"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Assigned Office</p>
                                    <h5 class="text-lg font-extrabold text-gray-900 dark:text-white tracking-tight uppercase">{{ $intern->office->name ?? 'Unassigned' }}</h5>
                                    <p class="text-xs text-[#0B5694] dark:text-blue-400 font-bold mt-1">SDO Quezon City</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 rounded-2xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Reporting Supervisor</p>
                                    @if($supervisor)
                                        <h5 class="text-lg font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $supervisor->first_name }} {{ $supervisor->last_name }}</h5>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium mt-1">{{ $supervisor->user->email }}</p>
                                    @else
                                        <h5 class="text-lg font-extrabold text-gray-300 dark:text-gray-600 tracking-tight uppercase">Awaiting Assignment</h5>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-card-container>
                </div>

                {{-- RIGHT COLUMN: Performance & Analytics --}}
                <div class="xl:col-span-8 space-y-8">

                    {{-- Quick Stats Row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <x-stat-card
                            title="Tasks Completed"
                            value="{{ $completedTasks }}/{{ $totalTasks }}"
                            subtitle="{{ $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0 }}% Done"
                            size="compact"
                        />
                        <x-stat-card
                            title="Attendance Rate"
                            value="{{ $onTimePercentage }}%"
                            subtitle="On-Time Record"
                            size="compact"
                        />
                        <x-stat-card
                            title="Absences"
                            value="{{ $absents }}"
                            subtitle="Days Missed"
                            :color="$absents > 0 ? 'rose' : 'white'"
                            size="compact"
                        />
                    </div>

                    {{-- Performance Review --}}
                    <x-card-container title="Performance Review">
                        <div class="border-b border-gray-50 dark:border-gray-800 bg-gray-50/20 dark:bg-gray-800/10 -mx-8 md:-mx-10 px-8 md:px-10 py-6 mb-0">
                            <h5 class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4">Latest Feedback</h5>
                            <p class="text-sm text-gray-400 dark:text-gray-500 font-medium leading-relaxed">
                                @if(optional($intern->certificate)->status === 'published')
                                    "{{ $intern->certificate->supervisor_feedback ?? 'Your internship has been completed and verified. Final assessment records are now archived.' }}"
                                @else
                                    "Your supervisor hasn't submitted a review yet."
                                @endif
                            </p>
                        </div>
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse">
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                    @php
                                        $cert = $intern->certificate;
                                        $isPublished = optional($cert)->status === 'published';
                                        $eval = $isPublished ? optional($cert)->evaluation : null;

                                        $categories = [
                                            'Technical Proficiency' => optional($eval)->technical_proficiency,
                                            'Professionalism & Ethics' => optional($eval)->professionalism_ethics,
                                            'Communication Skills' => optional($eval)->communication_skills,
                                            'Team Collaboration' => optional($eval)->team_collaboration,
                                        ];
                                    @endphp
                                    @foreach($categories as $categoryName => $score)
                                    <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition">
                                        <td class="p-8">
                                            <p class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-tight">{{ $categoryName }}</p>
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-1">Standard Assessment Criteria</p>
                                        </td>
                                        <td class="p-8 text-right">
                                            @if($score)
                                                @php
                                                    $scoreColors = [
                                                        'Excellent' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50',
                                                        'Very Good' => 'bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 border-teal-100 dark:border-teal-900/50',
                                                        'Good' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900/50',
                                                        'Fair' => 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900/50',
                                                        'Poor' => 'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/50'
                                                    ];
                                                @endphp
                                                <span class="px-6 py-2.5 rounded-xl border text-[10px] font-black uppercase tracking-wider shadow-sm {{ $scoreColors[$score] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-400' }}">
                                                    {{ $score }}
                                                </span>
                                            @else
                                                <span class="px-6 py-2.5 bg-gray-50 dark:bg-gray-800 text-gray-300 dark:text-gray-700 text-[10px] font-bold uppercase tracking-[0.2em] rounded-xl border border-gray-100 dark:border-gray-700 group-hover:border-[#0B5694]/30 transition-all">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-card-container>

                    {{-- Account Settings --}}
                    <x-card-container title="Account Settings">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                            <div class="flex items-center gap-6">
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 rounded-[20px] border border-gray-100 dark:border-gray-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </div>
                                <div>
                                    <h5 class="text-base font-extrabold text-gray-900 dark:text-white uppercase tracking-tight">Account Security</h5>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-0.5">Maintain your credentials</p>
                                </div>
                            </div>
                            <form action="{{ route('profile.password.request-reset') }}" method="POST" class="w-full sm:w-auto">
                                @csrf
                                <button type="submit" class="w-full px-10 py-4 border-2 border-[#0B5694] dark:border-blue-500 hover:bg-[#0B5694] hover:text-white dark:hover:bg-blue-600 text-[#0B5694] dark:text-blue-400 rounded-2xl text-[10px] font-bold uppercase tracking-[0.3em] transition-all flex items-center justify-center gap-3 shadow-md shadow-blue-500/5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3-3.5 3.5z"/></svg>
                                    Reset Password
                                </button>
                            </form>
                        </div>
                    </x-card-container>
                </div>
            </div>
        </div>

    {{-- Profile Photo Upload & Cropping Modal --}}
    <div x-show="isPhotoModalOpen" 
         x-cloak
         class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-gray-900/60 dark:bg-black/80 backdrop-blur-md"
         @keydown.escape.window="closePhotoModal"
         style="display: none;"
    >
        <div class="w-full max-w-md min-h-[480px] bg-white dark:bg-gray-900 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-2xl p-6 sm:p-8 relative flex flex-col justify-between"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        >
            <!-- Close Button -->
            <button type="button" @click="closePhotoModal" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>

            <!-- STEP 1: UPLOAD ZONE -->
            <div x-show="photoModalStep === 'guide'" class="text-center animate-in fade-in duration-200 flex flex-col justify-between flex-1 h-full w-full">
                <div>
                    <h3 class="text-lg font-extrabold text-gray-900 dark:text-white uppercase tracking-tight mb-1">Select Profile Photo</h3>
                    <p class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-6">Attach a formal or semi-formal photo</p>

                    <!-- Dropzone Area -->
                    <div @click="$refs.profileInput.click()" 
                         @dragover.prevent=""
                         @drop.prevent="handleProfileDrop($event)"
                         class="border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-3xl p-8 hover:border-[#0B5694] dark:hover:border-blue-500 hover:bg-gray-50/50 dark:hover:bg-gray-800/10 transition-all cursor-pointer group flex flex-col items-center justify-center min-h-[220px] mb-6"
                    >
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-[#0B5694] dark:text-blue-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200 tracking-tight mb-1">Drag and drop your image here</span>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">or click to browse files</span>
                    </div>
                </div>

                <!-- Specifications Footer -->
                <div class="flex justify-between items-center text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                    <span>Formats: JPG, JPEG, PNG</span>
                    <span>Max Size: 2MB</span>
                </div>
            </div>

            <!-- STEP 2: CROPPING -->
            <div x-show="photoModalStep === 'crop'" class="flex flex-col items-center justify-between flex-1 h-full w-full">
                <div class="w-full flex flex-col items-center">
                    <h3 class="text-lg font-extrabold text-gray-900 dark:text-white uppercase tracking-tight mb-2">Crop Profile Picture</h3>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-6">Position and size to fit the circle preview</p>
                    
                    <!-- Cropper Workspace -->
                    <div class="w-full max-h-[300px] bg-gray-100 dark:bg-gray-800 rounded-2xl overflow-hidden mb-6 flex justify-center items-center">
                        <img x-ref="cropperTarget" class="max-w-full max-h-[300px] block">
                    </div>
                </div>

                <div class="flex gap-4 w-full">
                    <button type="button" @click="closePhotoModal"
                            class="flex-1 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all active:scale-95 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" @click="cropAndSave"
                            class="flex-1 py-4 bg-[#0B5694] hover:bg-[#073D6B] dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-bold uppercase text-[10px] tracking-wider rounded-2xl transition-all shadow-md active:scale-95 cursor-pointer">
                        Crop & Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-page-layout>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<style>
    /* Circular Cropper Style */
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%;
    }
</style>
@endpush
@endsection
