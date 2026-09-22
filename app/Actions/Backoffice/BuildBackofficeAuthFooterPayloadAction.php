<?php

namespace App\Actions\Backoffice;

use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Support\Collection;

class BuildBackofficeAuthFooterPayloadAction
{
    private const DEFAULT_LOCALE = 'es';

    private const SUPPORTED_LOCALES = ['es', 'en', 'ca', 'fr', 'it', 'de'];

    public function execute(?string $locale = null): array
    {
        $locale = $this->resolveLocale($locale);

        $settings = Setting::query()
            ->where('is_public', true)
            ->where('group', 'footer')
            ->whereIn('key', ['credits'])
            ->orWhere(function ($query): void {
                $query
                    ->where('is_public', true)
                    ->where('group', 'legal')
                    ->where('key', 'buttons');
            })
            ->with([
                'translations' => fn ($query) => $query->whereIn('locale', [$locale, self::DEFAULT_LOCALE]),
            ])
            ->get()
            ->keyBy(fn (Setting $setting) => "{$setting->group}.{$setting->key}");

        $socialLinks = SocialLink::query()
            ->where('is_active', true)
            ->where('location', 'footer')
            ->orderBy('position')
            ->get();

        return [
            'credits' => [
                'copyright' => data_get($this->settingValue($settings, 'footer', 'credits', $locale), 'copyright', '© 2025 Copyright.'),
                'rights' => data_get($this->settingValue($settings, 'footer', 'credits', $locale), 'rights', ''),
            ],
            'legalLinks' => [
                ['label' => data_get($this->settingValue($settings, 'legal', 'buttons', $locale), 'terms_button', 'Términos y Condiciones')],
                ['label' => data_get($this->settingValue($settings, 'legal', 'buttons', $locale), 'privacy_button', 'Política de Privacidad')],
                ['label' => data_get($this->settingValue($settings, 'legal', 'buttons', $locale), 'cookies_button', 'Política de Cookies')],
            ],
            'socialLinks' => $socialLinks
                ->map(fn (SocialLink $socialLink): array => [
                    'label' => $socialLink->label ?: ucfirst($socialLink->platform),
                    'url' => $socialLink->url,
                ])
                ->values()
                ->all(),
        ];
    }

    private function resolveLocale(?string $locale): string
    {
        if (is_string($locale) && in_array($locale, self::SUPPORTED_LOCALES, true)) {
            return $locale;
        }

        $appLocale = app()->getLocale();

        if (is_string($appLocale) && in_array($appLocale, self::SUPPORTED_LOCALES, true)) {
            return $appLocale;
        }

        return self::DEFAULT_LOCALE;
    }

    private function settingValue(Collection $settings, string $group, string $key, string $locale): array
    {
        /** @var Setting|null $setting */
        $setting = $settings->get("{$group}.{$key}");

        if (! $setting instanceof Setting) {
            return [];
        }

        if (! $setting->is_translatable) {
            return is_array($setting->value) ? $setting->value : [];
        }

        $translation = $setting->translations->firstWhere('locale', $locale)
            ?? $setting->translations->firstWhere('locale', self::DEFAULT_LOCALE)
            ?? $setting->translations->first();

        return is_array($translation?->value) ? $translation->value : [];
    }
}
