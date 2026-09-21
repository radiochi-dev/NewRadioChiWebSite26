<?php

namespace App\Actions\Automation;

use App\Models\AutomationLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GenerateOllamaTextAction
{
    public function __construct(
        private readonly LogAutomationExchangeAction $logAutomationExchange,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     * @return array{text:string,model:string,response:array<string,mixed>}
     */
    public function execute(string $event, string $prompt, ?string $systemPrompt = null, ?Model $reference = null, array $options = []): array
    {
        $baseUrl = rtrim((string) config('services.ollama.base_url', ''), '/');
        $model = (string) ($options['model'] ?? config('services.ollama.model', ''));
        $timeout = (int) ($options['timeout'] ?? config('services.ollama.timeout', 45));
        $keepAlive = (string) ($options['keep_alive'] ?? config('services.ollama.keep_alive', '5m'));

        if ($baseUrl === '' || $model === '') {
            throw new RuntimeException('Ollama configuration is incomplete.');
        }

        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
            'keep_alive' => $keepAlive,
        ];

        if ($systemPrompt !== null && $systemPrompt !== '') {
            $payload['system'] = $systemPrompt;
        }

        try {
            $response = Http::baseUrl($baseUrl)
                ->timeout($timeout)
                ->post('/api/generate', $payload);

            $response->throw();

            $json = $response->json();
            $text = (string) data_get($json, 'response', '');

            $this->logAutomationExchange->execute([
                'integration' => 'ollama',
                'event' => $event,
                'status' => 'completed',
                'direction' => 'outbound',
                'reference' => $reference,
                'request_payload' => $payload,
                'response_payload' => $json,
            ]);

            return [
                'text' => $text,
                'model' => $model,
                'response' => is_array($json) ? $json : [],
            ];
        } catch (RequestException $exception) {
            $response = $exception->response;

            $this->logAutomationExchange->execute([
                'integration' => 'ollama',
                'event' => $event,
                'status' => 'failed',
                'direction' => 'outbound',
                'reference' => $reference,
                'request_payload' => $payload,
                'response_payload' => $response?->json() ?? ['body' => $response?->body()],
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
