<?php

namespace Tests\Feature;

use App\Actions\PublicSite\BuildPublicHomePayloadAction;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\SeoMeta;
use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $youtubeSetting = Setting::query()->where('group', 'media')->where('key', 'youtube_channel_url')->firstOrFail();
        $youtubeSetting->update(['value' => ['value' => 'https://youtube.com/@cms-channel']]);

        SocialLink::query()
            ->where('location', 'contact')
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
        $this->assertSame('CMS Rights', data_get($payload, 'content.footer.rights'));
        $this->assertSame('https://youtube.com/@cms-channel', data_get($payload, 'content.media.youtubeChannelUrl'));
        $this->assertSame('https://soundcloud.com/cms-profile', collect(data_get($payload, 'contactData.socialLinks'))->firstWhere('platform', 'soundcloud')['url']);
        $this->assertSame('/assets/img/logos/ibc+blue+logo+mk.webp', data_get($payload, 'calendarData.events.0.logo'));
        $this->assertSame('USA', data_get($payload, 'calendarData.events.0.country'));
        $this->assertSame('Descripcion SEO desde CMS', data_get($payload, 'seo.description'));
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
        $this->assertSame('UPCOMING EVENTS', data_get($payload, 'calendarData.translations.title'));
        $this->assertSame('English description from CMS', data_get($payload, 'seo.description'));
    }
}
