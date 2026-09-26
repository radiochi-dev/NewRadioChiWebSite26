<?php

namespace App\Http\Requests\Backoffice;

use App\Support\Backoffice\CrudModuleBlueprintFactory;
use App\Support\Backoffice\Phase6ModuleCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BackofficePreviewIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasBackofficeAccess() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $module = (string) $this->route('module');
        $columns = Phase6ModuleCatalog::supports($module)
            ? Phase6ModuleCatalog::module($module)['columns']
            : CrudModuleBlueprintFactory::make($module)['columns'];

        $sortableColumns = array_map(
            fn (array $column): string => $column['key'],
            array_values(array_filter($columns, fn (array $column): bool => $column['sortable'] ?? false))
        );

        return [
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', Rule::in($sortableColumns)],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:50'],
            'entity_type' => ['nullable', 'string', 'max:255'],
            'entity_id' => ['nullable', 'integer', 'min:1'],
            'locale' => ['nullable', 'string', 'max:5'],
            'filters' => ['nullable', 'array'],
        ];
    }
}
