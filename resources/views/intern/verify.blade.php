@extends('layouts.app')

@section('title', 'Verifying Attendance')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-gray-950" 
     x-data="attendanceScanner('{{ $token }}', '{{ route('attendance.process') }}')">
    
    {{-- Verification Hero --}}
    <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-[40px] shadow-2xl border border-gray-100 dark:border-gray-800 p-10 text-center relative overflow-hidden">
        {{-- Decorative Background --}}
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 dark:bg-blue-900/10 rounded-bl-full -z-0"></div>

        {{-- Icon/Status --}}
        <div class="relative z-10 mb-10">
            <template x-if="status === 'locating'">
                <div class="w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center text-[#0B5694] dark:text-blue-400 mx-auto border-4 border-blue-100 dark:border-blue-800 animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
            </template>

            <template x-if="status === 'processing'">
                <div class="w-24 h-24 bg-[#0B5694] rounded-full flex items-center justify-center text-white mx-auto border-4 border-blue-100 dark:border-blue-800 animate-spin">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/></svg>
                </div>
            </template>

            <template x-if="status === 'success'">
                <div class="w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-full flex items-center justify-center text-emerald-500 mx-auto border-4 border-emerald-100 dark:border-emerald-800 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
            </template>

            <template x-if="status === 'error'">
                <div class="w-24 h-24 bg-rose-50 dark:bg-rose-950/30 rounded-full flex items-center justify-center text-rose-500 mx-auto border-4 border-rose-100 dark:border-rose-900/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
            </template>
        </div>

        {{-- Text Content --}}
        <div class="relative z-10">
            <h3 class="text-2xl font-black text-gray-850 dark:text-white uppercase tracking-tight mb-3" x-text="title"></h3>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-[0.2em] leading-relaxed px-4" x-text="message"></p>
        </div>

        {{-- Manual Retry --}}
        <template x-if="status === 'error'">
            <button @click="startVerification" class="mt-10 w-full py-4 bg-[#0B5694] text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:scale-[1.02] active:scale-95 transition-all">
                Try Again
            </button>
        </template>
    </div>

    {{-- Security Footer --}}
    <div class="mt-8 flex items-center gap-3 opacity-50">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-gray-400"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span class="text-[9px] font-black text-gray-400 uppercase tracking-[0.3em]">End-to-End Secure Verification</span>
    </div>
</div>

<script>
    function attendanceScanner(token, processRoute) {
        return {
            status: 'locating', // locating, processing, success, error
            title: 'Verifying Location',
            message: 'Please allow access to your GPS to confirm you are within the office premises.',
            latitude: null,
            longitude: null,

            init() {
                this.startVerification();
            },

            startVerification() {
                this.status = 'locating';
                this.title = 'Verifying Location';
                this.message = 'Please allow access to your GPS to confirm you are within the office premises.';

                if (!navigator.geolocation) {
                    this.handleError('Geolocation is not supported by your browser.');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.latitude = position.coords.latitude;
                        this.longitude = position.coords.longitude;
                        this.processAttendance();
                    },
                    (error) => {
                        this.handleError('Location access denied. Please enable GPS and try again.');
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
                );
            },

            processAttendance() {
                this.status = 'processing';
                this.title = 'Processing Log';
                this.message = 'Confirming your identity and security token...';

                fetch(processRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        token: token,
                        latitude: this.latitude,
                        longitude: this.longitude
                    })
                })
                .then(async response => {
                    const data = await response.json();
                    if (response.ok) {
                        this.handleSuccess(data.message);
                    } else {
                        this.handleError(data.message || 'Verification failed.');
                    }
                })
                .catch(error => {
                    this.handleError('Network error. Please check your connection.');
                });
            },

            handleSuccess(msg) {
                this.status = 'success';
                this.title = 'Verified';
                this.message = msg;
            },

            handleError(msg) {
                this.status = 'error';
                this.title = 'Verification Failed';
                this.message = msg;
            }
        }
    }
</script>
@endsection
