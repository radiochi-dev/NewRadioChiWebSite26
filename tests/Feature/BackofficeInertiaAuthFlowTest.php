<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficeInertiaAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_configured_super_admin_can_login_from_the_new_backoffice_screen_and_keep_the_intended_redirect(): void
    {
        config()->set('backoffice.super_admins', [
            [
                'name' => 'Fernando Cardona Toro',
                'email' => 'fernandocardonatoro@gmail.com',
                'password' => 'c4c4v4c4$',
            ],
        ]);

        $this->get('/backoffice')
            ->assertRedirect('/backoffice/login');

        $response = $this->post('/backoffice/login', [
            'email' => 'fernandocardonatoro@gmail.com',
            'password' => 'c4c4v4c4$',
        ]);

        $response->assertRedirect('/backoffice');

        $user = User::query()->where('email', 'fernandocardonatoro@gmail.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->fresh()->isSuperAdmin());
        $this->assertTrue($user->fresh()->hasBackofficeAccess());
    }

    public function test_plain_authenticated_user_without_backoffice_access_cannot_login_to_the_backoffice(): void
    {
        $user = User::factory()->create([
            'email' => 'reader@example.com',
            'password' => Hash::make('secret-pass'),
        ]);

        $response = $this->from('/backoffice/login')->post('/backoffice/login', [
            'email' => $user->email,
            'password' => 'secret-pass',
        ]);

        $response->assertRedirect('/backoffice/login');
        $response->assertSessionHasErrors([
            'email' => 'Tu cuenta no tiene acceso al backoffice.',
        ]);
        $this->assertGuest();
    }

    public function test_editor_can_login_and_logout_through_the_new_backoffice_flow(): void
    {
        $editor = User::factory()->create([
            'email' => 'editor@example.com',
            'password' => Hash::make('editor-pass'),
        ]);
        $editor->assignRole(Role::findOrCreate('editor', 'web'));

        $this->post('/backoffice/login', [
            'email' => 'editor@example.com',
            'password' => 'editor-pass',
        ])->assertRedirect('/backoffice');

        $this->assertAuthenticatedAs($editor);

        $this->post('/backoffice/logout')
            ->assertRedirect('/backoffice/login');

        $this->assertGuest();
    }
}

