<?php

namespace App\Http\Requests\Backoffice;

use App\Support\BackofficeLocales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BackofficeMusicTrackTranslationUpsertRequest extends FormRequest
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
            'artist_name' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cta_primary_label' => ['nullable', 'string', 'max:255'],
            'cta_secondary_label' => ['nullable', 'string', 'max:255'],
        ];

        if (! $this->route('translation')) {
            $rules['locale'] = ['required', 'string', 'max:5', Rule::in(BackofficeLocales::values())];
        }

        return $rules;
    }
}
