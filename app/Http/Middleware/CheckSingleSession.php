<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
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
            // register its finalized session ID into the active cache and bind it to browser fingerprints.
            if (!session()->has('session_verified')) {
                $deviceId = Str::random(40);
                session([
                    'session_verified' => true,
                    'session_user_agent' => $request->userAgent(),
                    'session_ip' => $request->ip(),
                    'session_device_id' => $deviceId,
                    'session_client_id' => $request->cookie('smartern_client_id'),
                ]);
                Cookie::queue('smartern_device_id', $deviceId, 0, '/', null, true, true);
                Cache::put('user-session-' . $user->id, session()->getId());
            } else {
                // Prevent Session Hijacking/Cookie Injection:
                // Check if the User-Agent, IP, custom device ID cookie, and client ID cookie match.
                $cookieDeviceId = $request->cookie('smartern_device_id');
                $sessionDeviceId = session('session_device_id');
                $cookieClientId = $request->cookie('smartern_client_id');
                $sessionClientId = session('session_client_id');

                if (session('session_user_agent') !== $request->userAgent() || 
                    session('session_ip') !== $request->ip() ||
                    !$cookieDeviceId || 
                    $cookieDeviceId !== $sessionDeviceId ||
                    !$cookieClientId ||
                    $cookieClientId !== $sessionClientId) {
                    
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    // Queue deletion of the cookies to clean up
                    Cookie::queue(Cookie::forget('smartern_device_id'));
                    Cookie::queue(Cookie::forget('smartern_client_id'));

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'error' => 'session_taken_over',
                            'message' => 'Security warning: Session hijacking or duplicate session injection detected.'
                        ], 401);
                    }

                    return redirect()->route('login')->with('session_taken_over', true);
                }

                $cachedSessionId = Cache::get('user-session-' . $user->id);

                // If the cache contains a different session ID, this session is no longer active.
                if ($cachedSessionId && $cachedSessionId !== session()->getId()) {
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    Cookie::queue(Cookie::forget('smartern_device_id'));
                    Cookie::queue(Cookie::forget('smartern_client_id'));

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
