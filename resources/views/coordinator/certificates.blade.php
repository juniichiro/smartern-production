@extends('layouts.app')

@section('title', 'Certificates')

@section('content')
<x-page-layout 
    portalType="School Coordinator" 
    userName="{{ Auth::user()->schoolCoordinatorDetails->first_name }}" 
    userRole="{{ Auth::user()->schoolCoordinatorDetails->school }}"
>
    {{-- Header Section --}}
    <x-page-header 
        title="Certificates" 
        subtitle="Verification & preview of student completion credentials" 
    />

    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-10 gap-6">
        <div class="flex gap-4">
            <span class="px-4 py-1.5 bg-[#0B5694] dark:bg-blue-900/20 text-white dark:text-blue-400 text-[10px] font-bold rounded-full border border-blue-100 dark:border-blue-800 uppercase tracking-wider shadow-sm">Official Records</span>
        </div>
        <button onclick="window.history.back()" class="w-full lg:w-auto flex items-center justify-center gap-3 px-6 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl text-[10px] font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition shadow-sm uppercase tracking-wider active:scale-95">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Directory
        </button>
    </div>

    {{-- Intern Profile Summary Card --}}
    <x-card-container padding="p-8 lg:p-12">
        <div class="flex flex-col md:flex-row gap-10 items-center mb-12 border-b border-gray-100 dark:border-gray-800 pb-12">
            <x-avatar :user="null" size="32" circular="false" border="4" textSize="4xl" />
            
            <div class="space-y-2 text-center md:text-left flex-1 min-w-0">
                <h3 class="text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tight uppercase">Ren Ren Agustin</h3>
                <p class="text-[12px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest italic">Example University • SDO QC Records Unit</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-1">Feb 1, 2026 — Apr 30, 2026</p>
                
                <div class="flex flex-wrap justify-center md:justify-start gap-3 mt-6">
                    <span class="px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold rounded-full border border-emerald-100 dark:border-emerald-900/30 uppercase tracking-wider">Good Punctuality</span>
                    <span class="px-4 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-bold rounded-full border border-blue-100 dark:border-blue-900/30 uppercase tracking-wider">Task Compliant</span>
                </div>
            </div>
        </div>

        {{-- Metric Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="p-8 bg-gray-50 dark:bg-gray-800/50 rounded-3xl border border-gray-100 dark:border-gray-700 text-center group hover:border-[#0B5694] transition-all">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Hours Rendered</p>
                <p class="text-4xl font-black text-gray-800 dark:text-gray-100 tracking-tighter">480/480</p>
                <div class="w-full bg-white dark:bg-gray-900 h-2 rounded-full mt-6 overflow-hidden shadow-inner border border-gray-100 dark:border-gray-800">
                    <div class="bg-[#0B5694] h-full shadow-lg" style="width: 100%"></div>
                </div>
            </div>

            <div class="p-8 bg-gray-50 dark:bg-gray-800/50 rounded-3xl border border-gray-100 dark:border-gray-700 text-center group hover:border-[#0B5694] transition-all">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Task Completion</p>
                <p class="text-4xl font-black text-gray-800 dark:text-gray-100 tracking-tighter">21/21</p>
                <div class="w-full bg-white dark:bg-gray-900 h-2 rounded-full mt-6 overflow-hidden shadow-inner border border-gray-100 dark:border-gray-800">
                    <div class="bg-[#0B5694] h-full shadow-lg" style="width: 100%"></div>
                </div>
            </div>

            <div class="p-8 bg-gray-50 dark:bg-gray-800/50 rounded-3xl border border-gray-100 dark:border-gray-700 text-center group hover:border-[#0B5694] transition-all">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Performance Rating</p>
                <p class="text-4xl font-black text-gray-800 dark:text-gray-100 tracking-tighter">4.5<span class="text-xl text-gray-300 ml-1">/5</span></p>
                <div class="w-full bg-white dark:bg-gray-900 h-2 rounded-full mt-6 overflow-hidden shadow-inner border border-gray-100 dark:border-gray-800">
                    <div class="bg-emerald-500 h-full shadow-lg" style="width: 85%"></div>
                </div>
            </div>

            <div class="p-8 bg-gray-50 dark:bg-gray-800/50 rounded-3xl border border-gray-100 dark:border-gray-700 text-center group hover:border-[#0B5694] transition-all">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Certification</p>
                <p class="text-4xl font-black text-[#0B5694] dark:text-blue-400 tracking-tighter uppercase">PUBLISHED</p>
                <div class="w-full bg-white dark:bg-gray-900 h-2 rounded-full mt-6 overflow-hidden shadow-inner border border-gray-100 dark:border-gray-800">
                    <div class="bg-emerald-500 h-full shadow-lg" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </x-card-container>

    {{-- Certificate Preview Container --}}
    <div class="mt-8 bg-gray-100 dark:bg-gray-900 rounded-[50px] p-6 lg:p-20 flex flex-col items-center border border-gray-200 dark:border-gray-800 shadow-inner">
        <div class="bg-white dark:bg-gray-950 w-full max-w-5xl shadow-2xl p-10 lg:p-24 aspect-[1.414/1] flex flex-col items-center justify-between text-center border-[15px] lg:border-[30px] border-gray-50 dark:border-gray-900 relative overflow-hidden group">
            {{-- Decorative Elements --}}
            <div class="absolute top-0 left-0 w-32 h-32 bg-[#0B5694]/5 rounded-br-full"></div>
            <div class="absolute bottom-0 right-0 w-32 h-32 bg-[#0B5694]/5 rounded-tl-full"></div>

            <div class="space-y-6 lg:space-y-10 mt-4">
                <h3 class="text-3xl lg:text-7xl font-serif text-gray-800 dark:text-gray-100 tracking-tight">Certificate of Completion</h3>
                <p class="text-lg lg:text-3xl text-gray-400 dark:text-gray-500 italic font-serif">This is to certify that</p>
            </div>

            <div class="w-3/4 border-b-2 lg:border-b-4 border-gray-100 dark:border-gray-800 pb-4 lg:pb-8">
                <h4 class="text-2xl lg:text-6xl font-serif font-extrabold text-gray-900 dark:text-white uppercase tracking-normal">REN REN AGUSTIN</h4>
            </div>

            <div class="space-y-2 lg:space-y-4">
                <p class="text-sm lg:text-2xl text-gray-500 dark:text-gray-400 font-medium">has successfully completed the SmarTern Internship Program</p>
                <p class="text-sm lg:text-2xl text-gray-500 dark:text-gray-400">with a total of <span class="font-black text-gray-900 dark:text-white">480 Hours rendered</span></p>
            </div>

            <div class="w-full flex justify-around mt-10 lg:mt-20 mb-6 lg:mb-10">
                <div class="w-32 lg:w-64 border-t lg:border-t-2 border-gray-200 dark:border-gray-800 pt-4">
                    <p class="text-[9px] lg:text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Intern Supervisor</p>
                </div>
                <div class="w-32 lg:w-64 border-t lg:border-t-2 border-gray-200 dark:border-gray-800 pt-4">
                    <p class="text-[9px] lg:text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Program Dean</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Button --}}
    <div class="mt-10">
        <button class="w-full bg-[#0B5694] dark:bg-blue-600 text-white py-6 rounded-2xl font-bold text-xs uppercase tracking-wider shadow-2xl shadow-blue-500/20 hover:bg-[#084577] dark:hover:bg-blue-700 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-4 group">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:translate-y-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Official Record
        </button>
    </div>
</x-page-layout>
@endsection
