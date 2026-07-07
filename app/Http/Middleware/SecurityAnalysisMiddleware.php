<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SecurityAnalysisMiddleware
{
    /**
     * Handle an incoming request
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            // Lock check: block and force log out if locked_until is set in the future
            if ($user->locked_until && now()->lessThan($user->locked_until)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Your account has been locked due to suspicious activity. Please submit a formal request letter for account reactivation.',
                    ], 403);
                }

                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been locked due to suspicious activity. Please submit a formal request letter for account reactivation.',
                ]);
            }
        }

        // Only apply to authenticated users and GET requests
        if (! $user || ! $request->isMethod('get')) {
            return $next($request);
        }

        // Exclude specific routes to avoid loops and allow analysis/logout
        $excludedRoutes = [
            'security.run',
            'logout',
            'mfa.*',
            'notifications.*',
            'profile.*',
            'onboarding.*',
            'attendance.verify',
            'attendance.process',
        ];

        foreach ($excludedRoutes as $pattern) {
            if ($request->is($pattern) || $request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // Check if security analysis has been run in this session
        if (! session('security_analysis_run') && ! $request->routeIs('attendance.*')) {
            return response()->view('auth.skeleton-loading', [
                'redirect_to' => $request->fullUrl(),
            ]);
        }

        return $next($request);
    }
}
