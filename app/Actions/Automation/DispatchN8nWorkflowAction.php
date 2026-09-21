<?php

namespace App\Actions\Automation;

use App\Models\AutomationLog;
use App\Support\AutomationSignature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DispatchN8nWorkflowAction
{
    public function __construct(
        private readonly LogAutomationExchangeAction $logAutomationExchange,
    ) {}

    public function execute(string $workflow, array $payload, ?Model $reference = null): AutomationLog
    {
        $baseUrl = rtrim((string) config('services.n8n.webhook_base_url', ''), '/');
        $sharedSecret = (string) config('services.n8n.shared_secret', '');
        $apiKey = (string) config('services.n8n.api_key', '');
        $timeout = (int) config('services.n8n.timeout', 15);

        if ($baseUrl === '' || $sharedSecret === '') {
            throw new RuntimeException('N8N webhook configuration is incomplete.');
        }

        $headers = AutomationSignature::headers($sharedSecret, $payload, $workflow);

        if ($apiKey !== '') {
            $headers['X-Radiochi-Key'] = $apiKey;
        }

        try {
            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->post($baseUrl.'/'.ltrim($workflow, '/'), $payload);

            $response->throw();

            return $this->logAutomationExchange->execute([
                'integration' => 'n8n',
                'event' => $workflow,
                'status' => 'queued',
                'direction' => 'outbound',
                'reference' => $reference,
                'request_payload' => $payload,
                'response_payload' => $response->json() ?? ['body' => $response->body()],
            ]);
        } catch (RequestException $exception) {
            $response = $exception->response;

            $this->logAutomationExchange->execute([
                'integration' => 'n8n',
                'event' => $workflow,
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
