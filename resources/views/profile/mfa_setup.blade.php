@extends('layouts.app')

@section('title', 'MFA Setup')

@section('content')
<div class="flex min-h-screen bg-gray-50 dark:bg-gray-950 font-sans text-[14px]">
    <x-sidebar portalType="User" 
               userName="{{ Auth::user()->email }}" 
               userRole="Security Settings"
               :setupMode="true" />

    <main class="flex-1 ml-0 lg:ml-64 mr-0 lg:mr-80 p-4 md:p-6 lg:p-10 pt-20 lg:pt-10 text-left transition-all duration-300">
        <header class="mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
            <div>
                <h2 class="text-2xl md:text-4xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">Security</h2>
                <p class="text-base text-gray-400 dark:text-gray-500 font-medium italic mt-1">Enhance your account security with Two-Factor Authentication</p>
            </div>
            
            {{-- Header Theme Toggle --}}
            <div x-data="darkModeManager">
                <button @click="darkMode = !darkMode" 
                        class="p-3 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl text-gray-400 hover:text-[#0B5694] dark:hover:text-blue-400 transition-all active:scale-95 shadow-sm">
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                    </svg>
                    <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>
            </div>
        </header>

        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-900 rounded-[35px] shadow-sm border border-gray-100 dark:border-gray-800 p-8 lg:p-12 relative overflow-hidden"
                 x-data="{
                     activeMethod: 'totp',
                     emailSent: false,
                     emailSending: false,
                     statusMessage: '',
                     statusType: 'success',
                     sendEmailCode() {
                         if (this.emailSending) return;
                         this.emailSending = true;
                         this.statusMessage = 'Sending code...';
                         this.statusType = 'info';
                         
                         fetch('{{ route('mfa.send-email-code') }}', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                             }
                         })
                         .then(response => response.json().then(data => ({ status: response.status, body: data })))
                         .then(res => {
                             this.emailSending = false;
                             if (res.status === 200 && res.body.success) {
                                 this.emailSent = true;
                                 this.statusMessage = res.body.message;
                                 this.statusType = 'success';
                             } else {
                                 this.statusMessage = res.body.message || 'Failed to send code.';
                                 this.statusType = 'error';
                             }
                         })
                         .catch(error => {
                             this.emailSending = false;
                             this.statusMessage = 'Failed to send code.';
                             this.statusType = 'error';
                         });
                     }
                 }">
                <div class="flex items-center gap-6 mb-10">
                    <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center rounded-[22px] text-[#0B5694] dark:text-blue-400 border-2 border-white dark:border-gray-800 shadow-xl transition-all">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-gray-800 dark:text-gray-100 text-xl md:text-2xl tracking-normal uppercase">Two-Factor Authentication</h3>
                        <p class="text-sm md:text-base text-gray-400 dark:text-gray-500 font-medium italic mt-1">Protect your account with an extra verification method</p>
                    </div>
                </div>

                @if(!$enabled)
                    {{-- TAB CONTROLLERS --}}
                    <div class="flex gap-4 mb-8 border-b border-gray-100 dark:border-gray-800 pb-4">
                        <button type="button" 
                                @click="activeMethod = 'totp'"
                                :class="activeMethod === 'totp' ? 'text-[#0B5694] border-[#0B5694] dark:text-blue-400 dark:border-blue-400' : 'text-gray-400 border-transparent hover:text-gray-600'"
                                class="pb-2 border-b-2 font-bold uppercase tracking-wider text-xs transition-all focus:outline-none">
                            Authenticator App (TOTP)
                        </button>
                        <button type="button" 
                                @click="activeMethod = 'email'"
                                :class="activeMethod === 'email' ? 'text-[#0B5694] border-[#0B5694] dark:text-blue-400 dark:border-blue-400' : 'text-gray-400 border-transparent hover:text-gray-600'"
                                class="pb-2 border-b-2 font-bold uppercase tracking-wider text-xs transition-all focus:outline-none">
                            Email OTP Code
                        </button>
                    </div>

                    {{-- DYNAMIC EMAIL STATUS MESSAGE --}}
                    <template x-if="statusMessage && activeMethod === 'email'">
                        <div class="mb-8 p-4 rounded-2xl border flex items-center gap-4 transition-all duration-300 text-left"
                             :class="{
                                 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-100 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-300': statusType === 'success',
                                 'bg-blue-50 dark:bg-blue-950/30 border-blue-100 dark:border-blue-900/50 text-blue-800 dark:text-blue-300': statusType === 'info',
                                 'bg-red-50 dark:bg-red-950/30 border-red-100 dark:border-red-900/50 text-red-800 dark:text-red-300': statusType === 'error'
                             }">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center shadow-lg"
                                 :class="{
                                     'bg-emerald-500 text-white shadow-emerald-500/20': statusType === 'success',
                                     'bg-blue-500 text-white shadow-blue-500/20': statusType === 'info',
                                     'bg-red-500 text-white shadow-red-500/20': statusType === 'error'
                                 }">
                                <template x-if="statusType === 'success'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </template>
                                <template x-if="statusType === 'info'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                </template>
                                <template x-if="statusType === 'error'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </template>
                            </div>
                            <div>
                                <p class="text-[12px] font-bold uppercase tracking-wider leading-none mb-1"
                                   x-text="statusType === 'success' ? 'Email Code Sent' : (statusType === 'info' ? 'Sending Code' : 'Error')"></p>
                                <p class="text-[12px] font-medium tracking-tight" x-text="statusMessage"></p>
                            </div>
                        </div>
                    </template>

                    {{-- TAB 1: AUTHENTICATOR APP (TOTP) --}}
                    <div x-show="activeMethod === 'totp'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                            <div class="space-y-8">
                                <div class="flex gap-4">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center font-bold text-xs shrink-0">1</div>
                                    <p class="text-gray-600 dark:text-gray-400 font-medium leading-relaxed">
                                        Download a mobile authenticator app like <span class="text-[#0B5694] dark:text-blue-400 font-bold">Google Authenticator</span> or <span class="text-[#0B5694] dark:text-blue-400 font-bold">Microsoft Authenticator</span>.
                                    </p>
                                </div>
                                <div class="flex gap-4">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center font-bold text-xs shrink-0">2</div>
                                    <p class="text-gray-600 dark:text-gray-400 font-medium leading-relaxed">
                                        Scan the QR code on the right with your app, or manually enter the secret key: <br>
                                        <span class="inline-block mt-2 font-mono text-sm bg-gray-50 dark:bg-gray-800 px-4 py-2 rounded-lg border border-gray-100 dark:border-gray-700 text-[#0B5694] dark:text-blue-400 font-bold tracking-wider">{{ $secret }}</span>
                                    </p>
                                </div>
                                <div class="flex gap-4">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center font-bold text-xs shrink-0">3</div>
                                    <div class="space-y-1">
                                        <p class="text-gray-600 dark:text-gray-400 font-medium leading-relaxed">
                                            Enter the 6-digit code generated by your app below to verify and enable MFA.
                                        </p>
                                    </div>
                                </div>

                                <form action="{{ route('mfa.enable') }}" method="POST" class="pt-6"
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
                                          }
                                      }"
                                      @submit="if (submitting) { $event.preventDefault(); } else { submitting = true; }"
                                >
                                    @csrf
                                    <div class="space-y-4">
                                        <div class="text-left">
                                            <label class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-wider ml-1 text-center">Verification Code</label>
                                            <input type="hidden" name="one_time_password" x-ref="hiddenInput" required>
                                            <div class="grid grid-cols-6 gap-2 sm:gap-3">
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
                                                           class="w-full h-14 sm:h-16 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl text-center text-2xl font-extrabold text-[#0B5694] dark:text-blue-400 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 focus:border-[#0B5694] outline-none transition-all shadow-sm select-all"
                                                           required>
                                                @endfor
                                            </div>
                                        </div>
                                        @if($errors->has('one_time_password'))
                                            <p class="text-xs text-red-500 font-bold italic ml-1">{{ $errors->first('one_time_password') }}</p>
                                        @endif
                                    </div>
                                    <button type="submit" 
                                            :disabled="submitting"
                                            class="w-full mt-6 bg-[#0B5694] dark:bg-blue-600 text-white py-5 rounded-2xl font-bold text-sm uppercase tracking-wider shadow-xl hover:bg-[#084577] dark:hover:bg-blue-700 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                                        <template x-if="submitting">
                                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <span x-text="submitting ? 'Enabling...' : 'Enable MFA'"></span>
                                    </button>
                                </form>
                            </div>

                            <div class="flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-800/30 rounded-[30px] p-10 border border-gray-100 dark:border-gray-800 shadow-inner">
                                <div class="bg-white p-6 rounded-3xl shadow-xl flex items-center justify-center">
                                    {!! $qrCodeUrl !!}
                                </div>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-8">Scan QR Code</p>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: EMAIL VERIFICATION --}}
                    <div x-show="activeMethod === 'email'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="max-w-xl mx-auto space-y-8">
                            <div class="bg-gray-50 dark:bg-gray-800/30 rounded-[30px] p-8 border border-gray-100 dark:border-gray-800 text-center">
                                <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center rounded-[22px] text-[#0B5694] dark:text-blue-400 border-2 border-white dark:border-gray-800 shadow-xl transition-all mx-auto mb-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                </div>
                                <h4 class="font-extrabold text-gray-800 dark:text-gray-100 text-lg uppercase tracking-normal">Setup Email MFA</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">
                                    We will send a 6-digit one-time passcode to your registered email address:<br>
                                    <span class="text-[#0B5694] dark:text-blue-400 font-bold">{{ Auth::user()->email }}</span>
                                </p>
                                
                                <button type="button"
                                        @click="sendEmailCode()"
                                        :disabled="emailSending"
                                        class="mt-6 bg-[#0B5694] dark:bg-blue-600 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg hover:bg-[#084577] dark:hover:bg-blue-700 transition-all active:scale-95 disabled:opacity-50">
                                    <span x-text="emailSending ? 'Sending Code...' : (emailSent ? 'Resend Verification Code' : 'Send Verification Code')"></span>
                                </button>
                            </div>

                            <form action="{{ route('mfa.enable') }}" method="POST" class="pt-2"
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
                                      }
                                  }"
                                  @submit="if (submitting) { $event.preventDefault(); } else { submitting = true; }"
                            >
                                @csrf
                                <div class="space-y-4">
                                    <div class="text-left">
                                        <label class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase block mb-3 tracking-wider ml-1 text-center">Verification Code</label>
                                        <input type="hidden" name="one_time_password" x-ref="hiddenInput" required>
                                        <div class="grid grid-cols-6 gap-2 sm:gap-3">
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
                                                       class="w-full h-14 sm:h-16 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl text-center text-2xl font-extrabold text-[#0B5694] dark:text-blue-400 focus:ring-2 focus:ring-[#0B5694] dark:focus:ring-blue-500 focus:border-[#0B5694] outline-none transition-all shadow-sm select-all"
                                                       required>
                                            @endfor
                                        </div>
                                    </div>
                                    @if($errors->has('one_time_password'))
                                        <p class="text-xs text-red-500 font-bold italic ml-1">{{ $errors->first('one_time_password') }}</p>
                                    @endif
                                </div>
                                <button type="submit" 
                                        :disabled="submitting || !emailSent"
                                        class="w-full mt-6 bg-[#0B5694] dark:bg-blue-600 text-white py-5 rounded-2xl font-bold text-sm uppercase tracking-wider shadow-xl hover:bg-[#084577] dark:hover:bg-blue-700 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                                    <template x-if="submitting">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <span x-text="submitting ? 'Enabling...' : 'Enable MFA'"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 rounded-[30px] p-10 text-center">
                        <div class="w-20 h-20 bg-emerald-500 text-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl shadow-emerald-500/20">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <h4 class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400 uppercase tracking-normal">MFA is Active</h4>
                        <p class="text-sm text-emerald-600/70 dark:text-emerald-500/70 mt-2 font-bold italic">Your account is protected by Two-Factor Authentication.</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <x-notification-sidebar :notifications="$notifications ?? []" />
</div>
@endsection
