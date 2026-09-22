<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PublicAnalyticsGa4Test extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_keeps_ga4_disabled_when_not_configured(): void
    {
        $this->seedLegalDocuments();

        $response = $this->get('/');

        $response->assertOk();

        $page = $this->inertiaPage($response);

        $this->assertNull(data_get($page, 'props.analytics.ga4.measurementId'));
        $this->assertFalse(data_get($page, 'props.analytics.ga4.enabled'));
        $this->assertFalse(data_get($page, 'props.analytics.ga4.legalApproved'));
        $this->assertFalse(data_get($page, 'props.analytics.ga4.loadScript'));
        $this->assertArrayHasKey('seo', $page['props']);
        $this->assertStringContainsString('NO UTILIZA ningún tipo de cookie', (string) data_get($page, 'props.content.termsPolicyCookies.cookies.content'));
    }

    public function test_public_home_keeps_ga4_script_disabled_without_explicit_legal_approval(): void
    {
        $this->seedLegalDocuments();

        $this->app['env'] = 'production';
        config()->set('services.ga4.enabled', true);
        config()->set('services.ga4.measurement_id', 'G-TEST123456');

        $response = $this->get('/en');

        $response->assertOk();
        $response->assertHeader('Content-Security-Policy');

        $page = $this->inertiaPage($response);
        $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertSame('G-TEST123456', data_get($page, 'props.analytics.ga4.measurementId'));
        $this->assertTrue(data_get($page, 'props.analytics.ga4.enabled'));
        $this->assertFalse(data_get($page, 'props.analytics.ga4.legalApproved'));
        $this->assertFalse(data_get($page, 'props.analytics.ga4.loadScript'));
        $this->assertSame('en', data_get($page, 'props.locale'));
        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' https:", $contentSecurityPolicy);
        $this->assertStringContainsString("connect-src 'self' https:", $contentSecurityPolicy);
        $this->assertStringContainsString('DOES NOT USE any type of cookie', (string) data_get($page, 'props.content.termsPolicyCookies.cookies.content'));
    }

    public function test_public_home_enables_ga4_only_in_production_with_measurement_id_and_legal_approval(): void
    {
        $this->seedLegalDocuments();

        $this->app['env'] = 'production';
        config()->set('services.ga4.enabled', true);
        config()->set('services.ga4.legal_approved', true);
        config()->set('services.ga4.measurement_id', 'G-TEST123456');

        $response = $this->get('/en');

        $response->assertOk();

        $page = $this->inertiaPage($response);

        $this->assertSame('G-TEST123456', data_get($page, 'props.analytics.ga4.measurementId'));
        $this->assertTrue(data_get($page, 'props.analytics.ga4.enabled'));
        $this->assertTrue(data_get($page, 'props.analytics.ga4.legalApproved'));
        $this->assertTrue(data_get($page, 'props.analytics.ga4.loadScript'));
        $this->assertSame('en', data_get($page, 'props.locale'));
        $this->assertStringContainsString('Google Analytics 4', (string) data_get($page, 'props.content.termsPolicyCookies.cookies.content'));
        $this->assertStringNotContainsString('DOES NOT USE any type of cookie', (string) data_get($page, 'props.content.termsPolicyCookies.cookies.content'));
        $this->assertStringContainsString('Google Analytics 4', (string) data_get($page, 'props.content.termsPolicyCookies.privacy.content'));
    }

    public function test_public_setting_can_override_env_ga4_measurement_id_without_breaking_payload(): void
    {
        $this->seedLegalDocuments();

        $this->app['env'] = 'production';
        config()->set('services.ga4.enabled', true);
        config()->set('services.ga4.legal_approved', true);
        config()->set('services.ga4.measurement_id', 'G-ENV123456');

        Setting::query()->create([
            'group' => 'analytics',
            'key' => 'ga4',
            'type' => 'json',
            'value' => [
                'enabled' => true,
                'measurement_id' => 'G-SETTING123456',
            ],
            'is_translatable' => false,
            'is_public' => true,
            'position' => 1,
            'settings' => ['source' => 'test'],
        ]);

        $response = $this->get('/');

        $response->assertOk();

        $page = $this->inertiaPage($response);

        $this->assertSame('G-SETTING123456', data_get($page, 'props.analytics.ga4.measurementId'));
        $this->assertTrue(data_get($page, 'props.analytics.ga4.legalApproved'));
        $this->assertTrue(data_get($page, 'props.analytics.ga4.loadScript'));
        $this->assertArrayHasKey('seo', $page['props']);
    }

    public function test_public_setting_cannot_activate_ga4_when_env_flag_is_disabled(): void
    {
        $this->seedLegalDocuments();

        $this->app['env'] = 'production';
        config()->set('services.ga4.enabled', false);
        config()->set('services.ga4.legal_approved', true);
        config()->set('services.ga4.measurement_id', null);

        Setting::query()->create([
            'group' => 'analytics',
            'key' => 'ga4',
            'type' => 'json',
            'value' => [
                'enabled' => true,
                'measurement_id' => 'G-SETTING123456',
            ],
            'is_translatable' => false,
            'is_public' => true,
            'position' => 1,
            'settings' => ['source' => 'test'],
        ]);

        $response = $this->get('/');

        $response->assertOk();

        $page = $this->inertiaPage($response);

        $this->assertFalse(data_get($page, 'props.analytics.ga4.enabled'));
        $this->assertTrue(data_get($page, 'props.analytics.ga4.legalApproved'));
        $this->assertSame('G-SETTING123456', data_get($page, 'props.analytics.ga4.measurementId'));
        $this->assertFalse(data_get($page, 'props.analytics.ga4.loadScript'));
    }

    private function inertiaPage(TestResponse $response): array
    {
        preg_match('/<script data-page="app" type="application\/json">(.*?)<\/script>/s', $response->getContent(), $matches);

        $this->assertArrayHasKey(1, $matches, 'No se encontro data-page en la respuesta Inertia.');

        $page = json_decode($matches[1], true);

        $this->assertIsArray($page);

        return $page;
    }

    private function seedLegalDocuments(): void
    {
        $documents = [
            'terms' => [
                'es' => [
                    'title' => 'Términos y Condiciones de Uso',
                    'content' => '<p>Base legal.</p><ul><li>No se utilizan cookies ni tecnologías de seguimiento que puedan recopilar información personal.</li></ul>',
                ],
                'en' => [
                    'title' => 'Terms and Conditions',
                    'content' => '<p>Legal baseline.</p><ul><li>No cookies or tracking technologies that may collect personal information are used.</li></ul>',
                ],
            ],
            'privacy' => [
                'es' => [
                    'title' => 'Política de Privacidad',
                    'content' => '<p>Este Sitio Web no utiliza cookies de ningún tipo, ni propias ni de terceros, para finalidades analíticas, publicitarias o de seguimiento. La navegación es completamente libre de rastreadores, garantizando que tu actividad no es monitoreada.</p>',
                ],
                'en' => [
                    'title' => 'Privacy Policy',
                    'content' => '<p>This Website does not use cookies of any type, neither own nor third-party, for analytical, advertising or tracking purposes. Navigation is completely free of trackers, ensuring that your activity is not monitored.</p>',
                ],
            ],
            'cookies' => [
                'es' => [
                    'title' => 'Política de Cookies',
                    'content' => '<p><strong>Este Sitio Web NO UTILIZA ningún tipo de cookie, ni propia ni de terceros.</strong></p><p>No utilizamos ninguna tecnología que almacene información en tu navegador para finalidades de seguimiento, análisis, publicidad o funcionamiento. Tu visita es completamente anónima y privada desde el punto de vista de nuestro sitio web.</p>',
                ],
                'en' => [
                    'title' => 'Cookies Policy',
                    'content' => '<p><strong>This Website DOES NOT USE any type of cookie, neither own nor third-party.</strong></p><p>We do not use any technology that stores information in your browser for tracking, analysis, advertising, or operational purposes. Your visit is completely anonymous and private from our website\'s perspective.</p>',
                ],
            ],
        ];

        foreach ($documents as $documentType => $translations) {
            $document = LegalDocument::query()->create([
                'slug' => $documentType,
                'document_type' => $documentType,
                'version' => '2025',
                'position' => 1,
                'is_published' => true,
                'settings' => ['source' => 'test'],
            ]);

            foreach ($translations as $locale => $payload) {
                $document->translations()->create([
                    'locale' => $locale,
                    'title' => $payload['title'],
                    'summary' => null,
                    'content' => $payload['content'],
                    'cta_label' => null,
                ]);
            }
        }
    }
}
