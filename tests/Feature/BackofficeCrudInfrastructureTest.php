<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\NewsletterCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficeCrudInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_use_the_crud_index_preview_with_querystring_patterns(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::findOrCreate('editor', 'web'));

        $this->actingAs($editor)
            ->get('/backoffice/social-links?search=spotify&sort=label&direction=asc&page=1&perPage=10')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'Redes sociales')
                ->where('table.query.sort', 'label')
                ->where('table.query.direction', 'asc')
                ->where('table.query.perPage', 10)
                ->where('filters.0.key', 'is_active')
                ->has('table.bulkActions', 0)
                ->has('table.pagination')
                ->missing('testChecklist'));
    }

    public function test_readonly_can_view_crud_index_preview_but_without_bulk_actions(): void
    {
        $readonly = User::factory()->create();
        $readonly->assignRole(Role::findOrCreate('readonly', 'web'));
        Event::query()->create([
            'slug' => 'readonly-preview-event',
            'title' => 'Readonly Preview Event',
            'is_published' => true,
        ]);

        $this->actingAs($readonly)
            ->get('/backoffice/events')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('capabilities.canCreate', false)
                ->has('table.bulkActions', 0)
                ->has('table.rows', 1));
    }

    public function test_editor_can_open_the_crud_form_preview_with_relations_actions_and_validation_summary(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::findOrCreate('editor', 'web'));

        $this->actingAs($editor)
            ->get('/backoffice/events/create')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.action', '/backoffice/events/draft')
                ->has('form.sections')
                ->has('form.relationManagers', 0)
                ->has('form.specialActions', 0)
                ->has('form.dangerousActions', 0)
                ->has('form.validationSummary')
                ->missing('form.testChecklist'));
    }

    public function test_crud_draft_preview_uses_form_request_validation_and_returns_errors(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::findOrCreate('editor', 'web'));
        $token = 'csrf-token-backoffice-crud';

        $this->actingAs($editor)
            ->withSession(['_token' => $token])
            ->from('/backoffice/newsletter-campaigns/create')
            ->post('/backoffice/newsletter-campaigns/draft', ['_token' => $token])
            ->assertRedirect('/backoffice/newsletter-campaigns/create')
            ->assertSessionHasErrors(['name', 'subject', 'html_body']);
    }

    public function test_crud_preview_actions_validate_confirmation_and_flash_success(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::findOrCreate('editor', 'web'));
        $token = 'csrf-token-backoffice-actions';
        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Infra Queue',
            'subject' => 'Infra Queue Subject',
            'html_body' => '<p>Infra body</p>',
            'status' => 'draft',
        ]);

        $this->actingAs($editor)
            ->withSession(['_token' => $token])
            ->from('/backoffice/newsletter-campaigns/'.$campaign->id.'/edit')
            ->post('/backoffice/newsletter-campaigns/actions/queue-campaign', [
                '_token' => $token,
                'confirmation' => 'ENCOLAR',
                'record' => (string) $campaign->id,
            ])
            ->assertRedirect('/backoffice/newsletter-campaigns/'.$campaign->id.'/edit')
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');
    }
}
