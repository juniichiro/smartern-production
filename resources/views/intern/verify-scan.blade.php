@extends('layouts.app')

@section('title', 'Verify Attendance')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#0B5694] dark:bg-gray-950 text-white p-6">
    <div class="text-center w-full max-w-sm">
        <div id="status-icon" class="mb-6">
            <div class="w-16 h-16 border-4 border-white/20 border-t-white rounded-full animate-spin mx-auto"></div>
        </div>
        <h2 id="status-text" class="text-xl font-bold uppercase tracking-widest">Verifying Attendance</h2>
        <p id="message" class="text-white/60 dark:text-gray-400 text-sm mt-4 italic">Communicating with server...</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const token = new URLSearchParams(window.location.search).get('token');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    sendAttendance(token, position.coords.latitude, position.coords.longitude);
                },
                (error) => {
                    console.warn("GPS Denied or Unavailable, proceeding without location data.");
                    sendAttendance(token, null, null);
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
            );
        } else {
            sendAttendance(token, null, null);
        }

        function closeWindow() {
            window.close();
            setTimeout(() => {
                if (!window.closed) {
                    document.getElementById('message').innerHTML = `
                        <p class="mb-6 font-bold text-white/80 uppercase tracking-wider text-[10px] leading-relaxed">
                            Your browser prevented the window from closing automatically.<br>
                            You can safely close this tab now.
                        </p>
                        <button onclick="window.location.href='/intern/dashboard'" class="px-8 py-3 bg-white/10 text-white rounded-xl font-bold text-xs uppercase tracking-wider border border-white/20">
                            Return to Dashboard
                        </button>
                    `;
                }
            }, 500);
        }

        function sendAttendance(token, lat, lng, confirm_early_checkout = false, early_checkout_reason = '') {
            document.getElementById('status-text').innerText = "Processing...";
            if (confirm_early_checkout) {
                document.getElementById('status-icon').innerHTML = `
                    <div class="w-16 h-16 border-4 border-white/20 border-t-white rounded-full animate-spin mx-auto"></div>
                `;
            }

            fetch("{{ route('attendance.process') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ 
                    token, 
                    latitude: lat, 
                    longitude: lng, 
                    confirm_early_checkout, 
                    early_checkout_reason
                })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(data => { throw new Error(data.message || 'Attendance Failed') });
                }
                return res.json();
            })
            .then(data => {
                if (data.requires_confirmation) {
                    document.getElementById('status-icon').innerHTML = `
                        <div class="w-20 h-20 bg-yellow-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-yellow-500/40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#eab308" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12" y1="16" y2="16.01"/></svg>
                        </div>
                    `;
                    document.getElementById('status-text').innerText = "Early Time-Out Warning";
                    
                    let reasonInputHtml = '';
                    if (data.requires_reason) {
                        reasonInputHtml = `
                            <div class="mb-6 text-left">
                                <label class="block text-[10px] font-bold text-white/40 uppercase tracking-wider mb-2 px-1">Reason for Early Time-Out</label>
                                <textarea id="early-reason" 
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-white/20 focus:outline-none focus:border-yellow-500/50 transition-all resize-none" 
                                    rows="3" 
                                    placeholder="Briefly explain why you are leaving early..."></textarea>
                            </div>
                        `;
                    }

                    document.getElementById('message').innerHTML = `
                        <p class="mb-6 text-yellow-200 font-medium text-sm leading-relaxed">${data.message}</p>
                        ${reasonInputHtml}
                        <div class="flex flex-col gap-3">
                            <button id="btn-confirm-early" class="w-full py-3.5 bg-yellow-500 hover:bg-yellow-600 text-gray-950 rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg transition-colors">
                                Confirm Early Time-Out
                            </button>
                            <button onclick="window.location.href='/intern/dashboard'" class="w-full py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold text-xs uppercase tracking-wider border border-white/20 transition-colors">
                                Cancel & Keep Working
                            </button>
                        </div>
                    `;
                    
                    document.getElementById('btn-confirm-early').addEventListener('click', function() {
                        const reason = document.getElementById('early-reason') ? document.getElementById('early-reason').value : '';
                        sendAttendance(token, lat, lng, true, reason);
                    });
                    return;
                }

                document.getElementById('status-icon').innerHTML = `
                    <div class="w-20 h-20 bg-white/10 dark:bg-gray-800/50 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-white/20 dark:border-gray-850">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                `;
                document.getElementById('status-text').innerText = "Recorded!";
                document.getElementById('message').innerHTML = `
                    <p class="mb-6 font-bold text-emerald-400 uppercase tracking-wider text-xs">${data.message}</p>
                    <button onclick="closeWindow()" class="px-8 py-3 bg-white text-[#0B5694] rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg">
                        Close Window
                    </button>
                `;
                setTimeout(() => { closeWindow(); }, 3000);
            })
            .catch(err => {
                document.getElementById('status-icon').innerHTML = `
                    <div class="w-20 h-20 bg-rose-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-rose-500/40">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </div>
                `;
                document.getElementById('status-text').innerText = "Attendance Failed";
                document.getElementById('message').innerHTML = `
                    <p class="mb-8 text-rose-200 font-medium text-sm leading-relaxed">${err.message}</p>
                    <p class="text-[10px] text-white/30 uppercase tracking-wider font-bold italic">Please re-scan the QR code once you are at the office</p>
                `;
            });
        }
    });
</script>
@endsection
