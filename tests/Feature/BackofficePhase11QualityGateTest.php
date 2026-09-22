<?php

namespace Tests\Feature;

use App\Jobs\SendNewsletterCampaignJob;
use App\Models\NewsletterCampaign;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficePhase11QualityGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_role_keeps_full_backoffice_access_and_can_queue_newsletters(): void
    {
        Queue::fake();
        $token = 'csrf-token-phase11-marketing';

        $marketing = $this->createUserWithRole('marketing');
        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Phase 11 Campaign',
            'subject' => 'Phase 11 Subject',
            'html_body' => '<p>Phase 11 body</p>',
            'status' => 'draft',
        ]);

        $this->actingAs($marketing)
            ->get('/backoffice')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Dashboard/Index')
                ->where('auth.capabilities.hasBackofficeAccess', true)
                ->where('auth.capabilities.canManageBackofficeContent', true));

        $this->actingAs($marketing)
            ->get('/backoffice/newsletter-campaigns/create')
            ->assertOk();

        $this->actingAs($marketing)
            ->withSession(['_token' => $token])
            ->from('/backoffice/newsletter-campaigns/'.$campaign->id.'/edit')
            ->post('/backoffice/newsletter-campaigns/actions/queue-campaign', [
                '_token' => $token,
                'record' => (string) $campaign->id,
                'confirmation' => 'ENCOLAR',
            ])
            ->assertRedirect('/backoffice/newsletter-campaigns/'.$campaign->id.'/edit');

        $campaign->refresh();

        $this->assertSame('queued', $campaign->status);
        Queue::assertPushed(SendNewsletterCampaignJob::class);
    }

    public function test_readonly_role_cannot_mutate_drafts_translations_or_newsletter_actions(): void
    {
        $token = 'csrf-token-phase11-readonly';
        $readonly = $this->createUserWithRole('readonly');
        $page = Page::query()->create([
            'slug' => 'phase-11-home',
            'template' => 'default',
            'is_published' => true,
        ]);
        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Readonly campaign',
            'subject' => 'Readonly subject',
            'html_body' => '<p>Readonly body</p>',
            'status' => 'draft',
        ]);

        $this->actingAs($readonly)
            ->withSession(['_token' => $token])
            ->post('/backoffice/pages/draft', [
                '_token' => $token,
                'slug' => 'readonly-phase11',
                'template' => 'default',
                'is_published' => true,
            ])
            ->assertForbidden();

        $this->actingAs($readonly)
            ->withSession(['_token' => $token])
            ->post('/backoffice/pages/'.$page->id.'/translations', [
                '_token' => $token,
                'locale' => 'en',
                'title' => 'Readonly blocked translation',
                'content' => 'Readonly blocked content',
            ])
            ->assertForbidden();

        $this->actingAs($readonly)
            ->withSession(['_token' => $token])
            ->post('/backoffice/newsletter-campaigns/actions/queue-campaign', [
                '_token' => $token,
                'record' => (string) $campaign->id,
                'confirmation' => 'ENCOLAR',
            ])
            ->assertForbidden();
    }

    public function test_authenticated_backoffice_pages_keep_the_strict_security_headers_without_unsafe_eval(): void
    {
        $editor = $this->createUserWithRole('editor');

        $response = $this->actingAs($editor)->get('/backoffice');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Dashboard/Index')
                ->where('backoffice.branding.name', 'RadioChi Backoffice'));

        $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("script-src 'self' 'unsafe-inline' https:", $contentSecurityPolicy);
        $this->assertStringNotContainsString("'unsafe-eval'", $contentSecurityPolicy);
        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('same-origin', $response->headers->get('Cross-Origin-Opener-Policy'));
        $this->assertSame('same-origin', $response->headers->get('Cross-Origin-Resource-Policy'));
    }

    public function test_public_home_still_resolves_through_the_public_inertia_contract(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertCookie('radiochi_locale', 'en')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('locale', 'en')
                ->has('content')
                ->has('events')
                ->has('seo'));
    }

    public function test_filament_and_livewire_runtime_classes_are_no_longer_available(): void
    {
        $this->assertFalse(class_exists('Filament\\Panel'));
        $this->assertFalse(interface_exists('Filament\\Models\\Contracts\\FilamentUser'));
        $this->assertFalse(class_exists('Livewire\\Livewire'));
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($roleName, 'web'));

        return $user;
    }
}
