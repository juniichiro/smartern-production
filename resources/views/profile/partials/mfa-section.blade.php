<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Two-Factor Authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Add an extra layer of security to your account by enabling Two-Factor Authentication.') }}
        </p>
    </header>

    <div class="mt-6">
        @if(auth()->user()->google2fa_enabled)
            <div class="flex items-center gap-4">
                <div class="px-4 py-2 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-xl border border-emerald-100 dark:border-emerald-900/50 text-xs font-black uppercase tracking-widest">
                    Active
                </div>
                <a href="{{ route('mfa.setup') }}" class="text-sm font-bold text-[#0B5694] dark:text-blue-400 hover:underline">
                    Manage MFA Settings
                </a>
            </div>
        @else
            <x-primary-button onclick="window.location.href='{{ route('mfa.setup') }}'">
                {{ __('Enable MFA') }}
            </x-primary-button>
        @endif
    </div>

    @if (session('status') === 'mfa-enabled')
        <p
            x-data="uiNotification(2000)"
            class="mt-4 text-sm text-emerald-600 font-bold"
        >{{ __('MFA has been successfully enabled.') }}</p>
    @endif

    @if (session('status') === 'mfa-disabled')
        <p
            x-data="uiNotification(2000)"
            class="mt-4 text-sm text-rose-600 font-bold"
        >{{ __('MFA has been deactivated.') }}</p>
    @endif
</section>
