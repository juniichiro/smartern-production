<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MFACheck
{
    /**
     * Handle an incoming request
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // TEMPORARY TEST BYPASS FOR AUTOMATION TESTING
        // return $next($request);

        $user = Auth::user();

        // If not logged in, just continue (auth middleware will handle it)
        if (! $user) {
            return $next($request);
        }

        // Test user bypasses MFA check completely for UI testing
        if ($user->email === 'test@smartern.com') {
            if (! session('mfa_verified')) {
                session(['mfa_verified' => true]);
            }
            return $next($request);
        }

        // Exclude MFA routes, password change, and logout from the check to avoid infinite loops
        $excludedRoutes = [
            'mfa.verify',
            'mfa.verify.post',
            'mfa.setup',
            'mfa.enable',
            'mfa.disable',
            'mfa.scan',
            'mfa.send-email-code',
            'mfa.setup-data',
            'mfa.enable-totp-ajax',
            'mfa.disable-totp-ajax',
            'mfa.enable-email-ajax',
            'mfa.disable-mfa-ajax',
            'profile.change-password',
            'profile.password.update',
            'logout',
            'attendance.verify',
            'attendance.process',
        ];

        if ($request->routeIs($excludedRoutes)) {
            return $next($request);
        }

        // If MFA is not verified in session
        if (! session('mfa_verified')) {
            // If they haven't finished the initial setup, force setup
            if (! $user->google2fa_enabled) {
                return redirect()->route('mfa.setup');
            }

            // If MFA is enabled and verified in DB, force verification for this session
            if ($user->google2fa_enabled) {
                return redirect()->route('mfa.verify');
            }
        }

        return $next($request);
    }
}
