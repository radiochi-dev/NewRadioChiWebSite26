<?php

namespace App\Http\Requests\Backoffice;

use App\Support\BackofficeLocales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class BackofficeSettingTranslationUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageBackofficeContent() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'value' => ['nullable', 'array'],
        ];

        if (! $this->route('translation')) {
            $rules['locale'] = ['required', 'string', 'max:5', Rule::in(BackofficeLocales::values())];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('value') && is_string($this->input('value')) && $this->looksLikeJson((string) $this->input('value'))) {
            $this->merge([
                'value' => $this->decodeJsonField('value'),
            ]);

            return;
        }

        $typedValue = $this->typedValuePayload();

        if ($typedValue !== []) {
            $this->merge([
                'value' => $typedValue,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function typedValuePayload(): array
    {
        $mapping = [
            'label' => 'label',
            'menu_home' => 'items.home',
            'menu_about' => 'items.about',
            'menu_music' => 'items.music',
            'menu_media' => 'items.media',
            'menu_calendar' => 'items.calendarEvents',
            'menu_contact' => 'items.contact',
            'copyright' => 'copyright',
            'rights' => 'rights',
            'terms_button' => 'terms_button',
            'privacy_button' => 'privacy_button',
            'cookies_button' => 'cookies_button',
        ];

        $hasTypedInput = collect(array_keys($mapping))
            ->contains(fn (string $field): bool => $this->has($field));

        if (! $hasTypedInput && ! $this->has('value')) {
            return [];
        }

        $base = $this->route('translation')?->value;

        if (! is_array($base)) {
            $base = [];
        }

        foreach ($mapping as $field => $key) {
            if (! $this->has($field)) {
                continue;
            }

            Arr::set($base, $key, $this->input($field));
        }

        if ($this->has('value') && ! is_array($this->input('value'))) {
            Arr::set($base, 'value', $this->input('value'));
        }

        return $base;
    }

    private function decodeJsonField(string $field): ?array
    {
        $value = $this->input($field);

        if ($value === null || $value === '') {
            return null;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function looksLikeJson(string $value): bool
    {
        $trimmed = trim($value);

        return str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[');
    }
}
