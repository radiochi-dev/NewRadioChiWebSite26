<?php

namespace App\Http\Requests\Backoffice;

use App\Support\BackofficeLocales;
use Illuminate\Foundation\Http\FormRequest;
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
        if ($this->has('value') && is_string($this->input('value'))) {
            $this->merge([
                'value' => $this->decodeJsonField('value'),
            ]);
        }
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
