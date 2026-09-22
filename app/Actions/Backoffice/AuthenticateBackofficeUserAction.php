<?php

namespace App\Actions\Backoffice;

use App\Models\User;
use App\Support\BackofficeSuperAdminRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AuthenticateBackofficeUserAction
{
    public function execute(Request $request, string $email, string $password, bool $remember = false): User
    {
        $guard = Auth::guard('web');

        $this->ensureConfiguredSuperAdminExists($email);

        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User || ! Hash::check($password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Credenciales inválidas.',
            ]);
        }

        $authenticatedUser = $guard->loginUsingId($user->getAuthIdentifier(), $remember);

        if (! $authenticatedUser instanceof User) {
            throw ValidationException::withMessages([
                'email' => 'No se pudo iniciar la sesion del backoffice.',
            ]);
        }

        $request->session()->regenerate();

        $authenticatedUser->syncLegacyRoleToSpatieRole();
        $user = $authenticatedUser->fresh();

        if (! $user instanceof User || ! $user->hasBackofficeAccess()) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta no tiene acceso al backoffice.',
            ]);
        }

        return $user;
    }

    private function ensureConfiguredSuperAdminExists(string $email): void
    {
        $configuredSuperAdmin = BackofficeSuperAdminRegistry::findByEmail($email);

        if (! is_array($configuredSuperAdmin)) {
            return;
        }

        $data = [
            'email' => $configuredSuperAdmin['email'],
            'name' => $configuredSuperAdmin['name'],
            'password' => Hash::make($configuredSuperAdmin['password']),
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn('users', 'role')) {
            $data['role'] = 'SuperAdmin';
        }

        User::query()->updateOrCreate(
            ['email' => $configuredSuperAdmin['email']],
            $data,
        );
    }
}
