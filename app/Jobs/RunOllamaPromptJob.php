<?php

namespace App\Jobs;

use App\Actions\Automation\GenerateOllamaTextAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunOllamaPromptJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        private readonly string $event,
        private readonly string $prompt,
        private readonly ?string $systemPrompt = null,
        private readonly array $options = [],
        private readonly ?string $referenceType = null,
        private readonly ?int $referenceId = null,
    ) {
        $this->onQueue('automation');
    }

    public function handle(GenerateOllamaTextAction $action): void
    {
        $reference = null;

        if (is_string($this->referenceType) && $this->referenceType !== '' && $this->referenceId !== null && class_exists($this->referenceType)) {
            $reference = $this->referenceType::query()->find($this->referenceId);
        }

        $action->execute($this->event, $this->prompt, $this->systemPrompt, $reference, $this->options);
    }
}
