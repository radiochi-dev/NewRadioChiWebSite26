<?php

namespace App\Actions\PublicSite;

use App\Models\Setting;

class ResolvePublicAnalyticsConfigAction
{
    public function execute(): array
    {
        $setting = Setting::query()
            ->where('group', 'analytics')
            ->where('key', 'ga4')
            ->where('is_public', true)
            ->first();

        $settingValue = $setting instanceof Setting && is_array($setting->value)
            ? $setting->value
            : [];

        $measurementId = $this->normalizeString(data_get($settingValue, 'measurement_id'))
            ?? $this->normalizeString(config('services.ga4.measurement_id'));

        $enabled = $this->toBoolean(config('services.ga4.enabled', false));
        $legalApproved = $this->toBoolean(config('services.ga4.legal_approved', false));

        return [
            'ga4' => [
                'enabled' => $enabled,
                'legalApproved' => $legalApproved,
                'measurementId' => $measurementId,
                'loadScript' => app()->environment('production') && $enabled && $legalApproved && $measurementId !== null,
            ],
        ];
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function toBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
