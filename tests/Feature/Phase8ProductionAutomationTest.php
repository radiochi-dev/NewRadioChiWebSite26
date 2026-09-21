<?php

namespace Tests\Feature;

use App\Actions\Automation\DispatchN8nWorkflowAction;
use App\Actions\Automation\GenerateOllamaTextAction;
use App\Models\AutomationLog;
use App\Models\User;
use App\Support\AutomationSignature;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Phase8ProductionAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_8_seeds_configured_super_admins_with_backoffice_access(): void
    {
        config()->set('backoffice.super_admins', [
            [
                'name' => 'Fernando Cardona Toro',
                'email' => 'fernandocardonatoro@gmail.com',
                'password' => 'c4c4v4c4$',
            ],
            [
                'name' => 'RadioChi Dev',
                'email' => 'radiochi.dev@gmail.com',
                'password' => 'c4c4v4c4$',
            ],
        ]);

        $this->seed(SuperAdminSeeder::class);

        $primary = User::query()->where('email', 'fernandocardonatoro@gmail.com')->firstOrFail();
        $secondary = User::query()->where('email', 'radiochi.dev@gmail.com')->firstOrFail();

        $this->assertTrue($primary->hasRole('super_admin'));
        $this->assertTrue($secondary->hasRole('super_admin'));
        $this->assertTrue($primary->hasBackofficeAccess());
        $this->assertTrue($secondary->hasBackofficeAccess());
        $this->get('/backoffice/login')->assertOk();
    }

    public function test_phase_8_accepts_signed_n8n_results_and_persists_automation_log(): void
    {
        config()->set('services.n8n.shared_secret', 'phase8-secret');
        config()->set('services.n8n.allowed_clock_skew', 300);

        $payload = [
            'event' => 'seo-refresh',
            'workflow' => 'seo-refresh',
            'status' => 'completed',
            'request_payload' => ['locale' => 'es'],
            'response_payload' => ['updated' => 3],
        ];

        $response = $this->withHeaders($this->signatureHeaders($payload))
            ->postJson('/api/internal/automation/n8n/results', $payload);

        $response->assertCreated()
            ->assertJsonPath('event', 'seo-refresh')
            ->assertJsonPath('status', 'completed');

        $this->assertDatabaseHas('automation_logs', [
            'integration' => 'n8n',
            'event' => 'seo-refresh',
            'status' => 'completed',
            'direction' => 'inbound',
        ]);
    }

    public function test_phase_8_rejects_unsigned_automation_calls(): void
    {
        config()->set('services.n8n.shared_secret', 'phase8-secret');

        $this->postJson('/api/internal/automation/n8n/results', [
            'event' => 'seo-refresh',
            'status' => 'completed',
        ])->assertUnauthorized();
    }

    public function test_phase_8_exposes_signed_public_payload_and_postgres_views(): void
    {
        config()->set('services.n8n.shared_secret', 'phase8-secret');
        config()->set('services.n8n.allowed_clock_skew', 300);

        $this->artisan('legacy:import-content')->assertExitCode(0);

        $payload = [];

        $response = $this->withHeaders(array_merge(
            $this->signatureHeaders($payload),
            ['Accept' => 'application/json'],
        ))->get('/api/internal/automation/public-home/es');

        $response->assertOk()
            ->assertJsonPath('content.home.name', 'RadioChi')
            ->assertJsonStructure([
                'content',
                'calendarData',
                'mediaData',
                'contactData',
                'events',
                'seo',
            ]);

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            $events = collect(\DB::select('select * from automation_public_events order by id asc'));
            $this->assertNotEmpty($events);
        }
    }

    public function test_phase_8_dispatches_signed_n8n_workflows_and_logs_outbound_calls(): void
    {
        config()->set('services.n8n.webhook_base_url', 'https://n8n.example.com/webhook');
        config()->set('services.n8n.shared_secret', 'phase8-secret');
        config()->set('services.n8n.api_key', 'n8n-key');
        config()->set('services.n8n.timeout', 15);

        Http::fake([
            'https://n8n.example.com/webhook/*' => Http::response(['queued' => true], 200),
        ]);

        $log = app(DispatchN8nWorkflowAction::class)->execute('seo-refresh', [
            'event' => 'seo-refresh',
            'locale' => 'es',
        ]);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://n8n.example.com/webhook/seo-refresh'
                && $request->hasHeader('X-Radiochi-Key', 'n8n-key')
                && $request->hasHeader('X-Radiochi-Signature')
                && $request['locale'] === 'es';
        });

        $this->assertSame('n8n', $log->integration);
        $this->assertSame('queued', $log->status);
    }

    public function test_phase_8_calls_internal_ollama_service_with_timeout_and_logs_response(): void
    {
        config()->set('services.ollama.base_url', 'http://ollama:11434');
        config()->set('services.ollama.model', 'phi3:mini');
        config()->set('services.ollama.timeout', 45);
        config()->set('services.ollama.keep_alive', '5m');

        Http::fake([
            'http://ollama:11434/api/generate' => Http::response([
                'model' => 'phi3:mini',
                'response' => 'Resumen generado',
                'done' => true,
            ], 200),
        ]);

        $result = app(GenerateOllamaTextAction::class)->execute(
            event: 'editorial-summary',
            prompt: 'Resume este bloque para SEO',
            systemPrompt: 'Eres un asistente editorial.',
        );

        $this->assertSame('Resumen generado', $result['text']);
        $this->assertDatabaseHas('automation_logs', [
            'integration' => 'ollama',
            'event' => 'editorial-summary',
            'status' => 'completed',
            'direction' => 'outbound',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, string>
     */
    private function signatureHeaders(array $payload): array
    {
        return AutomationSignature::headers('phase8-secret', $payload, 'phase8-test');
    }
}
