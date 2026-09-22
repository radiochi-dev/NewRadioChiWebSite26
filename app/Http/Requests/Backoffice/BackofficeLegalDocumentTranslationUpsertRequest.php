<?php

namespace App\Http\Requests\Backoffice;

use App\Support\BackofficeLocales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BackofficeLegalDocumentTranslationUpsertRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'cta_label' => ['nullable', 'string', 'max:255'],
        ];

        if (! $this->route('translation')) {
            $rules['locale'] = ['required', 'string', 'max:5', Rule::in(BackofficeLocales::values())];
        }

        return $rules;
    }
}
