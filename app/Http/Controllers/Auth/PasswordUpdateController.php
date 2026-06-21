<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordUpdateRequest;
use Illuminate\Support\Facades\Hash;

class PasswordUpdateController extends Controller
{
    // Show the form
    public function edit()
    {
        return view('auth.change-password');
    }

    // Process the update
    public function update(PasswordUpdateRequest $request)
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->password),
            'is_first_login' => false, // This is the crucial part!
        ]);

        // Redirect based on role
        $url = match ($user->role->name) {
            'admin' => '/admin/dashboard',
            'supervisor', 'head_supervisor' => '/supervisor/dashboard',
            'intern' => '/intern/dashboard',
            default => '/dashboard',
        };

        return redirect($url)->with('success', 'Security details refined. Welcome to SmarTern!');
    }
}
