<?php

namespace Tests\Feature;

use App\Jobs\SendNewsletterCampaignJob;
use App\Models\LegalDocument;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterLog;
use App\Models\NewsletterSubscriber;
use App\Models\RedirectRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficePhase8OperationalModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_view_real_phase_8_indexes(): void
    {
        $editor = $this->createUserWithRole('editor');

        $document = LegalDocument::query()->create([
            'slug' => 'privacy-policy',
            'document_type' => 'privacy',
            'version' => '2026.09',
            'position' => 1,
            'is_published' => true,
        ]);
        $document->translations()->create([
            'locale' => 'es',
            'title' => 'Politica de privacidad',
            'content' => 'Contenido legal',
        ]);

        RedirectRule::query()->create([
            'source_path' => '/old-home',
            'destination_url' => '/nuevo-home',
            'http_status' => 301,
            'is_active' => true,
            'hit_count' => 7,
        ]);

        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'phase8@example.com',
            'name' => 'Phase 8',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Phase 8 Campaign',
            'subject' => 'Phase 8 Subject',
            'html_body' => '<p>Newsletter body</p>',
            'status' => 'draft',
        ]);

        NewsletterLog::query()->create([
            'campaign_id' => $campaign->id,
            'subscriber_id' => $subscriber->id,
            'status' => 'sent',
            'processed_at' => now(),
        ]);

        foreach ([
            '/backoffice/legal-documents' => 'Documentos legales',
            '/backoffice/redirect-rules' => 'Redirecciones',
            '/backoffice/newsletter-subscribers' => 'Suscriptores newsletter',
            '/backoffice/newsletter-campaigns' => 'Campanas newsletter',
            '/backoffice/newsletter-logs' => 'Logs newsletter',
        ] as $url => $title) {
            $this->actingAs($editor)
                ->get($url)
                ->assertOk()
                ->assertInertia(fn (Assert $inertia) => $inertia
                    ->component('Backoffice/Preview/ModuleIndex')
                    ->where('title', $title)
                    ->has('table.rows', 1));
        }
    }

    public function test_readonly_can_view_phase_8_indexes_and_log_detail_but_cannot_create_or_mutate(): void
    {
        $readonly = $this->createUserWithRole('readonly');
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'readonly@example.com',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Readonly campaign',
            'subject' => 'Readonly subject',
            'html_body' => '<p>Readonly body</p>',
            'status' => 'sent',
        ]);
        $log = NewsletterLog::query()->create([
            'campaign_id' => $campaign->id,
            'subscriber_id' => $subscriber->id,
            'status' => 'sent',
            'processed_at' => now(),
        ]);

        foreach ([
            '/backoffice/legal-documents',
            '/backoffice/redirect-rules',
            '/backoffice/newsletter-subscribers',
            '/backoffice/newsletter-campaigns',
            '/backoffice/newsletter-logs',
        ] as $url) {
            $this->actingAs($readonly)->get($url)->assertOk();
        }

        foreach ([
            '/backoffice/legal-documents/create',
            '/backoffice/redirect-rules/create',
            '/backoffice/newsletter-subscribers/create',
            '/backoffice/newsletter-campaigns/create',
            '/backoffice/newsletter-logs/create',
        ] as $url) {
            $this->actingAs($readonly)->get($url)->assertForbidden();
        }

        $this->actingAs($readonly)
            ->get('/backoffice/newsletter-logs/'.$log->id.'/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('title', 'Ver Log newsletter')
                ->where('capabilities.canSubmit', false));

        $this->actingAs($readonly)
            ->post('/backoffice/newsletter-campaigns/actions/queue-campaign', [
                'record' => (string) $campaign->id,
                'confirmation' => 'ENCOLAR',
            ])
            ->assertForbidden();

        $this->actingAs($readonly)
            ->post('/backoffice/newsletter-subscribers/actions/unsubscribe', [
                'record' => (string) $subscriber->id,
            ])
            ->assertForbidden();
    }

    public function test_editor_can_create_legal_document_manage_translation_and_create_redirect_rule(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor)
            ->post('/backoffice/legal-documents/draft', [
                'slug' => 'terms-phase8',
                'document_type' => 'terms',
                'locale' => 'es',
                'title' => 'Terminos fase 8',
                'summary' => 'Resumen legal inicial',
                'content' => '<h2>Contenido legal inicial</h2><p>Texto base en castellano.</p>',
                'cta_label' => 'Leer',
                'version' => 'v8',
                'position' => 1,
                'is_published' => true,
                'settings' => json_encode(['surface' => 'footer'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect();

        $document = LegalDocument::query()->where('slug', 'terms-phase8')->firstOrFail();

        $this->actingAs($editor)
            ->get('/backoffice/legal-documents/'.$document->id.'/edit?locale=es')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->has('form.relationManagers', 0)
                ->where('form.localeActions.0.label', 'ES')
                ->where('form.localeActions.1.label', 'EN +')
                ->where('form.sections.1.fields.3.type', 'richtext')
                ->where('form.defaults.title', 'Terminos fase 8'));

        $this->actingAs($editor)
            ->post('/backoffice/legal-documents/'.$document->id.'/translations', [
                'locale' => 'en',
                'title' => 'Terms Phase 8',
                'summary' => 'Legal summary',
                'content' => '<h2>Legal translated content</h2><p>Formatted paragraph</p>',
                'cta_label' => 'Read more',
            ])
            ->assertRedirect('/backoffice/legal-documents/'.$document->id.'/edit');

        $translation = $document->translations()->where('locale', 'en')->firstOrFail();

        $this->actingAs($editor)
            ->get('/backoffice/legal-documents/'.$document->id.'/translations/'.$translation->id.'/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.sections.0.fields.4.type', 'richtext'));

        $this->actingAs($editor)
            ->post('/backoffice/redirect-rules/draft', [
                'source_path' => '/legacy-terms',
                'destination_url' => '/legal/terms',
                'http_status' => 308,
                'locale' => 'en',
                'hit_count' => 4,
                'is_active' => true,
                'notes' => 'Phase 8 redirect',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('legal_documents', [
            'id' => $document->id,
            'slug' => 'terms-phase8',
            'document_type' => 'terms',
        ]);
        $this->assertDatabaseHas('legal_document_translations', [
            'legal_document_id' => $document->id,
            'locale' => 'en',
            'title' => 'Terms Phase 8',
        ]);
        $this->assertDatabaseHas('redirect_rules', [
            'source_path' => '/legacy-terms',
            'destination_url' => '/legal/terms',
            'http_status' => 308,
            'locale' => 'en',
        ]);
    }

    public function test_editor_can_create_and_update_newsletter_subscriber_with_business_dates(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor)
            ->post('/backoffice/newsletter-subscribers/draft', [
                'email' => 'Phase8-Subscriber@GMAIL.com',
                'name' => 'Phase 8 Subscriber',
                'is_active' => true,
            ])
            ->assertRedirect();

        $subscriber = NewsletterSubscriber::query()->where('email', 'phase8-subscriber@gmail.com')->firstOrFail();

        $this->assertSame('phase8-subscriber@gmail.com', $subscriber->email);
        $this->assertNotNull($subscriber->subscribed_at);
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertNull($subscriber->confirmation_token);
        $this->assertNotEmpty($subscriber->unsubscribe_token);
        $this->assertSame(64, strlen((string) $subscriber->unsubscribe_token));
        $this->assertSame('v1.1', $subscriber->consent_text_version);

        $this->actingAs($editor)
            ->post('/backoffice/newsletter-subscribers/draft/'.$subscriber->id, [
                'email' => 'PHASE8-SUBSCRIBER@GMAIL.COM',
                'name' => 'Phase 8 Subscriber',
                'is_active' => false,
            ])
            ->assertRedirect('/backoffice/newsletter-subscribers/'.$subscriber->id.'/edit');

        $subscriber->refresh();

        $this->assertFalse($subscriber->is_active);
        $this->assertSame('phase8-subscriber@gmail.com', $subscriber->email);
        $this->assertNotNull($subscriber->unsubscribed_at);
    }

    public function test_newsletter_subscriber_model_generates_legal_defaults_for_inactive_records(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'Legal-Default@Example.com',
            'name' => 'Legal Default',
        ]);

        $this->assertSame('legal-default@example.com', $subscriber->email);
        $this->assertFalse($subscriber->is_active);
        $this->assertNull($subscriber->subscribed_at);
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertNull($subscriber->confirmation_token);
        $this->assertNotEmpty($subscriber->unsubscribe_token);
        $this->assertSame(64, strlen((string) $subscriber->unsubscribe_token));
        $this->assertSame('v1.1', $subscriber->consent_text_version);
    }

    public function test_editor_can_operate_manual_unsubscribe_and_reactivate_from_backoffice_without_rgpd_delete(): void
    {
        $editor = $this->createUserWithRole('editor');
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'ops-subscriber@example.com',
            'name' => 'Ops Subscriber',
            'is_active' => true,
            'subscribed_at' => now()->subDay(),
            'confirmation_token' => str_repeat('z', 64),
        ]);

        $this->actingAs($editor)
            ->get('/backoffice/newsletter-subscribers')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('table.rows.0.actions.0.label', 'Editar')
                ->where('table.rows.0.actions.1.label', 'Dar de baja')
                ->missing('table.rows.0.actions.2'));

        $this->actingAs($editor)
            ->get('/backoffice/newsletter-subscribers/'.$subscriber->id.'/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.specialActions.0.slug', 'unsubscribe')
                ->where('form.sections.0.fields.5.key', 'consent_text_version')
                ->where('form.sections.0.fields.6.key', 'ip_address')
                ->where('form.sections.0.fields.9.key', 'user_agent')
                ->where('form.dangerousActions', []));

        $this->actingAs($editor)
            ->from('/backoffice/newsletter-subscribers/'.$subscriber->id.'/edit')
            ->post('/backoffice/newsletter-subscribers/actions/unsubscribe', [
                'record' => (string) $subscriber->id,
            ])
            ->assertRedirect('/backoffice/newsletter-subscribers/'.$subscriber->id.'/edit');

        $subscriber->refresh();

        $this->assertFalse($subscriber->is_active);
        $this->assertNotNull($subscriber->unsubscribed_at);
        $this->assertNull($subscriber->confirmation_token);

        $this->actingAs($editor)
            ->get('/backoffice/newsletter-subscribers/'.$subscriber->id.'/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.specialActions.0.slug', 'reactivate')
                ->where('form.dangerousActions', []));

        $this->actingAs($editor)
            ->from('/backoffice/newsletter-subscribers/'.$subscriber->id.'/edit')
            ->post('/backoffice/newsletter-subscribers/actions/reactivate', [
                'record' => (string) $subscriber->id,
            ])
            ->assertRedirect('/backoffice/newsletter-subscribers/'.$subscriber->id.'/edit');

        $subscriber->refresh();

        $this->assertTrue($subscriber->is_active);
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertNull($subscriber->confirmation_token);
    }

    public function test_super_admin_can_execute_definitive_rgpd_delete_with_cascade_from_backoffice(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'rgpd-delete@example.com',
            'name' => 'RGPD Delete',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
        $campaign = NewsletterCampaign::query()->create([
            'name' => 'RGPD Campaign',
            'subject' => 'RGPD Subject',
            'html_body' => '<p>RGPD body</p>',
            'status' => 'sent',
        ]);
        $log = NewsletterLog::query()->create([
            'campaign_id' => $campaign->id,
            'subscriber_id' => $subscriber->id,
            'status' => 'sent',
            'processed_at' => now(),
        ]);

        $this->actingAs($superAdmin)
            ->get('/backoffice/newsletter-subscribers/'.$subscriber->id.'/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.specialActions.0.slug', 'unsubscribe')
                ->where('form.dangerousActions.0.slug', 'forget'));

        $this->actingAs($superAdmin)
            ->post('/backoffice/newsletter-subscribers/actions/forget', [
                'record' => (string) $subscriber->id,
                'confirmation' => 'ELIMINAR',
            ])
            ->assertRedirect('/backoffice/newsletter-subscribers');

        $this->assertDatabaseMissing('newsletter_subscribers', [
            'id' => $subscriber->id,
        ]);
        $this->assertDatabaseMissing('newsletter_logs', [
            'id' => $log->id,
        ]);
    }

    public function test_editor_can_queue_campaign_and_open_related_logs_from_campaign_form(): void
    {
        Queue::fake();

        $editor = $this->createUserWithRole('editor');
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'phase8-log@example.com',
            'name' => 'Phase 8 Log',
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Queue me',
            'subject' => 'Queue subject',
            'html_body' => '<p>Queue body</p>',
            'status' => 'draft',
        ]);
        $log = NewsletterLog::query()->create([
            'campaign_id' => $campaign->id,
            'subscriber_id' => $subscriber->id,
            'status' => 'failed',
            'error_message' => 'SMTP timeout',
            'processed_at' => now(),
        ]);

        $this->actingAs($editor)
            ->get('/backoffice/newsletter-campaigns/'.$campaign->id.'/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.sections.1.fields.0.type', 'richtext')
                ->where('form.relationManagers.0.createLabel', 'Ver logs')
                ->where('form.relationManagers.0.items.0.id', (string) $log->id)
                ->where('form.specialActions.0.slug', 'queue-campaign'));

        $this->actingAs($editor)
            ->post('/backoffice/newsletter-campaigns/actions/queue-campaign', [
                'record' => (string) $campaign->id,
                'confirmation' => 'ENCOLAR',
            ])
            ->assertRedirect('/backoffice/newsletter-campaigns/'.$campaign->id.'/edit');

        $campaign->refresh();

        $this->assertSame('queued', $campaign->status);
        Queue::assertPushed(SendNewsletterCampaignJob::class);

        $this->actingAs($editor)
            ->get('/backoffice/newsletter-logs?filters[campaign_id]='.$campaign->id)
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('filters.0.value', (string) $campaign->id)
                ->has('table.rows', 1));
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($roleName, 'web'));

        return $user;
    }
}
