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

        $response = $this->postWithCsrf('/backoffice/login', [
            'email' => 'fernandocardonatoro@gmail.com',
            'password' => 'c4c4v4c4$',
        ]);

        $response->assertRedirect('/backoffice');

        $user = User::query()->where('email', 'fernandocardonatoro@gmail.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->fresh()->isSuperAdmin());
        $this->assertTrue($user->fresh()->hasBackofficeAccess());
    }

    public function test_secondary_configured_super_admin_can_login_with_radiochi_dev_credentials(): void
    {
        config()->set('backoffice.super_admins', [
            [
                'name' => 'Fernando Cardona Toro',
                'email' => 'fernandocardonatoro@gmail.com',
                'password' => 'c4c4v4c4$',
            ],
            [
                'name' => 'RadioChi Dev',
                'email' => 'radiochi.dev@gmail.com',
                'password' => 'c4c4v4c4$',
            ],
        ]);

        $this->postWithCsrf('/backoffice/login', [
            'email' => 'radiochi.dev@gmail.com',
            'password' => 'c4c4v4c4$',
        ])->assertRedirect('/backoffice');

        $user = User::query()->where('email', 'radiochi.dev@gmail.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->fresh()->isSuperAdmin());
        $this->assertTrue($user->fresh()->hasBackofficeAccess());
    }

    public function test_configured_super_admin_login_realigns_an_existing_record_with_the_configured_password(): void
    {
        config()->set('backoffice.super_admins', [
            [
                'name' => 'RadioChi Dev',
                'email' => 'radiochi.dev@gmail.com',
                'password' => 'c4c4v4c4$',
            ],
        ]);

        User::query()->create([
            'name' => 'RadioChi Dev',
            'email' => 'radiochi.dev@gmail.com',
            'password' => Hash::make('old-password'),
            'role' => 'SuperAdmin',
        ]);

        $this->postWithCsrf('/backoffice/login', [
            'email' => 'radiochi.dev@gmail.com',
            'password' => 'c4c4v4c4$',
        ])->assertRedirect('/backoffice');

        $this->assertAuthenticated();
    }

    public function test_plain_authenticated_user_without_backoffice_access_cannot_login_to_the_backoffice(): void
    {
        $user = User::factory()->create([
            'email' => 'reader@example.com',
            'password' => Hash::make('secret-pass'),
        ]);

        $response = $this->postWithCsrf('/backoffice/login', [
            'email' => $user->email,
            'password' => 'secret-pass',
        ], '/backoffice/login');

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

        $this->postWithCsrf('/backoffice/login', [
            'email' => 'editor@example.com',
            'password' => 'editor-pass',
        ])->assertRedirect('/backoffice');

        $this->assertAuthenticatedAs($editor);

        $this->postWithCsrf('/backoffice/logout')
            ->assertRedirect('/backoffice/login');

        $this->assertGuest();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function postWithCsrf(string $uri, array $data = [], ?string $from = null)
    {
        $token = 'csrf-backoffice-auth-test';

        $request = $this->withSession(['_token' => $token]);

        if (is_string($from)) {
            $request = $request->from($from);
        }

        return $request->post($uri, array_merge(['_token' => $token], $data));
    }
}
