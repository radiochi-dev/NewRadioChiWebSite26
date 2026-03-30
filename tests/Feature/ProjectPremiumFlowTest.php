<?php

namespace Tests\Feature;

use App\Jobs\SendNewsletterCampaignJob;
use App\Models\NewsletterCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProjectPremiumFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_expose_seo_and_security_baseline(): void
    {
        $home = $this->get('/');

        $home->assertOk();
        $home->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $home->assertHeader('X-Content-Type-Options', 'nosniff');

        $sitemap = $this->get('/sitemap.xml');
        $sitemap->assertOk();
        $this->assertStringContainsString('application/xml', (string) $sitemap->headers->get('Content-Type'));
        $sitemap->assertSee('<urlset', false);

        $robots = $this->get('/robots.txt');
        $robots->assertOk();
        $robots->assertSee('User-agent', false);
    }

    public function test_admin_can_crud_events_with_basic_auth(): void
    {
        $authHeaders = $this->basicAuthHeaders();

        $create = $this->withHeaders($authHeaders)->postJson('/admin/events', [
            'slug' => 'test-event-premium',
            'title' => 'Test Event Premium',
            'location' => 'Barcelona',
            'is_published' => true,
        ]);

        $create->assertCreated();
        $eventId = $create->json('id');

        $index = $this->withHeaders($authHeaders)->getJson('/admin/events');
        $index->assertOk()->assertJsonPath('data.0.id', $eventId);

        $update = $this->withHeaders($authHeaders)->putJson('/admin/events/'.$eventId, [
            'title' => 'Test Event Premium Updated',
        ]);
        $update->assertOk()->assertJsonPath('title', 'Test Event Premium Updated');

        $delete = $this->withHeaders($authHeaders)->deleteJson('/admin/events/'.$eventId);
        $delete->assertNoContent();
    }

    public function test_admin_can_queue_newsletter_campaign(): void
    {
        $authHeaders = $this->basicAuthHeaders();
        Queue::fake();

        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Launch',
            'subject' => 'RadioChi Launch',
            'html_body' => '<h1>Launch</h1>',
            'status' => 'draft',
        ]);

        $queue = $this->withHeaders($authHeaders)->postJson('/admin/newsletter-campaigns/'.$campaign->id.'/queue');
        $queue->assertOk()->assertJsonPath('campaign_id', $campaign->id);

        Queue::assertPushed(SendNewsletterCampaignJob::class);
        $this->assertDatabaseHas('newsletter_campaigns', [
            'id' => $campaign->id,
            'status' => 'queued',
        ]);
    }

    public function test_dashboard_access_flow_has_login_route_and_super_admin_access(): void
    {
        $this->assertTrue(Route::has('login'));

        $this->get('/dashboard')->assertRedirect(route('login'));

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'fernandocardonatoro@gmail.com'],
            [
                'name' => 'Fernando Cardona Toro',
                'password' => bcrypt('12345678'),
            ]
        );

        $this->actingAs($superAdmin)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_login_redirects_to_dashboard_with_valid_credentials(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'fernandocardonatoro@gmail.com'],
            [
                'name' => 'Fernando Cardona Toro',
                'password' => bcrypt('12345678'),
            ]
        );

        $response = $this->post('/login', [
            'email' => 'fernandocardonatoro@gmail.com',
            'password' => '12345678',
            'accepted_legal' => 1,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    private function basicAuthHeaders(): array
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        return [
            'Authorization' => 'Basic '.base64_encode($user->email.':password'),
        ];
    }
}
