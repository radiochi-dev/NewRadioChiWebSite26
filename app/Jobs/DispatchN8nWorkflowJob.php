<?php

namespace App\Jobs;

use App\Actions\Automation\DispatchN8nWorkflowAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchN8nWorkflowJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly string $workflow,
        private readonly array $payload,
        private readonly ?string $referenceType = null,
        private readonly ?int $referenceId = null,
    ) {
        $this->onQueue('automation');
    }

    public function handle(DispatchN8nWorkflowAction $action): void
    {
        $reference = null;

        if (is_string($this->referenceType) && $this->referenceType !== '' && $this->referenceId !== null && class_exists($this->referenceType)) {
            $reference = $this->referenceType::query()->find($this->referenceId);
        }

        $action->execute($this->workflow, $this->payload, $reference);
    }
}
