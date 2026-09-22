<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Backoffice\BackofficePath;
use App\Support\BackofficeSuperAdminRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'accepted_legal' => ['accepted'],
        ]);

        $configuredSuperAdmin = BackofficeSuperAdminRegistry::findByEmail($credentials['email']);

        if (is_array($configuredSuperAdmin)) {
            $user = User::query()->where('email', $configuredSuperAdmin['email'])->first();

            if (! $user) {
                $data = [
                    'email' => $configuredSuperAdmin['email'],
                    'name' => $configuredSuperAdmin['name'],
                    'password' => Hash::make($configuredSuperAdmin['password']),
                    'email_verified_at' => now(),
                ];

                if (Schema::hasColumn('users', 'role')) {
                    $data['role'] = 'SuperAdmin';
                }

                User::query()->create($data);
            }
        }

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], true)) {
            throw ValidationException::withMessages([
                'email' => 'Credenciales inválidas.',
            ]);
        }

        $request->session()->regenerate();

        $request->user()?->syncLegacyRoleToSpatieRole();

        return redirect()->intended(BackofficePath::official());
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
