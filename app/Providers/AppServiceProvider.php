<?php

namespace App\Providers;

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
        \Illuminate\Support\Facades\RateLimiter::for('login', function ($request) {
            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by('ip:'.$request->ip()),
                \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by('account:'.hash('sha256', mb_strtolower(is_string($request->input('email')) ? trim($request->input('email')) : '').'|'.$request->ip())),
            ];
        });
        \Illuminate\Support\Facades\RateLimiter::for('register', fn ($request) =>
            \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip())
        );
        \Illuminate\Support\Facades\RateLimiter::for('checkout', fn ($request) =>
            \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by((string) $request->user()->id)
        );
    }
}
