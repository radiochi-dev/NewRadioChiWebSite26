<?php

namespace App\Actions\Backoffice;

use App\Models\User;
use App\Support\Backoffice\PreviewModuleRegistry;

class BuildBackofficeNavigationAction
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(?User $user): array
    {
        if (! $user instanceof User || ! $user->hasBackofficeAccess()) {
            return [];
        }

        $navigation = [
            [
                'label' => 'General',
                'items' => [
                    [
                        'slug' => 'dashboard',
                        'label' => 'Dashboard',
                        'href' => '/backoffice',
                        'icon' => 'home',
                        'description' => 'Dashboard principal del nuevo backoffice React + Inertia.',
                    ],
                ],
            ],
            ...PreviewModuleRegistry::groups(),
        ];

        return $navigation;
    }
}
