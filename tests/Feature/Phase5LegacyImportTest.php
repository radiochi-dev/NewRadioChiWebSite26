<?php

namespace Tests\Feature;

use App\Actions\Legacy\ImportLegacyContentAction;
use App\Models\Event;
use App\Models\LegalDocument;
use App\Models\MediaAsset;
use App\Models\MusicTrack;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Partner;
use App\Models\SeoMeta;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5LegacyImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_5_1_inventories_the_effective_legacy_sources(): void
    {
        $this->artisan('legacy:inventory-content')
            ->expectsOutputToContain('Legacy inventory is complete.')
            ->expectsOutputToContain('resources/js/legacy/data/videos.json')
            ->assertExitCode(0);
    }

    public function test_phase_5_imports_legacy_content_into_editorial_tables_with_locale_parity(): void
    {
        $this->artisan('legacy:import-content')->assertExitCode(0);

        $this->assertSame(6, Page::query()->count());
        $this->assertSame(36, Page::query()->withCount('translations')->get()->sum('translations_count'));
        $this->assertSame(14, PageBlock::query()->count());
        $this->assertSame(84, PageBlock::query()->withCount('translations')->get()->sum('translations_count'));
        $this->assertSame(6, MusicTrack::query()->count());
        $this->assertSame(36, MusicTrack::query()->withCount('translations')->get()->sum('translations_count'));
        $this->assertSame(3, Event::query()->count());
        $this->assertSame(19, MediaAsset::query()->count());
        $this->assertSame(5, Partner::query()->count());
        $this->assertSame(8, SocialLink::query()->count());
        $this->assertSame(3, LegalDocument::query()->count());
        $this->assertSame(18, LegalDocument::query()->withCount('translations')->get()->sum('translations_count'));
        $this->assertSame(11, Setting::query()->count());
        $this->assertSame(36, Setting::query()->withCount('translations')->get()->sum('translations_count'));
        $this->assertSame(6, SeoMeta::query()->count());

        $home = Page::query()->where('slug', 'home')->firstOrFail();
        $homeEs = $home->translations()->where('locale', 'es')->firstOrFail();
        $homeEn = $home->translations()->where('locale', 'en')->firstOrFail();
        $this->assertSame('RadioChi', $homeEs->title);
        $this->assertSame('Welcome to RadioChi', $homeEn->content['slides'][0]['title']);

        $aboutStep = PageBlock::query()->where('key', 'about-step-01')->firstOrFail();
        $this->assertSame('EL INGREDIENTE CLAVE', $aboutStep->translations()->where('locale', 'es')->firstOrFail()->content['title']);

        $musicTrack = MusicTrack::query()->where('slug', 'legacy-track-01-bodybangers-vs-hayley-parsons')->firstOrFail();
        $this->assertSame('Sunglasses at Night (RadioChi Remix)', $musicTrack->translations()->where('locale', 'es')->firstOrFail()->title);

        $legalDocument = LegalDocument::query()->where('slug', 'privacy')->firstOrFail();
        $this->assertSame('Privacy Policy', $legalDocument->translations()->where('locale', 'en')->firstOrFail()->title);

        $setting = Setting::query()->where('group', 'header')->where('key', 'menu')->firstOrFail();
        $this->assertSame('Próximos Eventos', $setting->translations()->where('locale', 'es')->firstOrFail()->value['items']['calendarEvents']);

        $seoMeta = SeoMeta::query()->where('locale', 'fr')->firstOrFail();
        $this->assertSame('/fr', $seoMeta->canonical_url);

        $event = Event::query()->where('slug', 'international-bear-convergence')->firstOrFail();
        $this->assertSame('USA', $event->country);
        $this->assertSame('/assets/img/logos/ibc+blue+logo+mk.webp', $event->poster_path);

        $youtubeSetting = Setting::query()->where('group', 'media')->where('key', 'youtube_channel_url')->firstOrFail();
        $this->assertSame('https://www.youtube.com/channel/TUCANALAQUI', $youtubeSetting->value['value']);
    }

    public function test_phase_5_import_remains_idempotent_on_repeated_runs(): void
    {
        $this->artisan('legacy:import-content')->assertExitCode(0);
        $this->artisan('legacy:import-content')->assertExitCode(0);

        $this->assertSame(6, Page::query()->count());
        $this->assertSame(14, PageBlock::query()->count());
        $this->assertSame(6, MusicTrack::query()->count());
        $this->assertSame(3, Event::query()->count());
        $this->assertSame(19, MediaAsset::query()->count());
        $this->assertSame(3, LegalDocument::query()->count());
        $this->assertSame(11, Setting::query()->count());
        $this->assertSame(6, SeoMeta::query()->count());
    }

    public function test_phase_5_locale_parity_covers_the_six_supported_locales(): void
    {
        app(ImportLegacyContentAction::class)->import();

        $supportedLocales = ImportLegacyContentAction::LOCALES;
        $home = Page::query()->where('slug', 'home')->firstOrFail();
        $terms = LegalDocument::query()->where('slug', 'terms')->firstOrFail();

        $this->assertEqualsCanonicalizing($supportedLocales, $home->translations()->pluck('locale')->all());
        $this->assertEqualsCanonicalizing($supportedLocales, $terms->translations()->pluck('locale')->all());
        $this->assertEqualsCanonicalizing(
            $supportedLocales,
            Setting::query()->where('group', 'header')->where('key', 'open_menu')->firstOrFail()->translations()->pluck('locale')->all(),
        );
        $this->assertEqualsCanonicalizing(
            $supportedLocales,
            SeoMeta::query()->pluck('locale')->all(),
        );
    }

    public function test_database_seeder_bootstraps_super_admins_and_cms_content_for_a_clean_environment(): void
    {
        config()->set('backoffice.super_admins', [
            [
                'name' => 'Fernando Cardona Toro',
                'email' => 'fernandocardonatoro@gmail.com',
                'password' => 'c4c4v4c4$',
            ],
            [
                'name' => 'RadioChi Dev',
                'email' => 'radiochi.dev@gmail.com',
                'password' => 'c4c4v4c4$',
            ],
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(User::query()->where('email', 'fernandocardonatoro@gmail.com')->exists());
        $this->assertTrue(User::query()->where('email', 'radiochi.dev@gmail.com')->exists());
        $this->assertGreaterThan(0, Page::query()->count());
        $this->assertGreaterThan(0, Setting::query()->count());
        $this->assertGreaterThan(0, LegalDocument::query()->count());
        $this->assertGreaterThan(0, SocialLink::query()->count());
    }
}
