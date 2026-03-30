<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\MediaAssetController;
use App\Http\Controllers\Admin\NewsletterCampaignController;
use App\Http\Controllers\Admin\NewsletterLogController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PageTranslationController;
use App\Http\Controllers\Admin\SeoMetaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

$locales = ['es', 'en', 'ca', 'fr', 'it', 'de'];

Route::get('/', [PublicHomeController::class, 'index']);
Route::get('/{locale}', [PublicHomeController::class, 'localized'])->whereIn('locale', $locales);
Route::get('/login', fn () => redirect('/'))->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::middleware(['auth', EnsureSuperAdmin::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

Route::get('/sitemap.xml', function () use ($locales) {
    $baseUrl = rtrim(config('app.url', request()->getSchemeAndHttpHost()), '/');

    $urls = collect($locales)->map(function (string $locale) use ($baseUrl) {
        $path = $locale === 'es' ? '/' : '/'.$locale;

        return [
            'loc' => $baseUrl.$path,
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => $locale === 'es' ? '1.0' : '0.9',
        ];
    })->all();

    $xml = view('sitemap', ['urls' => $urls])->render();

    return response($xml, 200, ['Content-Type' => 'application/xml']);
});

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Sitemap: '.rtrim(config('app.url', request()->getSchemeAndHttpHost()), '/').'/sitemap.xml',
    ];

    if (! app()->isProduction()) {
        $lines = [
            'User-agent: *',
            'Disallow: /',
        ];
    }

    return response(Str::of(implode("\n", $lines))->append("\n"), 200, ['Content-Type' => 'text/plain']);
});

Route::prefix('admin')
    ->middleware(['auth.basic', 'throttle:admin'])
    ->group(function () {
        Route::apiResource('events', EventController::class);
        Route::apiResource('pages', PageController::class);
        Route::post('pages/{page}/translations', [PageTranslationController::class, 'store']);
        Route::get('pages/{page}/translations/{translation}', [PageTranslationController::class, 'show']);
        Route::put('pages/{page}/translations/{translation}', [PageTranslationController::class, 'update']);
        Route::delete('pages/{page}/translations/{translation}', [PageTranslationController::class, 'destroy']);
        Route::apiResource('media', MediaAssetController::class);
        Route::apiResource('seo-meta', SeoMetaController::class);
        Route::apiResource('newsletter-subscribers', NewsletterSubscriberController::class)->parameters([
            'newsletter-subscribers' => 'subscriber',
        ]);
        Route::apiResource('newsletter-campaigns', NewsletterCampaignController::class)->parameters([
            'newsletter-campaigns' => 'campaign',
        ]);
        Route::post('newsletter-campaigns/{campaign}/queue', [NewsletterCampaignController::class, 'queue']);
        Route::get('newsletter-logs', [NewsletterLogController::class, 'index']);
        Route::get('newsletter-campaigns/{campaign}/logs', [NewsletterLogController::class, 'byCampaign']);
    });

Route::prefix('dashboard/api')
    ->middleware(['auth', EnsureSuperAdmin::class, 'throttle:admin'])
    ->group(function () {
        Route::apiResource('events', EventController::class);
        Route::apiResource('pages', PageController::class);
        Route::post('pages/{page}/translations', [PageTranslationController::class, 'store']);
        Route::get('pages/{page}/translations/{translation}', [PageTranslationController::class, 'show']);
        Route::put('pages/{page}/translations/{translation}', [PageTranslationController::class, 'update']);
        Route::delete('pages/{page}/translations/{translation}', [PageTranslationController::class, 'destroy']);
        Route::apiResource('media', MediaAssetController::class);
        Route::apiResource('seo-meta', SeoMetaController::class);
        Route::apiResource('newsletter-subscribers', NewsletterSubscriberController::class)->parameters([
            'newsletter-subscribers' => 'subscriber',
        ]);
        Route::apiResource('newsletter-campaigns', NewsletterCampaignController::class)->parameters([
            'newsletter-campaigns' => 'campaign',
        ]);
        Route::post('newsletter-campaigns/{campaign}/queue', [NewsletterCampaignController::class, 'queue']);
        Route::get('newsletter-logs', [NewsletterLogController::class, 'index']);
        Route::get('newsletter-campaigns/{campaign}/logs', [NewsletterLogController::class, 'byCampaign']);
    });
