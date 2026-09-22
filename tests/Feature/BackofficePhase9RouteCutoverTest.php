<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficePhase9RouteCutoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_official_backoffice_modules_to_the_backoffice_login(): void
    {
        $this->get('/backoffice/events')
            ->assertRedirect('/backoffice/login');
    }

    public function test_editor_can_open_official_backoffice_routes_and_forms_under_the_new_prefix(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor)
            ->get('/backoffice/events')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'Eventos')
                ->where('table.path', '/backoffice/events'));

        $this->actingAs($editor)
            ->get('/backoffice/pages/create')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.action', '/backoffice/pages/draft'));
    }

    public function test_official_backoffice_draft_flow_redirects_to_official_edit_route(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor)
            ->post('/backoffice/events/draft', [
                'slug' => 'phase9-event',
                'title' => 'Phase 9 Event',
                'location' => 'Barcelona',
                'is_published' => true,
            ])
            ->assertRedirect();

        $event = Event::query()->where('slug', 'phase9-event')->firstOrFail();

        $this->actingAs($editor)
            ->post('/backoffice/events/draft/'.$event->id, [
                'slug' => 'phase9-event',
                'title' => 'Phase 9 Event Updated',
                'location' => 'Madrid',
                'is_published' => false,
            ])
            ->assertRedirect('/backoffice/events/'.$event->id.'/edit');
    }

    public function test_intended_redirects_now_land_on_the_official_backoffice_prefix(): void
    {
        $editor = User::factory()->create([
            'email' => 'phase9-editor@example.com',
            'password' => Hash::make('phase9-pass'),
        ]);
        $editor->assignRole(Role::findOrCreate('editor', 'web'));

        $this->get('/backoffice/newsletter-campaigns')
            ->assertRedirect('/backoffice/login');

        $this->post('/backoffice/login', [
            'email' => 'phase9-editor@example.com',
            'password' => 'phase9-pass',
        ])->assertRedirect('/backoffice/newsletter-campaigns');
    }

    public function test_preview_alias_routes_are_no_longer_available_after_cleanup(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor)
            ->get('/backoffice-preview/pages/create')
            ->assertNotFound();
    }

    public function test_legacy_filament_panel_is_no_longer_registered_after_cleanup(): void
    {
        $this->get('/backoffice-legacy')
            ->assertNotFound();
    }

    public function test_legacy_dashboard_and_api_surfaces_are_no_longer_available_after_cleanup(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor)->get('/dashboard')->assertNotFound();
        $this->actingAs($editor)->get('/dashboard/api/events')->assertNotFound();
        $this->get('/admin/events')->assertNotFound();
    }

    public function test_super_admin_dashboard_no_longer_exposes_legacy_surface_links(): void
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
                ->has('sessionPanel.legacyLinks', 0));
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($roleName, 'web'));

        return $user;
    }
}
