<?php

namespace Tests\Feature;

use App\Jobs\SendNewsletterCampaignJob;
use App\Models\Event;
use App\Models\NewsletterCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
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

    public function test_editor_can_create_and_update_events_through_the_official_backoffice_flow(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor)->post('/backoffice/events/draft', [
            'slug' => 'test-event-premium',
            'title' => 'Test Event Premium',
            'location' => 'Barcelona',
            'is_published' => true,
        ])->assertRedirect();

        $event = Event::query()->where('slug', 'test-event-premium')->firstOrFail();

        $this->actingAs($editor)->post('/backoffice/events/draft/'.$event->id, [
            'slug' => 'test-event-premium',
            'title' => 'Test Event Premium Updated',
            'location' => 'Madrid',
            'is_published' => true,
        ])->assertRedirect('/backoffice/events/'.$event->id.'/edit');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Test Event Premium Updated',
            'location' => 'Madrid',
        ]);
    }

    public function test_editor_can_queue_newsletter_campaign_through_the_official_backoffice_action(): void
    {
        Queue::fake();
        $editor = $this->createUserWithRole('editor');

        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Launch',
            'subject' => 'RadioChi Launch',
            'html_body' => '<h1>Launch</h1>',
            'status' => 'draft',
        ]);

        $this->actingAs($editor)
            ->post('/backoffice/newsletter-campaigns/actions/queue-campaign', [
                'record' => (string) $campaign->id,
                'confirmation' => 'ENCOLAR',
            ])
            ->assertRedirect('/backoffice/newsletter-campaigns/'.$campaign->id.'/edit');

        Queue::assertPushed(SendNewsletterCampaignJob::class);
        $this->assertDatabaseHas('newsletter_campaigns', [
            'id' => $campaign->id,
            'status' => 'queued',
        ]);
    }

    public function test_backoffice_access_flow_has_login_route_and_super_admin_access(): void
    {
        $this->assertTrue(Route::has('login'));
        $this->assertTrue(Route::has('filament.backoffice.pages.dashboard'));

        $this->get('/backoffice')->assertRedirect('/backoffice/login');

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'fernandocardonatoro@gmail.com'],
            [
                'name' => 'Fernando Cardona Toro',
                'password' => bcrypt('12345678'),
                'role' => 'SuperAdmin',
            ]
        );
        $superAdmin->syncLegacyRoleToSpatieRole();

        $this->actingAs($superAdmin)
            ->get('/backoffice')
            ->assertOk();
    }

    public function test_public_login_redirects_to_the_official_backoffice_with_valid_credentials(): void
    {
        config()->set('backoffice.super_admins', [
            [
                'name' => 'Fernando Cardona Toro',
                'email' => 'fernandocardonatoro@gmail.com',
                'password' => '12345678',
            ],
        ]);

        $response = $this->post('/login', [
            'email' => 'fernandocardonatoro@gmail.com',
            'password' => '12345678',
            'accepted_legal' => 1,
        ]);

        $response->assertRedirect('/backoffice');
        $this->assertAuthenticated();
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($roleName, 'web'));

        return $user;
    }
}
