@extends('layouts.app')

@section('title', 'Certificate Settings')

@section('content')
<x-page-layout 
    portalType="Head Supervisor" 
    userName="{{ Auth::user()->supervisorDetails->first_name }}" 
    userRole="{{ Auth::user()->supervisorDetails->office->name }}"
>
    <div class="max-w-6xl mx-auto pb-12">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <x-page-header 
                title="Certificate Template Uploader" 
                subtitle="Customize the certificate format, logos, and signatory credentials dynamically." 
            />
            <div>
                <a href="{{ route('head-supervisor.certificates.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition active:scale-95 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back to Registry
                </a>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 rounded-2xl flex items-center gap-3 text-emerald-800 dark:text-emerald-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/50 rounded-2xl text-rose-800 dark:text-rose-300">
                <div class="flex items-center gap-3 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span class="text-sm font-black uppercase tracking-wider">Please fix the following validation errors:</span>
                </div>
                <ul class="list-disc pl-5 text-xs space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('head-supervisor.certificates.template.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Form fields: Left Column (2/3 width) --}}
                <div class="lg:col-span-2 space-y-8">
                    
                    {{-- Header Text & Title Config --}}
                    <div class="bg-white dark:bg-gray-900 p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
                        <h3 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-wider border-b border-gray-50 dark:border-gray-800 pb-3 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#0B5694]"></span>
                            Header Labels & Title
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="gov_text" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Government Line</label>
                                <input type="text" name="gov_text" id="gov_text" value="{{ old('gov_text', $template->gov_text) }}" class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium">
                            </div>

                            <div>
                                <label for="dept_text" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Department Name</label>
                                <input type="text" name="dept_text" id="dept_text" value="{{ old('dept_text', $template->dept_text) }}" class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium">
                            </div>

                            <div>
                                <label for="region_text" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Region Text</label>
                                <input type="text" name="region_text" id="region_text" value="{{ old('region_text', $template->region_text) }}" class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium">
                            </div>

                            <div>
                                <label for="office_text" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Schools Division Office</label>
                                <input type="text" name="office_text" id="office_text" value="{{ old('office_text', $template->office_text) }}" class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-50 dark:border-gray-800">
                            <label for="title_text" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Certificate Title</label>
                            <input type="text" name="title_text" id="title_text" value="{{ old('title_text', $template->title_text) }}" class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-bold uppercase tracking-wide">
                        </div>
                    </div>

                    {{-- Body Content --}}
                    <div class="bg-white dark:bg-gray-900 p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
                        <div class="flex justify-between items-center border-b border-gray-50 dark:border-gray-800 pb-3">
                            <h3 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-wider flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                Certificate Body Text
                            </h3>
                        </div>

                        <div>
                            <label for="body_text" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Statement Format</label>
                            <textarea name="body_text" id="body_text" rows="5" class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 transition text-gray-800 dark:text-gray-100 font-medium leading-relaxed">{{ old('body_text', $template->body_text) }}</textarea>
                        </div>

                        {{-- Template Variables Guide --}}
                        <div class="bg-blue-50/50 dark:bg-blue-950/20 rounded-2xl p-5 border border-blue-100/50 dark:border-blue-900/30">
                            <h4 class="text-xs font-bold text-[#0B5694] dark:text-blue-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                Available Dynamic Placeholders
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 leading-relaxed">
                                The system replaces these tags with individual intern metadata when the PDF is downloaded:
                            </p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="p-2.5 bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <code class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{name}</code>
                                    <span class="block text-[10px] text-gray-400 uppercase font-medium mt-1">Full Name</span>
                                </div>
                                <div class="p-2.5 bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <code class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{course}</code>
                                    <span class="block text-[10px] text-gray-400 uppercase font-medium mt-1">Course</span>
                                </div>
                                <div class="p-2.5 bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <code class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{school}</code>
                                    <span class="block text-[10px] text-gray-400 uppercase font-medium mt-1">University</span>
                                </div>
                                <div class="p-2.5 bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <code class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{hours}</code>
                                    <span class="block text-[10px] text-gray-400 uppercase font-medium mt-1">Hours</span>
                                </div>
                                <div class="p-2.5 bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <code class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{start_date}</code>
                                    <span class="block text-[10px] text-gray-400 uppercase font-medium mt-1">Start Date</span>
                                </div>
                                <div class="p-2.5 bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <code class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{end_date}</code>
                                    <span class="block text-[10px] text-gray-400 uppercase font-medium mt-1">End Date</span>
                                </div>
                                <div class="p-2.5 bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <code class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{unit}</code>
                                    <span class="block text-[10px] text-gray-400 uppercase font-medium mt-1">Office Unit</span>
                                </div>
                                <div class="p-2.5 bg-white dark:bg-gray-800/80 rounded-xl border border-gray-100 dark:border-gray-800">
                                    <code class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{article}</code>
                                    <span class="block text-[10px] text-gray-400 uppercase font-medium mt-1">a / an Course</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Signatory Section --}}
                    <div class="bg-white dark:bg-gray-900 p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
                        <h3 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-wider border-b border-gray-50 dark:border-gray-800 pb-3 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            Signatory Block
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="signatory_name" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Signatory Full Name</label>
                                <input type="text" name="signatory_name" id="signatory_name" value="{{ old('signatory_name', $template->signatory_name) }}" class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-bold uppercase">
                            </div>

                            <div>
                                <label for="signatory_title" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Designation / Title</label>
                                <input type="text" name="signatory_title" id="signatory_title" value="{{ old('signatory_title', $template->signatory_title) }}" class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-50 dark:border-gray-800">
                            <label for="initials" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Left-side Footer Initials / Reference Code</label>
                            <input type="text" name="initials" id="initials" value="{{ old('initials', $template->initials) }}" class="w-full bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0B5694] transition text-gray-800 dark:text-gray-100 font-medium italic">
                        </div>
                    </div>
                </div>

                {{-- Asset Uploaders: Right Column (1/3 width) --}}
                <div class="space-y-8">
                    
                    {{-- Logo Upload --}}
                    <div class="bg-white dark:bg-gray-900 p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
                        <h3 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-wider border-b border-gray-50 dark:border-gray-800 pb-3 flex items-center gap-2">
                            Header Logo
                        </h3>
                        
                        <div class="flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800">
                            @if($template->logo_path)
                                <img src="{{ asset('storage/' . $template->logo_path) }}" alt="Logo" class="h-24 w-auto mb-4 object-contain">
                            @else
                                <img src="{{ asset('images/Deped Logo.png') }}" alt="Default Logo" class="h-24 w-auto mb-4 object-contain">
                            @endif
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-black tracking-widest">Active Logo</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Upload New Logo</label>
                            <input type="file" name="logo" class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-gray-100 dark:file:bg-gray-800 file:text-gray-700 dark:file:text-gray-300 hover:file:bg-gray-200 transition">
                            <span class="block text-[10px] text-gray-400 mt-2">Recommended: PNG format with transparent background (Max 2MB).</span>
                        </div>
                    </div>

                    {{-- Footer Upload --}}
                    <div class="bg-white dark:bg-gray-900 p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm space-y-6">
                        <h3 class="text-base font-black text-gray-800 dark:text-gray-100 uppercase tracking-wider border-b border-gray-50 dark:border-gray-800 pb-3 flex items-center gap-2">
                            Footer Banner Image
                        </h3>
                        
                        <div class="flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800">
                            @if($template->footer_path)
                                <img src="{{ asset('storage/' . $template->footer_path) }}" alt="Footer Banner" class="h-16 w-full object-contain mb-4">
                            @else
                                <img src="{{ asset('images/certificate-footer.png') }}" alt="Default Footer" class="h-16 w-full object-contain mb-4">
                            @endif
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-black tracking-widest">Active Footer Banner</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Upload New Footer Banner</label>
                            <input type="file" name="footer" class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-gray-100 dark:file:bg-gray-800 file:text-gray-700 dark:file:text-gray-300 hover:file:bg-gray-200 transition">
                            <span class="block text-[10px] text-gray-400 mt-2">Recommended: Widescreen resolution PNG/JPG (Max 4MB).</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="bg-gradient-to-br from-gray-900 to-black p-6 sm:p-8 rounded-3xl shadow-lg border border-gray-800 flex flex-col gap-4">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-blue-400"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                            <h4 class="text-sm font-black text-white uppercase tracking-wider">Save Changes</h4>
                        </div>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            Once updated, all newly generated or exported Certificates of Completion will instantly reflect the modified templates and values.
                        </p>
                        <button type="submit" class="w-full bg-[#0B5694] hover:bg-blue-600 text-white rounded-2xl py-3.5 text-xs font-black uppercase tracking-widest shadow-md transition active:scale-95">
                            Publish Template
                        </button>
                    </div>

                </div>

            </div>
        </form>
    </div>
</x-page-layout>
@endsection
