<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationLog;
use Illuminate\Http\JsonResponse;

class AutomationStatusController extends Controller
{
    public function __invoke(AutomationLog $automationLog): JsonResponse
    {
        return response()->json([
            'id' => $automationLog->id,
            'integration' => $automationLog->integration,
            'event' => $automationLog->event,
            'status' => $automationLog->status,
            'direction' => $automationLog->direction,
            'reference_type' => $automationLog->reference_type,
            'reference_id' => $automationLog->reference_id,
            'error_message' => $automationLog->error_message,
            'processed_at' => $automationLog->processed_at?->toIso8601String(),
            'created_at' => $automationLog->created_at?->toIso8601String(),
        ]);
    }
}
