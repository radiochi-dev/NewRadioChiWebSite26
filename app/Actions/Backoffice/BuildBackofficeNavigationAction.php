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
                'items' => [[
                    'slug' => 'dashboard',
                    'label' => 'Dashboard',
                    'href' => '/backoffice',
                    'icon' => 'home',
                    'description' => 'Dashboard principal del nuevo backoffice React + Inertia.',
                ]],
            ],
            $this->group('Agenda', ['events']),
            $this->group('Editorial / contenido', ['pages']),
            $this->group('Media', ['media-assets', 'downloadable-files']),
            $this->group('Contacto', ['partners', 'social-links']),
            $this->group('Marketing', ['newsletter-subscribers', 'newsletter-campaigns']),
            $this->group('Legal y Footer', ['legal-documents']),
            $this->group('SEO', ['redirect-rules', 'seo-metas']),
            $this->group('Configuracion', ['settings']),
        ];

        return array_values(array_filter($navigation));
    }

    /**
     * @param  array<int, string>  $slugs
     * @return array<string, mixed>|null
     */
    private function group(string $label, array $slugs): ?array
    {
        $items = collect($slugs)
            ->map(fn (string $slug): ?array => PreviewModuleRegistry::find($slug))
            ->filter()
            ->map(fn (array $module): array => [
                'slug' => $module['slug'],
                'label' => $module['title'],
                'href' => '/backoffice/'.$module['slug'],
                'icon' => $module['icon'],
                'description' => $module['description'],
            ])
            ->values()
            ->all();

        if ($items === []) {
            return null;
        }

        return [
            'label' => $label,
            'items' => $items,
        ];
    }
}
