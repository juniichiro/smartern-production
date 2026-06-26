<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyMFARequest;
use App\Mail\MfaEmailCodeMail;
use App\Models\User;
use App\Services\SecurityAuditService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Vinkla\Hashids\Facades\Hashids;

class MFAController extends Controller
{
    public function __construct(protected SecurityAuditService $auditService) {}

    /**
     * Show MFA setup/activation for the first time
     */
    public function setup(): View
    {
        $this->auditService->log('MFA Setup Started', 'MFA Settings');
        $user = Auth::user();

        $secret = $user->google2fa_secret;
        if (! $secret) {
            $secret = session('pending_google2fa_secret');
            if (! $secret) {
                $secret = Google2FA::generateSecretKey();
                session(['pending_google2fa_secret' => $secret]);
            }
        }

        $qrCodeUrl = Google2FA::getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('profile.mfa_setup', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $secret,
            'enabled' => (bool) $user->google2fa_enabled,
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $code = $request->input('one_time_password');

        $valid = false;

        // Check Google2FA TOTP (check user's saved secret or pending session secret)
        $totpSecret = $user->google2fa_secret ?: session('pending_google2fa_secret');
        if ($totpSecret && strlen($totpSecret) >= 16) {
            try {
                $valid = Google2FA::verifyKey($totpSecret, $code);
                if ($valid) {
                    $user->google2fa_secret = $totpSecret;
                }
            } catch (\Exception $e) {
                // Ignore key formatting exceptions
            }
        }

        // Check Cached Email Code if not validated via TOTP
        if (! $valid) {
            $cachedCode = Cache::get('mfa_email_code_'.$user->id);
            if ($cachedCode && hash_equals($cachedCode, $code)) {
                $valid = true;
                Cache::forget('mfa_email_code_'.$user->id);
                // Clear any unverified/pending TOTP secret since they chose Email
                $user->google2fa_secret = null;
            }
        }

        if ($valid) {
            $user->google2fa_enabled = true;
            $user->save();
            session()->forget('pending_google2fa_secret');

            session(['mfa_verified' => true]);

            $this->auditService->log('MFA Enabled', 'MFA Settings');

            return redirect()->intended(route('dashboard'))->with('status', 'mfa-enabled');
        }

        return redirect()->back()->withErrors(['one_time_password' => 'Invalid verification code. Please try again.']);
    }

    /**
     * Disable MFA
     */
    public function disable(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $user->google2fa_enabled = false;
        $user->google2fa_secret = null;
        $user->save();

        $this->auditService->log('MFA Disabled', 'MFA Settings');

        return redirect()->route('profile.edit')->with('status', 'mfa-disabled');
    }

    /**
     * Display the QR Code for MFA Scanning (Alternative to TOTP)
     */
    public function getLoginQR(): JsonResponse
    {
        $user = Auth::user();

        // Use Hashids to generate a compact, obfuscated token (ID + Timestamp)
        $token = Hashids::encode($user->id, now()->timestamp);

        $qrPayload = route('mfa.scan', ['token' => $token]);

        $qrCodeHtml = QrCode::size(250)
            ->color(11, 86, 148) // Brand Blue
            ->generate($qrPayload);

        return response()->json([
            'qr_html' => (string) $qrCodeHtml,
            'token' => $token,
        ]);
    }

    public function verifyView(Request $request): View
    {
        $user = Auth::user();
        $hasTotp = $user->google2fa_secret && strlen($user->google2fa_secret) >= 16;

        // Determine method (totp or email)
        $method = $request->query('method');
        if (! $method) {
            $method = $hasTotp ? 'totp' : 'email';
        }

        if ($method === 'email') {
            $lastSent = Cache::get('mfa_email_code_sent_at_'.$user->id);
            
            // Auto-send verification code if it hasn't been sent in the last 60 seconds
            if (!$lastSent || (time() - $lastSent) >= 60) {
                $code = sprintf('%06d', random_int(0, 999999));
                Cache::put('mfa_email_code_'.$user->id, $code, now()->addMinutes(5));
                Cache::put('mfa_email_code_sent_at_'.$user->id, time(), 60);

                Mail::to($user->email)->send(new MfaEmailCodeMail($user, $code));
                $lastSent = time();
            }

            $cooldown = $lastSent ? max(0, 60 - (time() - $lastSent)) : 0;

            return view('auth.mfa_verify_email', [
                'hasTotp' => $hasTotp,
                'cooldown' => $cooldown,
            ]);
        }

        // Default to TOTP
        $timeLeft = 30 - (time() % 30);

        return view('auth.mfa_verify_totp', [
            'timeLeft' => $timeLeft,
            'hasEmail' => true, // Every user has an email as fallback
        ]);
    }

    public function scanCode(Request $request)
    {
        try {
            $decoded = Hashids::decode($request->token);

            if (count($decoded) < 2) {
                return abort(403, 'Invalid token format or legacy link.');
            }

            [$userId, $timestamp] = $decoded;

            // Integrity Check: Prevent Replay Attacks (5 minutes expiry for the QR)
            $tokenTime = Carbon::createFromTimestamp($timestamp);
            if ($tokenTime->diffInMinutes(now()) > 5) {
                return abort(403, 'QR Code Expired. Please refresh the login page.');
            }

            $user = User::findOrFail($userId);

            // Auto-verify the session for this user
            Auth::login($user);
            session(['mfa_verified' => true]);

            $this->auditService->log('Authentication', 'MFA Verified QR Scan', $user->id);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return abort(403, 'Security validation failed.');
        }
    }

    /**
     * Send MFA verification code via Email
     */
    public function sendEmailCode(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $lastSent = Cache::get('mfa_email_code_sent_at_'.$user->id);
        if ($lastSent && (time() - $lastSent) < 60) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait '.(60 - (time() - $lastSent)).' seconds before requesting another code.',
            ], 429);
        }

        $code = sprintf('%06d', random_int(0, 999999));

        Cache::put('mfa_email_code_'.$user->id, $code, now()->addMinutes(5));
        Cache::put('mfa_email_code_sent_at_'.$user->id, time(), 60);

        Mail::to($user->email)->send(new MfaEmailCodeMail($user, $code));

        return response()->json([
            'success' => true,
            'message' => 'Verification code has been sent to your email.',
        ]);
    }

    /**
     * Standard TOTP and Email Code Verification
     */
    public function verify(VerifyMFARequest $request): RedirectResponse
    {
        $user = Auth::user();
        $secret = $request->input('one_time_password');

        $valid = false;

        // Check Google2FA TOTP
        if ($user->google2fa_secret && strlen($user->google2fa_secret) >= 16) {
            try {
                $valid = Google2FA::verifyKey($user->google2fa_secret, $secret);
            } catch (\Exception $e) {
                // Ignore key length or format exceptions
            }
        }

        // Check Cached Email Code if not validated via TOTP
        if (! $valid) {
            $cachedCode = Cache::get('mfa_email_code_'.$user->id);
            if ($cachedCode && hash_equals($cachedCode, $secret)) {
                $valid = true;
                Cache::forget('mfa_email_code_'.$user->id);
            }
        }

        if ($valid) {
            session(['mfa_verified' => true]);

            // Clear the cooldown so subsequent logins start with a fresh timer
            Cache::forget('mfa_email_code_sent_at_'.$user->id);

            $this->auditService->log('Authentication', 'MFA Verified Login');

            return redirect()->intended(route('dashboard'));
        }

        return redirect()->back()->withErrors(['one_time_password' => 'Invalid verification code. Please try again.']);
    }

    /**
     * Get MFA setup data (secret key and QR code) as JSON
     */
    public function getSetupData(): JsonResponse
    {
        $user = Auth::user();

        $secret = $user->google2fa_secret;
        if (! $secret) {
            $secret = session('pending_google2fa_secret');
            if (! $secret) {
                $secret = Google2FA::generateSecretKey();
                session(['pending_google2fa_secret' => $secret]);
            }
        }

        $qrCodeUrl = Google2FA::getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'secret' => $secret,
            'qrCodeInline' => $qrCodeUrl,
        ]);
    }

    /**
     * Enable TOTP MFA via AJAX
     */
    public function enableTotpAjax(Request $request): JsonResponse
    {
        $user = Auth::user();
        $code = $request->input('one_time_password');

        $totpSecret = $user->google2fa_secret ?: session('pending_google2fa_secret');
        if (! $totpSecret) {
            return response()->json(['success' => false, 'message' => 'No secret key generated.'], 400);
        }

        $valid = false;
        try {
            $valid = Google2FA::verifyKey($totpSecret, $code);
        } catch (\Exception $e) {
            // Ignore key format exceptions
        }

        if ($valid) {
            $user->google2fa_secret = $totpSecret;
            $user->google2fa_enabled = true;
            $user->save();
            session()->forget('pending_google2fa_secret');

            session(['mfa_verified' => true]);

            $this->auditService->log('MFA Enabled', 'MFA Settings (Authenticator App)');

            return response()->json(['success' => true, 'message' => 'Authenticator App enabled.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid OTP code. Please try again.'], 422);
    }

    /**
     * Disable TOTP MFA via AJAX
     */
    public function disableTotpAjax(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->google2fa_secret = null;
        $user->save();

        $this->auditService->log('MFA Disabled', 'MFA Settings (Authenticator App)');

        return response()->json(['success' => true, 'message' => 'Authenticator App disabled.']);
    }

    /**
     * Enable Email MFA via AJAX
     */
    public function enableEmailAjax(Request $request): JsonResponse
    {
        $user = Auth::user();
        $secret = $request->input('one_time_password');

        $cachedCode = Cache::get('mfa_email_code_'.$user->id);
        if ($cachedCode && hash_equals($cachedCode, $secret)) {
            Cache::forget('mfa_email_code_'.$user->id);
            session()->forget('pending_google2fa_secret');

            $user->google2fa_enabled = true;
            $user->save();

            session(['mfa_verified' => true]);

            $this->auditService->log('MFA Enabled', 'MFA Settings (Email)');

            return response()->json(['success' => true, 'message' => 'Email Verification enabled.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid verification code. Please try again.'], 422);
    }

    /**
     * Disable MFA completely via AJAX
     */
    public function disableMfaAjax(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->google2fa_enabled = false;
        $user->google2fa_secret = null;
        $user->save();

        $this->auditService->log('MFA Disabled', 'MFA Settings');

        return response()->json(['success' => true, 'message' => 'Two-Factor Authentication disabled.']);
    }
}
