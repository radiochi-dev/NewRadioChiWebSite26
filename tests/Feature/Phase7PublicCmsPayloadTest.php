<?php

namespace Tests\Feature;

use App\Actions\PublicSite\BuildPublicHomePayloadAction;
use App\Models\MusicTrack;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\SeoMeta;
use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class Phase7PublicCmsPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_7_public_home_reads_editorial_payload_from_cms_tables(): void
    {
        $this->artisan('legacy:import-content')->assertExitCode(0);

        $heroSlide = PageBlock::query()->where('key', 'hero-slide-01')->firstOrFail();
        $heroTranslation = $heroSlide->translations()->where('locale', 'es')->firstOrFail();
        $heroContent = $heroTranslation->content;
        $heroContent['title'] = 'Hero desde CMS';
        $heroTranslation->update(['content' => $heroContent]);

        $footerSetting = Setting::query()->where('group', 'footer')->where('key', 'credits')->firstOrFail();
        $footerTranslation = $footerSetting->translations()->where('locale', 'es')->firstOrFail();
        $footerValue = $footerTranslation->value;
        $footerValue['rights'] = 'CMS Rights';
        $footerTranslation->update(['value' => $footerValue]);

        SocialLink::query()
            ->where('location', 'global')
            ->where('platform', 'youtube')
            ->firstOrFail()
            ->update(['url' => 'https://youtube.com/@cms-channel']);

        SocialLink::query()
            ->where('location', 'global')
            ->where('platform', 'soundcloud')
            ->firstOrFail()
            ->update(['url' => 'https://soundcloud.com/cms-profile']);

        $homePage = Page::query()->where('slug', 'home')->firstOrFail();
        SeoMeta::query()
            ->where('entity_type', Page::class)
            ->where('entity_id', $homePage->id)
            ->where('locale', 'es')
            ->firstOrFail()
            ->update(['meta_description' => 'Descripcion SEO desde CMS']);

        $this->get('/')->assertOk();

        $payload = app(BuildPublicHomePayloadAction::class)->execute('es', 'http://localhost');

        $this->assertSame('Hero desde CMS', data_get($payload, 'content.home.slides.0.title'));
        $this->assertSame('Hero desde CMS', data_get($payload, 'content.home.slides.0.logoAlt'));
        $this->assertSame('Hero desde CMS, imagen promocional', data_get($payload, 'content.home.slides.0.personImageAlt'));
        $this->assertSame('CMS Rights', data_get($payload, 'content.footer.rights'));
        $this->assertSame('https://youtube.com/@cms-channel', data_get($payload, 'content.media.youtubeChannelUrl'));
        $this->assertSame('https://soundcloud.com/cms-profile', collect(data_get($payload, 'contactData.socialLinks'))->firstWhere('platform', 'soundcloud')['url']);
        $this->assertSame('https://soundcloud.com/cms-profile', collect(data_get($payload, 'contactData.footerSocialLinks'))->firstWhere('platform', 'soundcloud')['url']);
        $this->assertSame('Newsletter', data_get($payload, 'contactData.newsletterForm.eyebrow'));
        $this->assertSame('Suscribete a RadioChi', data_get($payload, 'contactData.newsletterForm.title'));
        $this->assertSame('v1.2', data_get($payload, 'contactData.newsletterForm.consentVersion'));
        $this->assertSame('Este suscriptor ya existe.', data_get($payload, 'contactData.newsletterForm.messages.duplicateError'));
        $this->assertSame('Suscriptor ya registrado', data_get($payload, 'contactData.newsletterForm.modal.duplicateTitle'));
        $this->assertFalse(data_get($payload, 'calendarData.visible'));
        $this->assertSame('auto', data_get($payload, 'calendarData.mode'));
        $this->assertSame(5, data_get($payload, 'calendarData.minimumUpcomingEvents'));
        $this->assertSame(0, data_get($payload, 'calendarData.upcomingEventsCount'));
        $this->assertSame([], data_get($payload, 'calendarData.events'));
        $this->assertSame(['home', 'about', 'music', 'media', 'contact'], data_get($payload, 'visibleSections'));
        $this->assertArrayNotHasKey('calendarEvents', data_get($payload, 'content.header.menu'));
        $this->assertSame('Descripcion SEO desde CMS', data_get($payload, 'seo.description'));
        $this->assertStringNotContainsString('/assets/img/', (string) data_get($payload, 'content.home.slides.0.logo'));
        $this->assertStringNotContainsString('/assets/img/', (string) data_get($payload, 'content.home.slides.0.personImage'));
        $this->assertStringNotContainsString('/assets/img/', (string) data_get($payload, 'content.about.scrollytelling.steps.0.image'));
        $this->assertStringNotContainsString('/assets/img/', (string) data_get($payload, 'content.music.tracks.0.image'));
        $this->assertStringNotContainsString('/assets/img/', (string) data_get($payload, 'mediaData.photos.0.src'));
        $this->assertStringNotContainsString('/assets/img/', (string) data_get($payload, 'contactData.sponsorLogos.0.imgSrc'));
    }

    public function test_phase_7_localized_route_uses_locale_specific_cms_content_and_seo(): void
    {
        $this->artisan('legacy:import-content')->assertExitCode(0);

        $heroSlide = PageBlock::query()->where('key', 'hero-slide-01')->firstOrFail();
        $heroTranslation = $heroSlide->translations()->where('locale', 'en')->firstOrFail();
        $heroContent = $heroTranslation->content;
        $heroContent['title'] = 'Welcome from CMS';
        $heroTranslation->update(['content' => $heroContent]);

        $legalButtons = Setting::query()->where('group', 'legal')->where('key', 'buttons')->firstOrFail();
        $legalTranslation = $legalButtons->translations()->where('locale', 'en')->firstOrFail();
        $legalValue = $legalTranslation->value;
        $legalValue['terms_button'] = 'Terms via CMS';
        $legalTranslation->update(['value' => $legalValue]);

        $homePage = Page::query()->where('slug', 'home')->firstOrFail();
        SeoMeta::query()
            ->where('entity_type', Page::class)
            ->where('entity_id', $homePage->id)
            ->where('locale', 'en')
            ->firstOrFail()
            ->update(['meta_description' => 'English description from CMS']);

        $this->get('/en')->assertOk();

        $payload = app(BuildPublicHomePayloadAction::class)->execute('en', 'http://localhost');

        $this->assertSame('Welcome from CMS', data_get($payload, 'content.home.slides.0.title'));
        $this->assertSame('Terms via CMS', data_get($payload, 'content.termsPolicyCookies.terms_button'));
        $this->assertSame('Newsletter', data_get($payload, 'contactData.newsletterForm.eyebrow'));
        $this->assertSame('v1.2', data_get($payload, 'contactData.newsletterForm.consentVersion'));
        $this->assertSame('Subscriber already registered', data_get($payload, 'contactData.newsletterForm.modal.duplicateTitle'));
        $this->assertSame('UPCOMING EVENTS', data_get($payload, 'calendarData.translations.title'));
        $this->assertSame('English description from CMS', data_get($payload, 'seo.description'));
    }

    public function test_phase_7_manual_calendar_visibility_can_force_public_calendar_even_without_upcoming_events(): void
    {
        $this->artisan('legacy:import-content')->assertExitCode(0);

        Setting::query()
            ->where('group', 'calendar')
            ->where('key', 'visibility')
            ->firstOrFail()
            ->update([
                'is_public' => true,
                'value' => [
                    'mode' => 'manual',
                    'manual_enabled' => true,
                    'minimum_upcoming_events' => 5,
                    'lookahead_days' => 365,
                ],
            ]);

        $payload = app(BuildPublicHomePayloadAction::class)->execute('es', 'http://localhost');

        $this->assertTrue(data_get($payload, 'calendarData.visible'));
        $this->assertSame('manual', data_get($payload, 'calendarData.mode'));
        $this->assertSame(['home', 'about', 'music', 'calendar', 'media', 'contact'], data_get($payload, 'visibleSections'));
        $this->assertSame('Próximos Eventos', data_get($payload, 'content.header.menu.calendarEvents'));
    }

    public function test_phase_7_music_payload_normalizes_soundcloud_embed_urls_for_widget_usage(): void
    {
        $this->artisan('legacy:import-content')->assertExitCode(0);

        MusicTrack::query()->firstOrFail()->update([
            'stream_url' => 'https://api.soundcloud.com/tracks/319714972',
            'external_url' => 'https://soundcloud.com/forss/flickermood',
        ]);

        $payload = app(BuildPublicHomePayloadAction::class)->execute('es', 'http://localhost');
        $embedUrl = data_get($payload, 'content.music.tracks.0.soundcloudEmbedUrl');

        $this->assertStringStartsWith('https://w.soundcloud.com/player/?', $embedUrl);
        $this->assertStringContainsString(
            rawurlencode('https://api.soundcloud.com/tracks/319714972'),
            $embedUrl,
        );
    }

    public function test_phase_7_explicit_spanish_route_wins_even_if_browser_cookie_points_to_another_locale(): void
    {
        $this->artisan('legacy:import-content')->assertExitCode(0);

        $this->withCookie('radiochi_locale', 'en')
            ->get('/es')
            ->assertOk()
            ->assertCookie('radiochi_locale', 'es')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('locale', 'es')
                ->where('currentPath', '/es')
                ->has('visibleSections', 5)
                ->where('visibleSections.0', 'home')
                ->where('visibleSections.3', 'media')
                ->has('content.home.slides', 6)
                ->where('content.home.slides.0.title', 'Bienvenidos a RadioChi'));
    }
}

