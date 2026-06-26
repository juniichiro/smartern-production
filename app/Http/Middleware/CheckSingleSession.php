<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckSingleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $cachedSessionId = Cache::get('user-session-' . $user->id);

            // If the cache has a session ID and it doesn't match the current session ID,
            // it means the user logged in elsewhere.
            if ($cachedSessionId && $cachedSessionId !== session()->getId()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'error' => 'session_taken_over',
                        'message' => 'Your account has been logged in from another device/browser.'
                    ], 401);
                }

                return redirect()->route('login')->with('session_taken_over', true);
            }
        }

        return $next($request);
    }
}
