<?php

namespace App\Http\Controllers\Backoffice;

use App\Actions\Backoffice\AuthenticateBackofficeUserAction;
use App\Actions\Backoffice\BuildBackofficeAuthFooterPayloadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\BackofficeLoginRequest;
use App\Models\User;
use App\Support\Backoffice\BackofficePath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticateBackofficeUserAction $authenticateBackofficeUser,
        private readonly BuildBackofficeAuthFooterPayloadAction $authFooterPayload,
    ) {
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User && $user->hasBackofficeAccess()) {
            return redirect(BackofficePath::official());
        }

        $payload = [
            'title' => 'Entre a su cuenta',
            'description' => 'Acceso protegido al backoffice de RadioChi.',
            'footer' => $this->authFooterPayload->execute(),
        ];

        return Inertia::render('Backoffice/Auth/Login', $payload);
    }

    public function store(BackofficeLoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->authenticateBackofficeUser->execute(
            $request,
            $validated['email'],
            $validated['password'],
            (bool) ($validated['remember'] ?? false),
        );

        return redirect()->intended(BackofficePath::official());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(BackofficePath::official('login'));
    }
}
