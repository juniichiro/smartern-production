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

            // If the session has not been marked as verified in this session lifecycle yet,
            // register its finalized session ID into the active cache.
            if (!session()->has('session_verified')) {
                session(['session_verified' => true]);
                Cache::put('user-session-' . $user->id, session()->getId());
            } else {
                $cachedSessionId = Cache::get('user-session-' . $user->id);

                // If the cache contains a different session ID, this session is no longer active.
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
        }

        return $next($request);
    }
}
