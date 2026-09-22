<?php

namespace App\Http\Middleware;

use App\Actions\Backoffice\BuildBackofficeNavigationAction;
use App\Models\User;
use App\Support\Backoffice\BackofficePath;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $roles = $this->resolveRoles($user);
        $navigation = $user instanceof User
            ? app(BuildBackofficeNavigationAction::class)->execute($user)
            : [];

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ] : null,
                'roles' => $roles,
                'capabilities' => $user instanceof User ? [
                    'hasBackofficeAccess' => $user->hasBackofficeAccess(),
                    'canViewBackofficeContent' => $user->canViewBackofficeContent(),
                    'canManageBackofficeContent' => $user->canManageBackofficeContent(),
                    'isSuperAdmin' => $user->isSuperAdmin(),
                ] : [
                    'hasBackofficeAccess' => false,
                    'canViewBackofficeContent' => false,
                    'canManageBackofficeContent' => false,
                    'isSuperAdmin' => false,
                ],
            ],
            'backoffice' => [
                'branding' => [
                    'name' => 'RadioChi Backoffice',
                    'logo' => asset('assets/img/logos/RC_Logo_white.svg'),
                    'officialPrefix' => BackofficePath::official(),
                ],
                'locale' => app()->getLocale(),
                'navigation' => $navigation,
            ],
            'flash' => [
                'success' => fn (): ?string => $request->session()->get('success'),
                'error' => fn (): ?string => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolveRoles(mixed $user): array
    {
        if (! $user instanceof User) {
            return [];
        }

        $roles = [];

        if (method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames()->values()->all();
        }

        if (empty($roles) && $user->isLegacySuperAdmin()) {
            $roles[] = 'super_admin';
        }

        if (empty($roles) && is_string($user->role) && $user->role !== '') {
            $legacyRole = User::mapLegacyRoleToSpatieRole($user->role);

            if (is_string($legacyRole)) {
                $roles[] = $legacyRole;
            }
        }

        return array_values(array_unique($roles));
    }
}
