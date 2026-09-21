<?php

namespace App\Http\Requests\Automation;

use Illuminate\Foundation\Http\FormRequest;

class StoreAutomationWebhookResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'event' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:100'],
            'workflow' => ['nullable', 'string', 'max:255'],
            'reference_type' => ['nullable', 'string', 'max:255'],
            'reference_id' => ['nullable', 'integer'],
            'request_payload' => ['nullable', 'array'],
            'response_payload' => ['nullable', 'array'],
            'error_message' => ['nullable', 'string'],
            'processed_at' => ['nullable', 'date'],
        ];
    }
}
