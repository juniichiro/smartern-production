<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Redirect if it's the first login AND they aren't already on the change password page
        if ($user && $user->is_first_login &&
            ! $request->is('profile/change-password*') &&
            ! $request->routeIs('logout') &&
            ! $request->routeIs('attendance.verify') &&
            ! $request->routeIs('attendance.process')) {

            return redirect()->route('profile.change-password')
                ->with('info', 'Please change your default password to continue.');
        }

        return $next($request);
    }
}
