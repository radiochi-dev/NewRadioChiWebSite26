<?php

namespace App\Actions\Automation;

use App\Models\AutomationLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LogAutomationExchangeAction
{
    /**
     * @param  array{
     *     integration:string,
     *     event:string,
     *     status:string,
     *     direction?:string|null,
     *     reference?:Model|null,
     *     reference_type?:string|null,
     *     reference_id?:int|null,
     *     request_payload?:array|null,
     *     response_payload?:array|null,
     *     error_message?:string|null,
     *     processed_at?:Carbon|string|null
     * }  $data
     */
    public function execute(array $data): AutomationLog
    {
        /** @var Model|null $reference */
        $reference = $data['reference'] ?? null;

        $attributes = [
            'integration' => $data['integration'],
            'event' => $data['event'],
            'status' => $data['status'],
            'direction' => $data['direction'] ?? null,
            'request_payload' => $data['request_payload'] ?? null,
            'response_payload' => $data['response_payload'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'processed_at' => $data['processed_at'] ?? now(),
        ];

        if ($reference instanceof Model) {
            $attributes['reference_type'] = $reference->getMorphClass();
            $attributes['reference_id'] = $reference->getKey();
        } else {
            $attributes['reference_type'] = $data['reference_type'] ?? null;
            $attributes['reference_id'] = $data['reference_id'] ?? null;
        }

        return AutomationLog::query()->create($attributes);
    }
}
