<?php

use App\Http\Controllers\Backoffice\DashboardController as BackofficeDashboardController;
use App\Http\Controllers\Backoffice\PreviewController;
use App\Http\Controllers\Backoffice\Phase6TranslationController;
use App\Http\Controllers\Backoffice\AuthController as BackofficeAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicHomeController;
use App\Support\Backoffice\BackofficePath;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

$locales = ['es', 'en', 'ca', 'fr', 'it', 'de'];

Route::get('/', [PublicHomeController::class, 'index']);
Route::get('/{locale}', [PublicHomeController::class, 'localized'])->whereIn('locale', $locales);
Route::get('/login', fn () => redirect('/'))->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get(BackofficePath::official(), [BackofficeDashboardController::class, 'index'])->middleware('backoffice.access')->name('filament.backoffice.pages.dashboard');
Route::get(BackofficePath::official('login'), [BackofficeAuthController::class, 'create'])->name('filament.backoffice.auth.login');
Route::post(BackofficePath::official('login'), [BackofficeAuthController::class, 'store'])->middleware('throttle:login')->name('backoffice.login.store');
Route::post(BackofficePath::official('logout'), [BackofficeAuthController::class, 'destroy'])->middleware('auth')->name('filament.backoffice.auth.logout');

$registerBackofficeModuleRoutes = function (): void {
    Route::get('/legal-documents/{legalDocument}/translations/create', [Phase6TranslationController::class, 'createLegalDocument']);
    Route::post('/legal-documents/{legalDocument}/translations', [Phase6TranslationController::class, 'storeLegalDocument']);
    Route::get('/legal-documents/{legalDocument}/translations/{translation}/edit', [Phase6TranslationController::class, 'editLegalDocument']);
    Route::post('/legal-documents/{legalDocument}/translations/{translation}', [Phase6TranslationController::class, 'updateLegalDocument']);
    Route::get('/music-tracks/{musicTrack}/translations/create', [Phase6TranslationController::class, 'createMusicTrack']);
    Route::post('/music-tracks/{musicTrack}/translations', [Phase6TranslationController::class, 'storeMusicTrack']);
    Route::get('/music-tracks/{musicTrack}/translations/{translation}/edit', [Phase6TranslationController::class, 'editMusicTrack']);
    Route::post('/music-tracks/{musicTrack}/translations/{translation}', [Phase6TranslationController::class, 'updateMusicTrack']);
    Route::get('/pages/{page}/translations/create', [Phase6TranslationController::class, 'createPage']);
    Route::post('/pages/{page}/translations', [Phase6TranslationController::class, 'storePage']);
    Route::get('/pages/{page}/translations/{translation}/edit', [Phase6TranslationController::class, 'editPage']);
    Route::post('/pages/{page}/translations/{translation}', [Phase6TranslationController::class, 'updatePage']);
    Route::get('/page-blocks/{pageBlock}/translations/create', [Phase6TranslationController::class, 'createPageBlock']);
    Route::post('/page-blocks/{pageBlock}/translations', [Phase6TranslationController::class, 'storePageBlock']);
    Route::get('/page-blocks/{pageBlock}/translations/{translation}/edit', [Phase6TranslationController::class, 'editPageBlock']);
    Route::post('/page-blocks/{pageBlock}/translations/{translation}', [Phase6TranslationController::class, 'updatePageBlock']);
    Route::get('/settings/{setting}/translations/create', [Phase6TranslationController::class, 'createSetting']);
    Route::post('/settings/{setting}/translations', [Phase6TranslationController::class, 'storeSetting']);
    Route::get('/settings/{setting}/translations/{translation}/edit', [Phase6TranslationController::class, 'editSetting']);
    Route::post('/settings/{setting}/translations/{translation}', [Phase6TranslationController::class, 'updateSetting']);
    Route::get('/{module}', [PreviewController::class, 'index']);
    Route::get('/{module}/create', [PreviewController::class, 'create']);
    Route::post('/{module}/draft', [PreviewController::class, 'draft']);
    Route::post('/{module}/actions/{action}', [PreviewController::class, 'action']);
    Route::get('/{module}/{record}/edit', [PreviewController::class, 'edit']);
    Route::post('/{module}/draft/{record}', [PreviewController::class, 'draft']);
};

Route::prefix(trim(BackofficePath::official(), '/'))
    ->middleware(['backoffice.access'])
    ->group(function () use ($registerBackofficeModuleRoutes) {
        $registerBackofficeModuleRoutes();
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
