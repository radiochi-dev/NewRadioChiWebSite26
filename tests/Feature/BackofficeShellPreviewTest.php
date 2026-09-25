<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficeShellPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_away_from_the_official_backoffice_dashboard(): void
    {
        $this->get('/backoffice')
            ->assertRedirect('/backoffice/login');
    }

    public function test_editor_can_access_the_official_backoffice_dashboard_with_namespaced_props(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::findOrCreate('editor', 'web'));

        $this->actingAs($editor)
            ->get('/backoffice')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Dashboard/Index')
                ->where('title', 'Dashboard unificado del backoffice')
                ->where('auth.capabilities.hasBackofficeAccess', true)
                ->where('auth.capabilities.canManageBackofficeContent', true)
                ->where('backoffice.branding.name', 'RadioChi Backoffice')
                ->has('backoffice.navigation', 7)
                ->where('backoffice.navigation.1.label', 'Editorial / contenido')
                ->where('backoffice.navigation.2.label', 'Media')
                ->where('backoffice.navigation.3.label', 'Marketing')
                ->where('backoffice.navigation.6.label', 'Configuracion')
                ->has('summaryCards', 6)
                ->has('quickActions', 3)
                ->has('recentTables', 4));
    }

    public function test_readonly_can_view_official_module_indexes_but_cannot_open_create_routes(): void
    {
        $readonly = User::factory()->create();
        $readonly->assignRole(Role::findOrCreate('readonly', 'web'));

        $this->actingAs($readonly)
            ->get('/backoffice/events')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'Eventos')
                ->where('capabilities.canCreate', false)
                ->where('auth.capabilities.canManageBackofficeContent', false)
                ->has('table.columns', 7)
                ->has('filters', 2));

        $this->actingAs($readonly)
            ->get('/backoffice/events/create')
            ->assertForbidden();
    }
}
