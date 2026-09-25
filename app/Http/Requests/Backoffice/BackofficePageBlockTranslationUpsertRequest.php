<?php

namespace App\Http\Requests\Backoffice;

use App\Support\BackofficeLocales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class BackofficePageBlockTranslationUpsertRequest extends FormRequest
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
            'content' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
        ];

        if (! $this->route('translation')) {
            $rules['locale'] = ['required', 'string', 'max:5', Rule::in(BackofficeLocales::values())];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('locale')) {
            $queryLocale = $this->query('locale');

            if (is_string($queryLocale) && in_array($queryLocale, BackofficeLocales::values(), true)) {
                $this->merge([
                    'locale' => $queryLocale,
                ]);
            }
        }

        if ($this->has('content') && is_string($this->input('content'))) {
            $this->merge([
                'content' => $this->decodeJsonField('content'),
            ]);
        }

        $typedPayload = $this->typedPayload();

        if ($typedPayload['content'] !== []) {
            $this->merge([
                'content' => $typedPayload['content'],
            ]);
        }

        if ($typedPayload['settings'] !== []) {
            $this->merge([
                'settings' => $typedPayload['settings'],
            ]);
        }
    }

    /**
     * @return array{content: array<string, mixed>, settings: array<string, mixed>}
     */
    private function typedPayload(): array
    {
        $contentMapping = [
            'title' => 'title',
            'subtitle' => 'subtitle',
            'description' => 'description',
            'button_text' => 'buttonText',
            'link' => 'link',
            'event' => 'event',
            'content_text' => 'content',
            'chart_title' => 'chartTitle',
            'chart_subtitle' => 'chartSubtitle',
        ];

        $settingsMapping = [
            'logo' => 'logo',
            'logo_position' => 'logoPosition',
            'person_image' => 'personImage',
            'elipse_image' => 'elipseImage',
            'image' => 'image',
            'image_secondary' => 'image2',
        ];

        $baseContent = $this->route('translation')?->content;
        $baseSettings = $this->route('pageBlock')?->settings;

        if (! is_array($baseContent)) {
            $baseContent = [];
        }

        if (! is_array($baseSettings)) {
            $baseSettings = [];
        }

        foreach ($contentMapping as $field => $key) {
            if (! $this->has($field)) {
                continue;
            }

            Arr::set($baseContent, $key, $this->input($field));
        }

        if ($this->has('words_text')) {
            $baseContent['words'] = collect(preg_split('/\r\n|\r|\n/', (string) $this->input('words_text')) ?: [])
                ->map(fn (string $word): string => trim($word))
                ->filter(fn (string $word): bool => $word !== '')
                ->values()
                ->all();
        }

        foreach ($settingsMapping as $field => $key) {
            if (! $this->has($field)) {
                continue;
            }

            Arr::set($baseSettings, $key, $this->input($field));
        }

        return [
            'content' => $baseContent,
            'settings' => $baseSettings,
        ];
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
}
