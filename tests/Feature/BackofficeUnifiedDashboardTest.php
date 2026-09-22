<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\MediaAsset;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\SeoMeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficeUnifiedDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_unified_backoffice_dashboard_to_the_backoffice_login(): void
    {
        $this->get('/backoffice')
            ->assertRedirect('/backoffice/login');
    }

    public function test_editor_can_open_the_unified_backoffice_dashboard_and_see_real_summary_counts(): void
    {
        Event::query()->create([
            'slug' => 'dashboard-event-1',
            'title' => 'Dashboard Event 1',
            'is_published' => true,
        ]);
        Event::query()->create([
            'slug' => 'dashboard-event-2',
            'title' => 'Dashboard Event 2',
            'is_published' => false,
        ]);
        Page::query()->create([
            'slug' => 'home',
            'template' => 'default',
            'is_published' => true,
        ]);
        MediaAsset::query()->create([
            'disk' => 'public',
            'path' => 'media/cover.jpg',
            'filename' => 'cover.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        SeoMeta::query()->create([
            'entity_type' => 'page',
            'entity_id' => 1,
            'locale' => 'es',
            'meta_title' => 'Home',
        ]);
        NewsletterSubscriber::query()->create([
            'email' => 'listener@example.com',
            'name' => 'Listener',
            'is_active' => true,
        ]);
        NewsletterCampaign::query()->create([
            'name' => 'Launch',
            'subject' => 'RadioChi Launch',
            'html_body' => '<p>Launch</p>',
            'status' => 'draft',
        ]);

        $editor = User::factory()->create();
        $editor->assignRole(Role::findOrCreate('editor', 'web'));

        $this->actingAs($editor)
            ->get('/backoffice')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Dashboard/Index')
                ->where('title', 'Dashboard unificado del backoffice')
                ->where('summaryCards.0.value', '2')
                ->where('summaryCards.1.value', '1')
                ->where('summaryCards.2.value', '1')
                ->where('summaryCards.3.value', '1')
                ->where('summaryCards.4.value', '1')
                ->where('summaryCards.5.value', '1')
                ->where('sessionPanel.access.content', 'Gestion total')
                ->has('quickActions', 3)
                ->has('recentTables', 4)
                ->has('recentTables.0.table.rows', 2)
                ->has('recentTables.0.table.rows.0.cells', 4)
                ->where('recentTables.0.table.rows.0.actions.0.label', 'Abrir'));
    }

    public function test_super_admin_dashboard_no_longer_exposes_legacy_links_after_cleanup(): void
    {
        $superAdmin = User::query()->updateOrCreate(
            ['email' => User::SUPER_ADMIN_EMAIL],
            [
                'name' => 'Fernando Cardona Toro',
                'password' => bcrypt('12345678'),
                'role' => 'SuperAdmin',
            ],
        );

        $superAdmin->syncLegacyRoleToSpatieRole();

        $this->actingAs($superAdmin->fresh())
            ->get('/backoffice')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Dashboard/Index')
                ->where('sessionPanel.access.surface', 'Super Admin')
                ->has('sessionPanel.legacyLinks', 0));
    }
}
