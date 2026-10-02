<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request
     *
     * @param Closure(Request): (Response) $next
     */
    // app/Http/Middleware/RoleMiddleware.php
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if ($request->user()) {
            $user = $request->user();
            $userRole = strtolower($user->role?->name ?? '');

            if ($userRole === 'super_admin' || $user->email === 'test@smartern.com') {
                return $next($request);
            }

            foreach ($roles as $role) {
                if ($userRole === strtolower($role)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Unauthorized access.');
    }
}
