<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPasswordNotification::createUrlUsing(function ($notifiable, $token) {
            return url('/api/password/reset/' . $token . '?email=' . urlencode($notifiable->email));
        });
    }
}
