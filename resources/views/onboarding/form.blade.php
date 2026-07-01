@extends('layouts.app')

@section('title', 'Intern Onboarding')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-950 font-sans flex items-center justify-center p-3 xs:p-4 md:p-8 transition-colors duration-300 relative"
     x-data="smartOnboardingManager(@js($assessmentItems))">
    
    {{-- Back to Landing --}}
    <a href="{{ url('/') }}" class="fixed top-4 left-4 sm:top-6 sm:left-6 z-[100] p-2.5 sm:p-3 bg-white/90 dark:bg-white/10 backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-2xl text-gray-500 dark:text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 hover:bg-white dark:hover:bg-white/20 transition-all active:scale-95 shadow-xl flex items-center gap-2 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="sm:w-4 sm:h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span>Back</span>
    </a>

    {{-- Theme Toggle --}}
    <div class="fixed top-4 right-4 sm:top-6 sm:right-6 z-[100]" x-data="darkModeManager">
        <button @click="darkMode = !darkMode"
                class="p-2.5 sm:p-3 bg-white/90 dark:bg-white/10 backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-2xl text-[#0B5694] dark:text-white hover:bg-white dark:hover:bg-white/20 transition-all active:scale-95 shadow-xl">
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="18" height="18" class="sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>       
            </svg>
            <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" class="sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
    </div>

    <div class="w-full max-w-4xl">
        {{-- Header Section --}}
        <div class="text-center mb-10" x-show="step > 0 && step < 4">
            <div class="inline-flex items-center justify-center p-4 bg-white dark:bg-gray-900 rounded-[24px] mb-6 shadow-xl border border-gray-100 dark:border-gray-800">
                <img src="{{ asset('images/blue-smartern.png') }}" alt="SmarTern Logo" class="h-10">
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight uppercase"
                x-text="step === 1 ? 'Step 1: Attach Resource' : (step === 2 ? 'Step 2: Refine Details' : (step === 3 ? 'Step 3: Finalize & Publish' : 'Application Finalized'))">
                Step 1: Attach Resource
            </h1>
            <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mt-3 italic"
               x-text="step === 1 ? 'Provide reference materials to begin matching' : (step === 2 ? 'Please verify your information and competencies' : (step === 3 ? 'Final verification before matching process' : 'Your application has been published successfully'))">
                Provide reference materials to begin matching
            </p>
        </div>

        {{-- Progress Indicator --}}
        <div class="flex items-center justify-between max-w-lg mx-auto mb-12 relative px-4" x-show="step > 0 && step < 4">
            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-200 dark:bg-gray-800 -translate-y-1/2 z-0"></div>
            <div class="absolute top-1/2 left-0 h-0.5 bg-[#0B5694] dark:bg-blue-500 -translate-y-1/2 z-0 transition-all duration-500"
                 :style="'width: ' + ((step - 1) / 2 * 100) + '%'"></div>

            <div class="relative z-10 flex flex-col items-center group" @click="if(step > 1) step = 1">
                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-500 border-2"
                     :class="step >= 1 ? 'bg-[#0B5694] border-[#0B5694] text-white shadow-lg shadow-blue-500/20' : 'bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800 text-gray-400'">      
                     <template x-if="step > 1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                     </template>
                     <template x-if="step <= 1">
                        <span class="font-bold text-sm">1</span>
                     </template>
                </div>
                <span class="absolute -bottom-7 text-[9px] font-bold uppercase tracking-wider whitespace-nowrap transition-colors duration-300"
                      :class="step >= 1 ? 'text-[#0B5694] dark:text-blue-400' : 'text-gray-400 dark:text-gray-600'">Reference</span>
            </div>

            <div class="relative z-10 flex flex-col items-center group" @click="if(step > 2) step = 2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-500 border-2"
                     :class="step >= 2 ? 'bg-[#0B5694] border-[#0B5694] text-white shadow-lg shadow-blue-500/20' : 'bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800 text-gray-400'">      
                     <template x-if="step > 2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                     </template>
                     <template x-if="step <= 2">
                        <span class="font-bold text-sm">2</span>
                     </template>
                </div>
                <span class="absolute -bottom-7 text-[9px] font-bold uppercase tracking-wider whitespace-nowrap transition-colors duration-300"
                      :class="step >= 2 ? 'text-[#0B5694] dark:text-blue-400' : 'text-gray-400 dark:text-gray-600'">Details</span>
            </div>

            <div class="relative z-10 flex flex-col items-center group" @click="if(step > 3) step = 3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-500 border-2"
                     :class="step >= 3 ? 'bg-[#0B5694] border-[#0B5694] text-white shadow-lg shadow-blue-500/20' : 'bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800 text-gray-400'">      
                     <span class="font-bold text-sm">3</span>
                </div>
                <span class="absolute -bottom-7 text-[9px] font-bold uppercase tracking-wider whitespace-nowrap transition-colors duration-300"
                      :class="step >= 3 ? 'text-[#0B5694] dark:text-blue-400' : 'text-gray-400 dark:text-gray-600'">Finalize</span>
            </div>
        </div>

        <div class="relative">
            <form id="finalForm" action="{{ route('onboarding.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="file" id="hiddenResumeInput" name="resume_pdf" class="hidden">
                <input type="file" x-ref="profileInput" name="profile_picture" class="hidden" @change="handleProfileUpload">

                {{-- STEP 0: WELCOME & INTRODUCTION --}}
                <div x-show="step === 0" class="animate-in fade-in slide-in-from-bottom-4 duration-500 max-w-2xl mx-auto">
                    <div class="bg-white dark:bg-gray-900 rounded-[24px] xs:rounded-[32px] sm:rounded-[40px] shadow-xl dark:shadow-none border border-gray-100 dark:border-gray-800 p-5 xs:p-8 md:p-12 text-center space-y-8">
                        <div class="space-y-6">
                            <div class="inline-flex items-center justify-center p-4 bg-white dark:bg-gray-900 rounded-[24px] mb-4 shadow-xl border border-gray-100 dark:border-gray-800">
                                <img src="{{ asset('images/blue-smartern.png') }}" alt="SmarTern Logo" class="h-10">
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Become a SmarTern Intern</h2>
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto font-medium leading-relaxed">
                                Join our smart internship matching ecosystem. Our AI-driven engine parses your profile and matches you with the ideal host office based on your skills and preferences.
                            </p>
                        </div>

                        {{-- Steps Outline Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-left my-8">
                            <div class="p-5 bg-gray-50 dark:bg-gray-800/30 rounded-3xl border border-gray-100/50 dark:border-gray-800/50 space-y-2">
                                <div class="w-8 h-8 rounded-full bg-[#0B5694]/10 dark:bg-blue-500/10 flex items-center justify-center text-[#0B5694] dark:text-blue-400 font-bold text-sm">1</div>
                                <h4 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider">Upload Reference</h4>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Upload your PDF resume to auto-fill details.</p>
                            </div>
                            <div class="p-5 bg-gray-50 dark:bg-gray-800/30 rounded-3xl border border-gray-100/50 dark:border-gray-800/50 space-y-2">
                                <div class="w-8 h-8 rounded-full bg-[#0B5694]/10 dark:bg-blue-500/10 flex items-center justify-center text-[#0B5694] dark:text-blue-400 font-bold text-sm">2</div>
                                <h4 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider">Refine Profile</h4>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Review details and self-assess competencies.</p>
                            </div>
                            <div class="p-5 bg-gray-50 dark:bg-gray-800/30 rounded-3xl border border-gray-100/50 dark:border-gray-800/50 space-y-2">
                                <div class="w-8 h-8 rounded-full bg-[#0B5694]/10 dark:bg-blue-500/10 flex items-center justify-center text-[#0B5694] dark:text-blue-400 font-bold text-sm">3</div>
                                <h4 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider">Submit & Match</h4>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Publish your application to matching queue.</p>
                            </div>
                        </div>

                        <div class="space-y-4 pt-4">
                            <button type="button" @click="step = 1" class="w-full bg-[#0B5694] dark:bg-blue-600 text-white py-5 rounded-[30px] font-black text-sm uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:shadow-blue-500/40 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3">
                                <span>Get Started</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </button>

                            <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                Already have an account? 
                                <a href="{{ route('login') }}" class="text-[#0B5694] dark:text-blue-400 hover:underline ml-1">Log in here</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 1: RESUME UPLOAD --}}
                <div x-show="step === 1" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="bg-white dark:bg-gray-900 rounded-[24px] xs:rounded-[32px] sm:rounded-[40px] shadow-xl dark:shadow-none border border-gray-100 dark:border-gray-800 p-5 xs:p-8 md:p-12 text-center space-y-10">
                        <div class="space-y-4">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-blue-50 dark:bg-blue-900/20 rounded-[20px] sm:rounded-[28px] flex items-center justify-center mx-auto text-[#0B5694] dark:text-blue-400 mb-6 group-hover:scale-110 transition-transform shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" class="sm:w-8 sm:h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M9 15h6"/><path d="M9 11h6"/><path d="M9 19h6"/></svg>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Attach Reference Materials</h3>
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto font-medium">Reference materials assist the matching engine in identifying your core competencies automatically.</p>
                        </div>
 
                        <div class="relative">
                            <input type="file" @change="handleResumeUpload" accept=".pdf" class="hidden" id="resume_upload">
                            <label for="resume_upload" class="cursor-pointer block p-6 xs:p-10 sm:p-12 border-2 border-transparent bg-gray-50/50 dark:bg-gray-800/20 rounded-[20px] xs:rounded-[28px] sm:rounded-[35px] hover:bg-gray-100 dark:hover:bg-gray-800/40 transition-all group border-gray-100 dark:border-gray-800">
                                <div x-show="!isParsing && !resumeFileName" class="space-y-4">
                                    <p class="text-xs sm:text-sm font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Click to browse or drag reference PDF</p>
                                    <p class="text-[9px] sm:text-[10px] text-gray-300 dark:text-gray-600 font-bold uppercase tracking-widest">Max Size: 5MB • PDF Only</p>
                                </div>
                                <div x-show="isParsing" class="flex flex-col items-center gap-4 py-4 animate-pulse">
                                    <div class="w-10 h-10 border-4 border-[#0B5694] border-t-transparent rounded-full animate-spin"></div>
                                    <p class="text-xs font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-widest">Analyzing resources...</p>
                                </div>
                                <div x-show="!isParsing && resumeFileName" class="flex flex-col items-center gap-4 py-4">
                                    <div class="flex items-center gap-3 px-6 py-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                        <span class="text-xs font-bold uppercase tracking-wider" x-text="resumeFileName"></span>
                                    </div>
                                    <button type="button" @click.prevent="isResumeModalOpen = true" class="text-[10px] font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-widest hover:underline">Review Attached Resource</button>
                                </div>
                            </label>
                        </div>
                        <div class="flex items-center gap-4 justify-center">
                            <button type="button" 
                                    @click="resumeFile = null; resumeFileName = ''; resumePreviewUrl = null; step = 2" 
                                    class="px-6 py-3.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-700 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m13 17 5-5-5-5"/><path d="m6 17 5-5-5-5"/></svg>
                                <span>Skip and Fill Manually</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: PROFILE & SKILLS --}}
                <div x-show="step === 2" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500 pb-32">
                    {{-- Personal Details Card --}}                    {{-- Personal Details Card --}}
                    <div class="bg-white dark:bg-gray-900 rounded-[24px] xs:rounded-[32px] sm:rounded-[40px] shadow-xl dark:shadow-none border border-gray-100 dark:border-gray-800 p-5 xs:p-8 md:p-12 space-y-12">
                        <div class="flex flex-col sm:flex-row items-center gap-6 sm:gap-10">
                            <div class="relative group shrink-0">
                                <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full bg-gray-50 dark:bg-gray-800 border-2 border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden group-hover:scale-[1.02] transition-transform duration-500 cursor-pointer flex items-center justify-center relative"
                                     @click="!profilePreview ? $refs.profileInput.click() : null">
                                    <template x-if="profilePreview">
                                        <div class="w-full h-full relative">
                                            <img :src="profilePreview" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                                <button type="button" @click.stop="profilePreview = null; profileFile = null; $refs.profileInput.value = ''" 
                                                         class="p-2 bg-rose-500 text-white rounded-xl shadow-lg hover:scale-110 active:scale-95 transition-all" title="Remove Photo">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                </button>
                                                <button type="button" @click.stop="$refs.profileInput.click()" 
                                                         class="p-2 bg-blue-500 text-white rounded-xl shadow-lg hover:scale-110 active:scale-95 transition-all" title="Change Photo">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!profilePreview">
                                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 dark:text-gray-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" class="sm:w-10 sm:h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            <span class="text-[8px] sm:text-[9px] font-bold uppercase tracking-wider mt-2.5 sm:mt-4">Attach Photo</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
 
                            <div class="flex-1 space-y-6 w-full text-left">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">First Name</label>
                                        <input type="text" name="first_name" required x-model="formData.first_name" 
                                               @input="errors['first_name'] = false"
                                               :class="errors['first_name'] ? 'border-rose-500 focus:border-rose-500 ring-4 ring-rose-500/5' : 'border-gray-100 dark:border-gray-800 focus:border-blue-500/20'"
                                               class="w-full bg-gray-50 dark:bg-gray-800 border-2 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
                                        <p x-show="errors['first_name']" x-text="errors['first_name']" class="text-[9px] font-black text-rose-500 uppercase tracking-widest mt-1 ml-1 animate-in fade-in slide-in-from-top-1"></p>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Middle Name</label>
                                        <input type="text" name="middle_name" x-model="formData.middle_name" 
                                               @input="errors['middle_name'] = false"
                                               :class="errors['middle_name'] ? 'border-rose-500 focus:border-rose-500 ring-4 ring-rose-500/5' : 'border-gray-100 dark:border-gray-800 focus:border-blue-500/20'"
                                               class="w-full bg-gray-50 dark:bg-gray-800 border-2 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
                                        <p x-show="errors['middle_name']" x-text="errors['middle_name']" class="text-[9px] font-black text-rose-500 uppercase tracking-widest mt-1 ml-1 animate-in fade-in slide-in-from-top-1"></p>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Last Name</label>
                                        <input type="text" name="last_name" required x-model="formData.last_name"
                                               @input="errors['last_name'] = false"
                                               :class="errors['last_name'] ? 'border-rose-500 focus:border-rose-500 ring-4 ring-rose-500/5' : 'border-gray-100 dark:border-gray-800 focus:border-blue-500/20'"
                                               class="w-full bg-gray-50 dark:bg-gray-800 border-2 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
                                        <p x-show="errors['last_name']" x-text="errors['last_name']" class="text-[9px] font-black text-rose-500 uppercase tracking-widest mt-1 ml-1 animate-in fade-in slide-in-from-top-1"></p>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                                    <input type="email" name="email" required x-model="formData.email"
                                           @input="errors['email'] = false"
                                           :class="errors['email'] ? 'border-rose-500 focus:border-rose-500 ring-4 ring-rose-500/5' : 'border-gray-100 dark:border-gray-800 focus:border-blue-500/20'"
                                           class="w-full bg-gray-50 dark:bg-gray-800 border-2 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
                                    <p x-show="errors['email']" x-text="errors['email']" class="text-[9px] font-black text-rose-500 uppercase tracking-widest mt-1 ml-1 animate-in fade-in slide-in-from-top-1"></p>
                                </div>
                            </div>
                        </div>
 
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
                            <div class="space-y-2" id="gender-selection">
                                <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Gender Identification</label>
                                <div class="grid grid-cols-3 gap-2.5">
                                    <button type="button" @click="gender = 'male'; errors['gender'] = false" :class="gender === 'male' ? 'bg-[#0B5694] text-white border-transparent' : (errors['gender'] ? 'bg-rose-50/50 dark:bg-rose-900/10 border-rose-500 text-rose-500' : 'bg-gray-50 dark:bg-gray-800 text-gray-400 border-gray-100 dark:border-gray-800')" class="py-3.5 sm:py-4 rounded-xl xs:rounded-2xl text-[9px] xs:text-[10px] font-bold uppercase tracking-wider transition-all border-2">Male</button>
                                    <button type="button" @click="gender = 'female'; errors['gender'] = false" :class="gender === 'female' ? 'bg-[#0B5694] text-white border-transparent' : (errors['gender'] ? 'bg-rose-50/50 dark:bg-rose-900/10 border-rose-500 text-rose-500' : 'bg-gray-50 dark:bg-gray-800 text-gray-400 border-gray-100 dark:border-gray-800')" class="py-3.5 sm:py-4 rounded-xl xs:rounded-2xl text-[9px] xs:text-[10px] font-bold uppercase tracking-wider transition-all border-2">Female</button>
                                    <button type="button" @click="gender = 'other'; errors['gender'] = false" :class="gender === 'other' ? 'bg-[#0B5694] text-white border-transparent' : (errors['gender'] ? 'bg-rose-50/50 dark:bg-rose-900/10 border-rose-500 text-rose-500' : 'bg-gray-50 dark:bg-gray-800 text-gray-400 border-gray-100 dark:border-gray-800')" class="py-3.5 sm:py-4 rounded-xl xs:rounded-2xl text-[9px] xs:text-[10px] font-bold uppercase tracking-wider transition-all border-2">Other</button>
                                </div>
                                <p x-show="errors['gender']" x-text="errors['gender']" class="text-[9px] font-black text-rose-500 uppercase tracking-widest mt-1 ml-1 animate-in fade-in slide-in-from-top-1"></p>
                                <input type="hidden" name="gender" :value="gender">
                                <div x-show="gender === 'other'" class="mt-4 animate-in slide-in-from-top-2">
                                    <input type="text" name="gender_other_specify" x-model="formData.gender_other_specify" 
                                           @input="errors['gender_other_specify'] = false"
                                           :class="errors['gender_other_specify'] ? 'border-rose-500 focus:border-rose-500 ring-4 ring-rose-500/5' : 'border-gray-100 dark:border-gray-800 focus:border-blue-500/20'"
                                           placeholder="Please specify gender" class="w-full bg-gray-50 dark:bg-gray-800 border-2 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none">
                                    <p x-show="errors['gender_other_specify']" x-text="errors['gender_other_specify']" class="text-[9px] font-black text-rose-500 uppercase tracking-widest mt-1 ml-1 animate-in fade-in slide-in-from-top-1"></p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Academic Institution</label>
                                <div class="relative" @click.away="isUniDropdownOpen = false">
                                    <input type="text" name="school" required x-model="formData.school"
                                           autocomplete="off"
                                           @focus="isUniDropdownOpen = true"
                                           @input="uniSearch = formData.school; isUniDropdownOpen = true; errors['school'] = false"
                                           :class="errors['school'] ? 'border-rose-500 focus:border-rose-500 ring-4 ring-rose-500/5' : 'border-gray-100 dark:border-gray-800 focus:border-blue-500/20'"
                                           class="w-full bg-gray-50 dark:bg-gray-800 border-2 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
                                    <p x-show="errors['school']" x-text="errors['school']" class="text-[9px] font-black text-rose-500 uppercase tracking-widest mt-1 ml-1 animate-in fade-in slide-in-from-top-1"></p>
                                    <div x-show="isUniDropdownOpen && filteredUniversities.length > 0" 
                                         class="absolute z-[120] w-full mt-2 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[24px] shadow-2xl max-h-64 overflow-y-auto py-2 no-scrollbar">
                                        <template x-for="uni in filteredUniversities" :key="uni">
                                            <div @click="formData.school = uni; uniSearch = uni; isUniDropdownOpen = false; errors['school'] = false" 
                                                 class="px-6 py-4 text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-[#0B5694] dark:hover:text-blue-400 cursor-pointer transition-colors"
                                                 x-text="uni"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Contact Number</label>
                                <input type="text" name="phone_number" required x-model="formData.phone_number"
                                       @input="errors['phone_number'] = false"
                                       :class="errors['phone_number'] ? 'border-rose-500 focus:border-rose-500 ring-4 ring-rose-500/5' : 'border-gray-100 dark:border-gray-800 focus:border-blue-500/20'"
                                       class="w-full bg-gray-50 dark:bg-gray-800 border-2 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/5 transition-all outline-none">
                                <p x-show="errors['phone_number']" x-text="errors['phone_number']" class="text-[9px] font-black text-rose-500 uppercase tracking-widest mt-1 ml-1 animate-in fade-in slide-in-from-top-1"></p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 col-span-1 md:col-span-2">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Emergency Contact (Full Name)</label>
                                    <input type="text" name="emergency_contact" required x-model="formData.emergency_contact"
                                           @input="errors['emergency_contact'] = false"
                                           :class="errors['emergency_contact'] ? 'border-rose-500 focus:border-rose-500 ring-4 ring-rose-500/5' : 'border-gray-100 dark:border-gray-800 focus:border-blue-500/20'"
                                           class="w-full bg-gray-50 dark:bg-gray-800 border-2 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all">
                                    <p x-show="errors['emergency_contact']" x-text="errors['emergency_contact']" class="text-[9px] font-black text-rose-500 uppercase tracking-widest mt-1 ml-1 animate-in fade-in slide-in-from-top-1"></p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Emergency Contact Number</label>
                                    <input type="text" name="emergency_contact_number" required x-model="formData.emergency_contact_number"
                                           @input="errors['emergency_contact_number'] = false"
                                           :class="errors['emergency_contact_number'] ? 'border-rose-500 focus:border-rose-500 ring-4 ring-rose-500/5' : 'border-gray-100 dark:border-gray-800 focus:border-blue-500/20'"
                                           class="w-full bg-gray-50 dark:bg-gray-800 border-2 rounded-2xl px-5 py-4 text-sm font-bold text-gray-700 dark:text-gray-200 outline-none transition-all">
                                    <p x-show="errors['emergency_contact_number']" x-text="errors['emergency_contact_number']" class="text-[9px] font-black text-rose-500 uppercase tracking-widest mt-1 ml-1 animate-in fade-in slide-in-from-top-1"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Competency Assessment Form --}}
                    <div class="space-y-12">
                        @foreach($assessmentItems as $category => $categoryItems)
                            <div class="bg-white dark:bg-gray-900 rounded-[24px] xs:rounded-[32px] sm:rounded-[40px] shadow-xl dark:shadow-none border border-gray-100 dark:border-gray-800 text-left">
                                <div class="bg-gray-50 dark:bg-gray-950 px-5 xs:px-8 sm:px-10 py-5 sm:py-6 border-b border-gray-100 dark:border-gray-800 rounded-t-[24px] xs:rounded-t-[32px] sm:rounded-t-[40px]">
                                    <h3 class="text-xs font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-[0.2em]">{{ $category }}</h3>
                                </div>
                                <div class="p-5 xs:p-8 md:p-10 space-y-6 sm:space-y-8">
                                    @foreach($categoryItems as $item)
                                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center group">
                                            <div class="lg:col-span-8">
                                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200 leading-relaxed group-hover:text-[#0B5694] dark:group-hover:text-blue-400 transition-colors">
                                                    {{ $item->competency_text }}
                                                </p>
                                            </div>
                                            <div class="lg:col-span-4" x-data="{ open: false, score: null }">
                                                <div class="relative w-full" :id="'competency-' + '{{ $item->id }}'">
                                                    @if(str_contains($category, 'I.'))
                                                        <input type="hidden" name="tech_skills[]" value="{{ $item->competency_text }}">
                                                        <input type="hidden" name="tech_scores[]" x-model="score">
                                                    @else
                                                        <input type="hidden" name="soft_skills[]" value="{{ $item->competency_text }}">
                                                        <input type="hidden" name="soft_scores[]" x-model="score">
                                                    @endif

                                                    <button type="button" @click="open = !open"
                                                            class="w-full bg-gray-50 dark:bg-gray-800 border-2 focus:border-blue-500/20 rounded-2xl px-5 py-3 text-xs font-black flex items-center justify-between cursor-pointer outline-none uppercase tracking-widest transition-all"
                                                            :class="score === null ? (errors['competency-{{ $item->id }}'] ? 'border-rose-500 text-rose-500' : 'border-gray-100 dark:border-gray-800 text-gray-400') : 'border-gray-100 dark:border-gray-800 text-gray-700 dark:text-gray-200'">
                                                        <span x-text="score === null ? 'Select Proficiency' : (score + ' - ' + ['Novice', 'Developing', 'Proficient', 'Advanced', 'Exceptional'][score - 1])"></span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
                                                    </button>
                                                    
                                                    <div x-show="open" @click.outside="open = false" x-cloak
                                                         class="absolute z-[110] w-full top-full mt-2 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-2xl overflow-hidden py-2 animate-in fade-in slide-in-from-top-2 duration-200">
                                                        <template x-for="i in 5">
                                                            <div @click="score = i; open = false; errors['competency-{{ $item->id }}'] = false; updateSkillInSummary('{{ $item->competency_text }}', i, {{ str_contains($category, 'I.') ? 'true' : 'false' }})" 
                                                                 class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer transition-colors"
                                                                 x-text="i + ' - ' + ['Novice', 'Developing', 'Proficient', 'Advanced', 'Exceptional'][i - 1]"></div>
                                                        </template>
                                                    </div>
                                                    <p x-show="errors['competency-{{ $item->id }}']" x-cloak class="absolute -bottom-5 left-1 text-[9px] font-black text-rose-500 uppercase tracking-widest animate-in fade-in slide-in-from-top-1">Selection Required</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-10">
                        <button type="button" @click="if(validateStep(2)) { step = 3; $nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' })) }" class="w-full bg-[#0B5694] dark:bg-blue-600 text-white py-5 rounded-[30px] font-black text-sm uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:shadow-blue-500/40 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3 group">
                            <span>Review Assessment</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:translate-x-1"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- STEP 3: PREVIEW & VERIFY --}}
                <div x-show="step === 3" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500 pb-32">                    {{-- Personal Details Card (Mirroring Step 2) --}}
                    <div class="bg-white dark:bg-gray-900 rounded-[24px] xs:rounded-[32px] sm:rounded-[40px] shadow-xl dark:shadow-none border border-gray-100 dark:border-gray-800 p-5 xs:p-8 md:p-12 space-y-12">
                        <div class="flex flex-col sm:flex-row items-center gap-6 sm:gap-10">
                            <div class="relative group shrink-0">
                                <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full bg-gray-50 dark:bg-gray-800 border-2 border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden flex items-center justify-center relative pointer-events-none">
                                    <template x-if="profilePreview">
                                        <img :src="profilePreview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!profilePreview">
                                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 dark:text-gray-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" class="sm:w-10 sm:h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        </div>
                                    </template>
                                </div>
                            </div>
 
                            <div class="flex-1 space-y-6 w-full text-left">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">First Name</label>
                                        <div class="w-full bg-gray-50/50 dark:bg-gray-800/50 border-2 border-gray-100 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-550 dark:text-slate-400" x-text="formData.first_name"></div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Middle Name</label>
                                        <div class="w-full bg-gray-50/50 dark:bg-gray-800/50 border-2 border-gray-100 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-550 dark:text-slate-400" x-text="formData.middle_name || 'N/A'"></div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Last Name</label>
                                        <div class="w-full bg-gray-50/50 dark:bg-gray-800/50 border-2 border-gray-100 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-550 dark:text-slate-400" x-text="formData.last_name"></div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Email Address</label>
                                    <div class="w-full bg-gray-50/50 dark:bg-gray-800/50 border-2 border-gray-100 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-550 dark:text-slate-400" x-text="formData.email"></div>
                                </div>
                            </div>
                        </div>
 
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Gender Identification</label>
                                <div class="grid grid-cols-3 gap-2.5 pointer-events-none">
                                    <div :class="gender === 'male' ? 'bg-[#0B5694] text-white border-transparent' : 'bg-gray-50/50 dark:bg-gray-800/50 text-gray-450 border-gray-100 dark:border-gray-800'" class="py-3.5 sm:py-4 rounded-xl xs:rounded-2xl text-[9px] xs:text-[10px] font-bold uppercase tracking-wider text-center border-2">Male</div>
                                    <div :class="gender === 'female' ? 'bg-[#0B5694] text-white border-transparent' : 'bg-gray-50/50 dark:bg-gray-800/50 text-gray-455 border-gray-100 dark:border-gray-800'" class="py-3.5 sm:py-4 rounded-xl xs:rounded-2xl text-[9px] xs:text-[10px] font-bold uppercase tracking-wider text-center border-2">Female</div>
                                    <div :class="gender === 'other' ? 'bg-[#0B5694] text-white border-transparent' : 'bg-gray-50/50 dark:bg-gray-800/50 text-gray-455 border-gray-100 dark:border-gray-800'" class="py-3.5 sm:py-4 rounded-xl xs:rounded-2xl text-[9px] xs:text-[10px] font-bold uppercase tracking-wider text-center border-2">Other</div>
                                </div>
                                <div x-show="gender === 'other'" class="mt-4">
                                    <div class="w-full bg-gray-50/50 dark:bg-gray-800/50 border-2 border-gray-100 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-500 dark:text-gray-400" x-text="formData.gender_other_specify"></div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Academic Institution</label>
                                <div class="w-full bg-gray-50/50 dark:bg-gray-800/50 border-2 border-gray-100 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-500 dark:text-gray-400" x-text="formData.school"></div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Contact Number</label>
                                <div class="w-full bg-gray-50/50 dark:bg-gray-800/50 border-2 border-gray-100 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-500 dark:text-gray-400" x-text="formData.phone_number"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 col-span-1 md:col-span-2">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Emergency Contact (Full Name)</label>
                                    <div class="w-full bg-gray-50/50 dark:bg-gray-800/50 border-2 border-gray-100 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-500 dark:text-gray-400" x-text="formData.emergency_contact"></div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest ml-1">Emergency Contact Number</label>
                                    <div class="w-full bg-gray-50/50 dark:bg-gray-800/50 border-2 border-gray-100 dark:border-gray-800 rounded-2xl px-5 py-4 text-sm font-bold text-gray-500 dark:text-gray-400" x-text="formData.emergency_contact_number"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Dynamic Competency Assessment Form (Mirroring Step 2) --}}
                    <div class="space-y-12">
                        @foreach($assessmentItems as $category => $categoryItems)
                            <div class="bg-white dark:bg-gray-900 rounded-[24px] xs:rounded-[32px] sm:rounded-[40px] shadow-xl dark:shadow-none border border-gray-100 dark:border-gray-800 text-left">
                                <div class="bg-gray-50 dark:bg-gray-950 px-5 xs:px-8 sm:px-10 py-5 sm:py-6 border-b border-gray-100 dark:border-gray-800 rounded-t-[24px] xs:rounded-t-[32px] sm:rounded-t-[40px]">
                                    <h3 class="text-xs font-black text-[#0B5694] dark:text-blue-400 uppercase tracking-[0.2em]">{{ $category }}</h3>
                                </div>
                                <div class="p-5 xs:p-8 md:p-10 space-y-6 sm:space-y-8">
                                    @foreach($categoryItems as $item)
                                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                                            <div class="lg:col-span-8">
                                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200 leading-relaxed">
                                                    {{ $item->competency_text }}
                                                </p>
                                            </div>
                                            <div class="lg:col-span-4" x-data="{ 
                                                get currentScore() {
                                                    const name = '{{ $item->competency_text }}';
                                                    const isTech = {{ str_contains($category, 'I.') ? 'true' : 'false' }};
                                                    const skill = (isTech ? techSkills : softSkills).find(s => s.name === name);
                                                    return skill ? skill.score : null;
                                                }
                                            }">
                                                <div class="w-full bg-gray-50/50 dark:bg-gray-800/50 border-2 border-gray-100 dark:border-gray-800 rounded-2xl px-5 py-3 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest flex items-center justify-between pointer-events-none">
                                                    <span x-text="currentScore === null ? 'Not Rated' : (currentScore + ' - ' + ['Novice', 'Developing', 'Proficient', 'Advanced', 'Exceptional'][currentScore - 1])"></span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
 
                    {{-- Privacy Acknowledgement & Final Actions --}}
                    <div class="space-y-10">
                        <div class="p-5 xs:p-8 bg-blue-50/50 dark:bg-blue-900/10 rounded-[20px] xs:rounded-[28px] sm:rounded-[35px] border border-blue-100/50 dark:border-blue-800/30 text-left">
                            <div class="flex items-start">
                                <input type="checkbox" id="privacy_acknowledgement" name="privacy_acknowledgement" value="1" required class="h-5 w-5 rounded-[6px] border-gray-300 text-[#0B5694] focus:ring-[#0B5694] mt-0.5 shrink-0">
                                <label for="privacy_acknowledgement" class="ml-3 text-xs text-gray-600 dark:text-gray-400 font-bold leading-normal">
                                    I acknowledge and agree to the <a href="{{ route('privacy-policy') }}" target="_blank" class="text-[#0B5694] dark:text-blue-400 hover:underline">Privacy Policy</a> and the <a href="https://www.officialgazette.gov.ph/2012/08/15/republic-act-no-10173/" target="_blank" class="text-[#0B5694] dark:text-blue-400 hover:underline">Data Privacy Act of 2012</a>.
                                </label>
                            </div>
                        </div>
 
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="button" @click="step = 2" class="w-full sm:w-1/3 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 py-5 rounded-[30px] font-black text-sm uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-700 transition-all hover:scale-[1.01] active:scale-[0.99]">Back to Edit</button>
                            <button type="button" @click="submitFinalForm" class="w-full sm:w-2/3 bg-[#0B5694] dark:bg-blue-600 text-white py-5 rounded-[30px] font-black text-sm uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:shadow-blue-500/40 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3">
                                <span>Finalize & Publish</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
 
        {{-- SUCCESS STATE --}}
        <div x-show="step === 4" x-cloak class="bg-white dark:bg-gray-900 rounded-[24px] xs:rounded-[32px] sm:rounded-[40px] shadow-xl border border-gray-100 dark:border-gray-800 p-5 xs:p-8 md:p-12 text-center transition-all duration-300">
            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-green-50 dark:bg-green-900/20 rounded-[24px] sm:rounded-[32px] flex items-center justify-center mx-auto text-green-600 dark:text-green-400 mb-8">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" class="sm:w-12 sm:h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white uppercase tracking-tight">Application Finalized!</h2>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 mb-10 leading-relaxed">We couldn't find any issues with your submission. Your credentials have been authorized.</p>
            <button type="button" @click="window.location.reload()" class="w-full bg-[#0B5694] dark:bg-blue-600 text-white py-5 rounded-[20px] sm:rounded-[24px] font-black text-sm uppercase tracking-widest shadow-xl">Finish Process</button>
        </div>
    </div>

    {{-- System Modals --}}
    <div x-show="isResumeModalOpen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4 md:p-12 bg-black/90 backdrop-blur-md">
        <div class="relative w-full max-w-6xl h-full flex flex-col" @click.away="isResumeModalOpen = false">
            <div class="flex justify-end mb-4"><button @click="isResumeModalOpen = false" class="text-white hover:scale-110 transition-transform"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button></div>
            <div class="flex-1 bg-white dark:bg-gray-900 rounded-[40px] shadow-2xl overflow-hidden flex flex-col">
                <div class="flex-1 bg-gray-100 dark:bg-gray-800 relative">
                    <iframe :src="resumePreviewUrl" class="w-full h-full border-0"></iframe>
                </div>
                <div class="p-6 shrink-0 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                    <button @click="isResumeModalOpen = false" class="w-full bg-[#0B5694] dark:bg-blue-600 text-white py-5 rounded-2xl font-bold text-xs uppercase tracking-wider">Close Artifact Preview</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<style>[x-cloak] { display: none !important; } .no-scrollbar::-webkit-scrollbar { display: none; }</style>
<script>
    function smartOnboardingManager(assessmentItems) {
        return {
            step: {{ session('success') ? 4 : ($errors->any() ? 2 : 0) }},
            isParsing: false,
            resumeFile: null,
            resumeFileName: '',
            resumePreviewUrl: null,
            profileFile: null,
            profilePreview: null,
            isResumeModalOpen: false,
            errors: @js($errors->getMessages()),
            universities: @js($universities ?? []),
            uniSearch: '',
            isUniDropdownOpen: false,
            gender: @js(old('gender', null)),
            techSkills: [],
            softSkills: [],
            formData: {
                first_name: @js(old('first_name', '')),
                middle_name: @js(old('middle_name', '')),
                last_name: @js(old('last_name', '')),
                email: @js(old('email', '')),
                phone_number: @js(old('phone_number', '')),
                school: @js(old('school', '')),
                gender_other_specify: @js(old('gender_other_specify', '')),
                emergency_contact: @js(old('emergency_contact', '')),
                emergency_contact_number: @js(old('emergency_contact_number', ''))
            },
            init() {
                // Flatten server-side errors if they are arrays (Laravel's default)
                Object.keys(this.errors).forEach(key => {
                    if (Array.isArray(this.errors[key])) {
                        this.errors[key] = this.errors[key][0];
                    }
                });

                Object.keys(assessmentItems).forEach(cat => {
                    assessmentItems[cat].forEach(item => {
                        const obj = { name: item.competency_text, score: null };
                        if (cat.includes('I.')) this.techSkills.push(obj);
                        else this.softSkills.push(obj);
                    });
                });
            },
            updateSkillInSummary(name, score, isTech) {
                const list = isTech ? this.techSkills : this.softSkills;
                const match = list.find(s => s.name === name);
                if (match) match.score = score;
            },
            handleProfileUpload(event) {
                const file = event.target.files[0];
                if (!file || !file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.profilePreview = e.target.result;
                    this.profileFile = file;
                };
                reader.readAsDataURL(file);
            },
            handleResumeUpload(event) {
                const file = event.target.files[0];
                if (!file || file.type !== 'application/pdf') {
                    if (window.showToast) window.showToast('Please upload a PDF document.', 'error');
                    return;
                }
                this.resumeFile = file;
                this.resumeFileName = file.name;
                this.resumePreviewUrl = URL.createObjectURL(file);
                this.isParsing = true;
                const fd = new FormData();
                fd.append('resume_pdf', file);
                fd.append('_token', '{{ csrf_token() }}');
                fetch('{{ route('onboarding.parse') }}', { method: 'POST', body: fd })
                .then(async r => {
                    const data = await r.json();
                    if (!r.ok) throw new Error(data.error || 'Failed to parse resource.');
                    return data;
                })
                .then(data => {
                    if (data.first_name) this.formData.first_name = data.first_name;
                    if (data.last_name) this.formData.last_name = data.last_name;
                    if (data.email) this.formData.email = data.email;
                    if (data.phone_number) this.formData.phone_number = data.phone_number;
                    this.step = 2;
                })
                .catch(err => {
                    if (window.showToast) {
                        window.showToast(err.message, 'error');
                    } else {
                        alert(err.message);
                    }
                    this.resumeFile = null;
                    this.resumeFileName = '';
                })
                .finally(() => this.isParsing = false);
            },
            get filteredUniversities() {
                const s = (this.uniSearch || '').trim().toLowerCase();
                return s ? this.universities.filter(u => u.toLowerCase().includes(s)) : this.universities;
            },
            normalizePhoneNumber(f) {
                let val = this.formData[f];
                if (!val) return;
                
                // Strip all non-digit characters to check pure numeric length
                let digits = val.replace(/\D/g, '');
                
                // If it's exactly 10 digits and doesn't start with 0, prepend 0
                if (digits.length === 10 && !digits.startsWith('0')) {
                    this.formData[f] = '0' + digits;
                } else {
                    // Otherwise, just use the cleaned digits (or original if symbols are preferred, 
                    // but usually for system consistency we want the clean format)
                    this.formData[f] = digits;
                }
            },
            validateStep(s) { 
                if (s === 2) {
                    let hasErrors = false;
                    this.errors = {}; 

                    // Normalize phone numbers before validation
                    this.normalizePhoneNumber('phone_number');
                    this.normalizePhoneNumber('emergency_contact_number');

                    // Validation Rules Definition
                    const rules = {
                        first_name: { required: true, name: true, max: 50, label: 'First Name' },
                        middle_name: { required: false, name: true, max: 50, label: 'Middle Name' },
                        last_name: { required: true, name: true, max: 50, label: 'Last Name' },
                        email: { required: true, email: true, max: 100, label: 'Email Address' },
                        school: { required: true, alphanumeric: true, max: 150, label: 'Academic Institution' },
                        phone_number: { required: true, phone: true, min: 10, max: 11, label: 'Contact Number' },
                        emergency_contact: { required: true, name: true, max: 100, label: 'Emergency Contact Name' },
                        emergency_contact_number: { required: true, phone: true, min: 10, max: 11, label: 'Emergency Contact Number' }
                    };

                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    const phoneRegex = /^[0-9]*$/; // Updated to be stricter since we normalize to digits
                    const nameRegex = /^[a-zA-Z\s\-\.]*$/;
                    const alphanumericRegex = /^[a-zA-Z0-9\s\-\.,&()]*$/;

                    Object.keys(rules).forEach(f => {
                        const val = this.formData[f];
                        const rule = rules[f];

                        if (rule.required && (!val || (typeof val === 'string' && val.trim() === ''))) {
                            this.errors[f] = `Please provide your ${rule.label.toLowerCase()}.`;
                            hasErrors = true;
                        } else if (rule.max && val && val.length > rule.max) {
                            this.errors[f] = `The ${rule.label.toLowerCase()} is too long (max ${rule.max} characters).`;
                            hasErrors = true;
                        } else if (rule.min && val && val.length < rule.min) {
                            this.errors[f] = `Please provide a complete ${rule.label.toLowerCase()}.`;
                            hasErrors = true;
                        } else if (rule.email && !emailRegex.test(val)) {
                            this.errors[f] = `We need a valid email format to reach you.`;
                            hasErrors = true;
                        } else if (rule.phone && !phoneRegex.test(val)) {
                            this.errors[f] = `Please use numbers only for contact details.`;
                            hasErrors = true;
                        } else if (rule.name && !nameRegex.test(val)) {
                            this.errors[f] = `Names should only contain letters and standard symbols.`;
                            hasErrors = true;
                        } else if (rule.alphanumeric && !alphanumericRegex.test(val)) {
                            this.errors[f] = `This field contains invalid special characters.`;
                            hasErrors = true;
                        }
                    });

                    if (!this.gender) {
                        this.errors['gender'] = 'Please select a gender option.';
                        hasErrors = true;
                    }

                    if (this.gender === 'other' && (!this.formData.gender_other_specify || !this.formData.gender_other_specify.trim())) {
                        this.errors['gender_other_specify'] = 'Please specify your identity details.';
                        hasErrors = true;
                    }

                    // Assessment Validation
                    this.techSkills.forEach((skill, index) => {
                        if (skill.score === null) {
                            hasErrors = true;
                            // We need to find the specific competency ID for the error mapping
                            // This is a bit tricky because techSkills is a simple list
                            // But we can use the competency text as a key or just find the element
                        }
                    });

                    // Re-calculate competency errors based on Alpine state
                    const assessmentInputs = document.querySelectorAll('[id^="competency-"]');
                    assessmentInputs.forEach(container => {
                        const id = container.id.replace('competency-', '');
                        // Find the skill in our lists
                        const skillText = container.closest('.group').querySelector('p').innerText.trim();
                        const skill = [...this.techSkills, ...this.softSkills].find(s => s.name === skillText);
                        
                        if (skill && skill.score === null) {
                            this.errors['competency-' + id] = 'Selection Required';
                            hasErrors = true;
                        }
                    });

                    if (hasErrors) {
                        this.scrollToFirstError();
                        return false;
                    }
                }

                if (s === 3) {
                    const privacyChecked = document.getElementById('privacy_acknowledgement').checked;
                    if (!privacyChecked) {
                        // We'll use a dynamic toast or just a standard alert for this final hurdle
                        if (window.showToast) {
                            window.showToast('Please acknowledge the Privacy Policy to proceed.', 'error');
                        } else {
                            alert('Please acknowledge the Privacy Policy to proceed.');
                        }
                        return false;
                    }
                }
                return true; 
            },
            scrollToFirstError() {
                this.$nextTick(() => {
                    const errorElements = document.querySelectorAll('.border-rose-500');
                    let topMost = null;
                    let minY = Infinity;

                    errorElements.forEach(el => {
                        const rect = el.getBoundingClientRect();
                        const absoluteY = rect.top + window.pageYOffset;
                        if (absoluteY < minY) {
                            minY = absoluteY;
                            topMost = el;
                        }
                    });

                    if (topMost) {
                        window.scrollTo({
                            top: minY - 150,
                            behavior: 'smooth'
                        });
                    }
                });
            },
            submitFinalForm() {
                if (!this.validateStep(3)) return;
                const dt = new DataTransfer();
                if (this.resumeFile) {
                    dt.items.add(this.resumeFile);
                    document.getElementById('hiddenResumeInput').files = dt.files;
                }
                document.getElementById('finalForm').submit();
            }
        };
    }
</script>
@endpush
@endsection
