<?php

use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\MFACheck;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityAnalysisMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'attendance/process',
        ]);

        $middleware->encryptCookies(except: [
            'smartern_client_id',
        ]);

        $middleware->web(append: [
            AuthenticateSession::class,
            \App\Http\Middleware\CheckSingleSession::class,
            ForcePasswordChange::class,
            MFACheck::class,
            SecurityAnalysisMiddleware::class,
            PreventBackHistory::class,
        ]);
        $middleware->redirectTo(
            guests: '/login',
            users: '/dashboard'
        );

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
