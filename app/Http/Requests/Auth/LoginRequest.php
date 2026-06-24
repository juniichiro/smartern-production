<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    // App\Http\Requests\Auth\LoginRequest.php

    public function authenticate(): void
    {
        // Make sure we are actually finding the user, bypassing global scopes to support Super Admin!
        $user = User::withoutGlobalScopes()->where('email', $this->email)->first();

        // If the user isn't found, the logic below never runs.
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // LOCKOUT CHECK
        if ($user->locked_until && now()->lessThan($user->locked_until)) {
            $isPermanent = $user->locked_until->year >= 9999;
            
            if ($isPermanent) {
                throw ValidationException::withMessages([
                    'email' => "Your account is currently deactivated. Please submit a formal request letter to the administration to reactivate your access.",
                ]);
            }

            if ($user->failed_attempts >= 5) {
                $diff = ceil(now()->diffInSeconds($user->locked_until) / 60);
                throw ValidationException::withMessages([
                    'email' => "Account locked due to multiple failed login attempts. Please try again in {$diff} minutes.",
                ]);
            } else {
                throw ValidationException::withMessages([
                    'email' => "Your account has been locked due to suspicious activity. Please submit a formal request letter to the administration to reactivate your access.",
                ]);
            }
        }

        if (! \Illuminate\Support\Facades\Hash::check($this->password, $user->password)) {
            // INCREMENT LOGIC
            $user->increment('failed_attempts');

            // Refresh the user instance to get the updated count from DB
            $user->refresh();

            if ($user->failed_attempts >= 5) {
                $user->update([
                    'locked_until' => now()->addMinutes(30),
                ]);
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Log the user in directly, bypassing global scopes on the user provider
        Auth::login($user, $this->boolean('remember'));

        // SUCCESS RESET
        $user->update([
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
