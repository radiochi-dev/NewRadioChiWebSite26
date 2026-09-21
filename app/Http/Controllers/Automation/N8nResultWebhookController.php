<?php

namespace App\Http\Controllers\Automation;

use App\Actions\Automation\LogAutomationExchangeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Automation\StoreAutomationWebhookResultRequest;
use Illuminate\Http\JsonResponse;

class N8nResultWebhookController extends Controller
{
    public function __invoke(StoreAutomationWebhookResultRequest $request, LogAutomationExchangeAction $logAutomationExchange): JsonResponse
    {
        $validated = $request->validated();

        $log = $logAutomationExchange->execute([
            'integration' => 'n8n',
            'event' => (string) ($validated['workflow'] ?? $validated['event']),
            'status' => (string) $validated['status'],
            'direction' => 'inbound',
            'reference_type' => $validated['reference_type'] ?? null,
            'reference_id' => $validated['reference_id'] ?? null,
            'request_payload' => $validated['request_payload'] ?? null,
            'response_payload' => $validated['response_payload'] ?? null,
            'error_message' => $validated['error_message'] ?? null,
            'processed_at' => $validated['processed_at'] ?? now(),
        ]);

        return response()->json([
            'id' => $log->id,
            'status' => $log->status,
            'event' => $log->event,
        ], 201);
    }
}
