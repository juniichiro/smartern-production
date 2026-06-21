<?php

namespace App\Listeners;

use App\Services\SecurityAuditService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;

class SecurityAuditListener
{
    protected $auditService;

    public function __construct(SecurityAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function handleLogin(Login $event)
    {
        // Only log immediate authentication if MFA is NOT enabled.
        // If MFA is enabled, the actual 'Authentication' log is created
        // upon successful MFA verification in MFAController.
        if (! $event->user->google2fa_enabled) {
            $this->auditService->log('Authentication', 'System Authentication', $event->user->id);
        }
    }

    public function handleFailed(Failed $event)
    {
        $resource = 'Login Attempt: '.($event->credentials['email'] ?? 'Unknown');
        $userId = $event->user ? $event->user->id : 1; // Default to ID 1 (Admin) for failed attempts if user not found

        $this->auditService->log('Failed Authentication', $resource, $userId);
    }
}
