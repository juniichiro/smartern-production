<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Check if the user is registered in the system
        $userExists = \App\Models\User::where('email', $request->email)->exists();

        if ($userExists) {
            // Send the password reset link to this user
            Password::sendResetLink(
                $request->only('email')
            );
        }

        // Always return success status to prevent email enumeration/discovery
        return back()->with('status', __(Password::RESET_LINK_SENT));
    }
}
