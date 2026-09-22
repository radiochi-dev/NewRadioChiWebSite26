<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PublicAnalyticsGa4Test extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_keeps_ga4_disabled_when_not_configured(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        $page = $this->inertiaPage($response);

        $this->assertNull(data_get($page, 'props.analytics.ga4.measurementId'));
        $this->assertFalse(data_get($page, 'props.analytics.ga4.enabled'));
        $this->assertFalse(data_get($page, 'props.analytics.ga4.legalApproved'));
        $this->assertFalse(data_get($page, 'props.analytics.ga4.loadScript'));
        $this->assertArrayHasKey('seo', $page['props']);
    }

    public function test_public_home_keeps_ga4_script_disabled_without_explicit_legal_approval(): void
    {
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
    }

    public function test_public_home_enables_ga4_only_in_production_with_measurement_id_and_legal_approval(): void
    {
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
    }

    public function test_public_setting_can_override_env_ga4_measurement_id_without_breaking_payload(): void
    {
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
}
