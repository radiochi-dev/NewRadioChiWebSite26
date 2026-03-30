<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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
        Gate::before(function ($user) {
            if ($user->email === 'fernandocardonatoro@gmail.com') {
                return true;
            }

            if (Schema::hasColumn('users', 'role') && $user->role === 'SuperAdmin') {
                return true;
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $key = mb_strtolower((string) $request->input('email')).'|'.$request->ip();

            return [
                Limit::perMinute(5)->by($key),
            ];
        });

        RateLimiter::for('admin', function (Request $request) {
            $identifier = $request->user()?->id ?: $request->ip();

            return [
                Limit::perMinute(120)->by($identifier),
            ];
        });
    }
}
