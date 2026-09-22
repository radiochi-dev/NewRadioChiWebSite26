<?php

namespace App\Http\Requests\Backoffice;

use App\Support\Backoffice\CrudModuleBlueprintFactory;
use App\Support\Backoffice\Phase6ModuleCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BackofficePreviewActionRequest extends FormRequest
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
        $module = (string) $this->route('module');
        $actionSlug = (string) $this->route('action');
        $action = Phase6ModuleCatalog::supports($module)
            ? Phase6ModuleCatalog::specialAction($module, $actionSlug)
            : CrudModuleBlueprintFactory::findAction($module, $actionSlug);

        abort_if(! is_array($action), 404);

        $rules = [
            'record' => ['nullable', 'string', 'max:255'],
        ];

        if (($action['requires_confirmation'] ?? false) === true) {
            $rules['confirmation'] = ['required', 'string', Rule::in([(string) $action['confirmation_phrase']])];
        }

        return $rules;
    }
}
