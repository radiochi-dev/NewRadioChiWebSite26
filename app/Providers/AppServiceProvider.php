<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
            if ($user instanceof User && $user->isSuperAdmin()) {
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

        RateLimiter::for('newsletter-subscribe', function (Request $request) {
            $locale = in_array((string) $request->input('locale'), ['es', 'en', 'ca', 'fr', 'it', 'de'], true)
                ? (string) $request->input('locale')
                : 'es';
            $ip = $request->ip() ?: 'unknown';
            $userAgent = Str::limit((string) $request->userAgent(), 180, '');
            $fingerprint = hash('sha256', $ip.'|'.$userAgent);

            return [
                Limit::perMinute(5)
                    ->by('newsletter:minute:'.$fingerprint)
                    ->response(function (Request $request, array $headers) use ($locale) {
                        return redirect()
                            ->to($locale === 'es' ? '/' : '/'.$locale)
                            ->withErrors([
                                'email' => __('newsletter.validation.throttled', locale: $locale),
                            ])
                            ->withHeaders($headers);
                    }),
                Limit::perHour(20)->by('newsletter:hour:'.$ip),
            ];
        });
    }
}
