<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BackofficeLoginExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_backoffice_login_uses_inertia_branding_and_stricter_csp_than_filament_pages(): void
    {
        $response = $this->get('/backoffice/login');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Auth/Login')
                ->where('title', 'Entre a su cuenta')
                ->where('description', 'Acceso protegido al backoffice de RadioChi.')
                ->where('footer.legalLinks.0.label', 'Términos y Condiciones'))
            ->assertSee('Entre a su cuenta');

        $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' https:", $contentSecurityPolicy);
        $this->assertStringNotContainsString("'unsafe-eval'", $contentSecurityPolicy);
        $this->assertStringContainsString("font-src 'self' https: data:", $contentSecurityPolicy);
    }

    public function test_public_routes_keep_the_stricter_csp_without_unsafe_eval(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' https:", $contentSecurityPolicy);
        $this->assertStringNotContainsString("'unsafe-eval'", $contentSecurityPolicy);
    }
}
