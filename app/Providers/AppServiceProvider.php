<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
// You can remove Event, Login, and Failed imports if they aren't used elsewhere
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user->role_id === \App\Enums\UserRole::SUPER_ADMIN) {
                return true;
            }
        });


        if (str_starts_with(config('app.url'), 'https://') && request()->getHost() !== 'localhost' && request()->getHost() !== '127.0.0.1') {
            URL::forceScheme('https');
        }

        /*
           REMOVED: Event::listen blocks
           Laravel eleven+ automatically discovers listeners in the app/Listeners folder
        */
        // Optimized View Composer for Notifications
        // Reverted to '*' to ensure compatibility with all views containing the sidebar
        view()->composer('*', function ($view) {
            if (Auth::check()) {
                $notifications = Notification::where('recipient_id', Auth::id())
                    ->latest()
                    ->limit(10)
                    ->get();
                $view->with('notifications', $notifications);
            } else {
                $view->with('notifications', collect());
            }
        });
    }
}
