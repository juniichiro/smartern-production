@extends('layouts.app')

@section('title', 'Verify MFA - Authenticator App')

@section('content')
<div class="min-h-screen w-full flex items-center justify-center relative overflow-hidden bg-gray-50 dark:bg-gray-950 transition-colors duration-300"
     x-data="{
         timeLeft: {{ $timeLeft }},
         init() {
             setInterval(() => {
                 if (this.timeLeft > -2) {
                     this.timeLeft--;
                 } else {
                     window.location.reload();
                 }
             }, 1000);
         }
     }">
    
    {{-- Dark Mode Toggle --}}
    <div class="absolute top-6 right-6 z-[100]" x-data="darkModeManager">
        <button @click="darkMode = !darkMode" 
                class="p-3 bg-white/90 dark:bg-white/10 backdrop-blur-md border border-gray-200 dark:border-white/10 rounded-2xl text-[#0B5694] dark:text-white hover:bg-white dark:hover:bg-white/20 transition-all active:scale-95 shadow-xl">
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
    </div>

    {{-- Background Design --}}
    <div class="absolute inset-0 z-0">
        <div class="absolute left-0 top-0 w-1/2 h-full bg-[#0B5694] dark:bg-blue-900 opacity-90 dark:opacity-80 skew-x-[-12deg] -translate-x-32 transition-colors duration-300 hidden md:block"></div>
        <div class="absolute right-0 top-0 w-full h-full bg-cover bg-center" 
             style="background-image: url('/images/school-bg.jpg'); filter: brightness(0.7);"></div>
    </div>

    {{-- MFA CARD --}}
    <div class="relative z-10 w-full max-w-[450px] bg-white dark:bg-gray-900 rounded-[28px] xs:rounded-[36px] sm:rounded-[40px] shadow-2xl p-6 xs:p-8 sm:p-10 md:p-12 mx-4 transition-all duration-300 border border-transparent dark:border-gray-800">
        
        <header class="text-center mb-8 sm:mb-10">
            <h1 class="text-2xl xs:text-3xl sm:text-4xl font-extrabold text-[#0B5694] dark:text-blue-400 tracking-normal transition-colors duration-300">Identity Check</h1>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-2 font-bold uppercase tracking-wider leading-relaxed">
                Enter the 6-digit code from your <br>
                <span class="text-[#0B5694] dark:text-blue-400">Authenticator App</span>
            </p>
        </header>

        {{-- Refresh Indicator --}}
        <div class="mb-8 px-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Code Refreshing In</span>
                <span class="text-xs font-bold text-[#0B5694] dark:text-blue-400" x-text="(timeLeft > 0 ? timeLeft : 0) + 's'"></span>
            </div>
            <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden mb-2">
                <div class="h-full bg-[#0B5694] dark:bg-blue-500 transition-all duration-1000 ease-linear"
                     :style="'width: ' + (Math.max(0, timeLeft) / 30 * 100) + '%'"></div>
            </div>
            <div class="text-center">
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight">System Time: {{ date('h:i:s A') }}</p>
            </div>
        </div>

        {{-- DYNAMIC ERROR FEEDBACK --}}
        @if ($errors->any())
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-8 p-4 bg-red-50 dark:bg-red-950/30 rounded-2xl border border-red-100 dark:border-red-900/50 flex items-center gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-red-500 rounded-full flex items-center justify-center shadow-lg shadow-red-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="text-white" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </div>
                <div>
                    <p class="text-[12px] font-bold text-red-600 dark:text-red-400 uppercase tracking-wider leading-none mb-1">Invalid Code</p>
                    <p class="text-[12px] text-red-400 dark:text-red-500 font-bold uppercase tracking-tight">Please try again.</p>
                </div>
            </div>
        @endif

        <form action="{{ route('mfa.verify.post') }}" method="POST" class="space-y-6"
              x-data="{
                  digits: ['', '', '', '', '', ''],
                  submitting: false,
                  focusNext(el, index) {
                      el.value = el.value.replace(/\D/g, '');
                      if (el.value.length > 1) {
                          el.value = el.value.slice(-1);
                      }
                      this.digits[index] = el.value;
                      
                      if (el.value && index < 5) {
                          let nextEl = this.$refs['input' + (index + 1)];
                          if (nextEl) {
                              nextEl.focus();
                              nextEl.select();
                          }
                      }
                      this.updateHiddenInput();
                  },
                  handleKeydown(e, index) {
                      if (e.key === 'Backspace') {
                          if (!this.digits[index] && index > 0) {
                              this.digits[index - 1] = '';
                              let prevEl = this.$refs['input' + (index - 1)];
                              if (prevEl) {
                                  prevEl.focus();
                                  prevEl.select();
                              }
                              e.preventDefault();
                          } else {
                              this.digits[index] = '';
                          }
                          this.updateHiddenInput();
                      } else if (e.key === 'ArrowLeft' && index > 0) {
                          let prevEl = this.$refs['input' + (index - 1)];
                          if (prevEl) {
                              prevEl.focus();
                              prevEl.select();
                              e.preventDefault();
                          }
                      } else if (e.key === 'ArrowRight' && index < 5) {
                          let nextEl = this.$refs['input' + (index + 1)];
                          if (nextEl) {
                              nextEl.focus();
                              nextEl.select();
                              e.preventDefault();
                          }
                      }
                  },
                  handlePaste(e) {
                      e.preventDefault();
                      let pasteData = (e.clipboardData || window.clipboardData).getData('text');
                      let numbers = pasteData.replace(/\D/g, '').substring(0, 6);
                      for (let i = 0; i < 6; i++) {
                          this.digits[i] = numbers[i] || '';
                      }
                      this.updateHiddenInput();
                      
                      let focusIndex = Math.min(numbers.length, 5);
                      let targetEl = this.$refs['input' + focusIndex];
                      if (targetEl) {
                          targetEl.focus();
                      }
                  },
                  updateHiddenInput() {
                      this.$refs.hiddenInput.value = this.digits.join('');
                      this.$refs.hiddenInput.dispatchEvent(new Event('input'));
                  },
                  init() {
                      this.$nextTick(() => {
                          if (this.$refs.input0) {
                              this.$refs.input0.focus();
                          }
                      });
                  }
              }"
              @submit="if (submitting) { $event.preventDefault(); } else { submitting = true; }"
        >
            @csrf

            {{-- OTP Code --}}
            <div class="text-left">
                <label class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-wider ml-1 text-center">Authenticator Code</label>
                
                {{-- Hidden Input for form submission --}}
                <input type="hidden" name="one_time_password" x-ref="hiddenInput" required>

                {{-- Grid of 6 inputs --}}
                <div class="grid grid-cols-6 gap-1 xs:gap-1.5 sm:gap-3">
                    @for($i = 0; $i < 6; $i++)
                        <input type="text"
                               x-ref="input{{ $i }}"
                               x-model="digits[{{ $i }}]"
                               @input="focusNext($el, {{ $i }})"
                               @keydown="handleKeydown($event, {{ $i }})"
                               @paste="handlePaste($event)"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               maxlength="2"
                               class="w-full h-11 xs:h-13 sm:h-16 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl xs:rounded-2xl text-center text-xl sm:text-2xl font-extrabold text-[#0B5694] dark:text-blue-400 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 focus:border-[#0B5694] outline-none transition-all shadow-sm select-all"
                               required>
                    @endfor
                </div>
            </div>

            {{-- Verify Button --}}
            <div class="pt-4">
                <button type="submit" 
                        :disabled="submitting"
                        class="w-full bg-[#0B5694] dark:bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-blue-500/20 hover:bg-[#084577] dark:hover:bg-blue-700 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                    <template x-if="submitting">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="submitting ? 'Verifying...' : 'Verify Code'"></span>
                </button>
            </div>
        </form>

        @if($hasEmail)
        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 text-center">
            <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">
                Having trouble with your app?
            </p>
            <a href="{{ route('mfa.verify', ['method' => 'email']) }}"
               class="mt-2 inline-flex items-center gap-2 text-xs font-bold text-[#0B5694] dark:text-blue-400 hover:text-[#084577] dark:hover:text-blue-300 transition-colors uppercase tracking-wider">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                Verify using Email instead
            </a>
        </div>
        @endif

        <div class="mt-8 text-center">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs font-bold text-gray-300 dark:text-gray-600 hover:text-red-500 transition-colors uppercase tracking-wider">Cancel and Sign Out</button>
            </form>
        </div>
    </div>
</div>
@endsection
