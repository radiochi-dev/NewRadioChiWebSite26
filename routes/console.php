<?php

use App\Actions\Automation\DispatchN8nWorkflowAction;
use App\Actions\Legacy\ImportLegacyContentAction;
use App\Jobs\SendNewsletterCampaignJob;
use App\Jobs\SyncInstagramFeedJob;
use App\Models\NewsletterCampaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('newsletter:send-campaign {campaignId}', function (int $campaignId) {
    $campaign = NewsletterCampaign::query()->findOrFail($campaignId);

    $campaign->update(['status' => 'queued']);
    SendNewsletterCampaignJob::dispatch($campaignId)->onQueue('newsletter');

    $this->info('Campaign queued: '.$campaignId);
})->purpose('Queue newsletter campaign delivery');

Artisan::command('instagram:sync', function () {
    SyncInstagramFeedJob::dispatch();

    $this->info('Instagram sync queued');
})->purpose('Queue Instagram feed synchronization');

Artisan::command('legacy:inventory-content', function (ImportLegacyContentAction $action) {
    $inventory = $action->inventory();

    $this->info('Legacy locales: '.implode(', ', $inventory['locales']));
    $this->line('Plan files: '.implode(', ', $inventory['plan_files']));
    $this->line('Used locale files: '.implode(', ', $inventory['used_locale_files']));
    $this->line('Data files: '.implode(', ', $inventory['data_files']));
    $this->line('Code sources: '.implode(', ', $inventory['code_sources']));
    $this->line('Empty data files: '.implode(', ', $inventory['empty_data_files']));

    if ($inventory['missing_locale_files'] !== [] || $inventory['missing_data_files'] !== [] || $inventory['missing_code_sources'] !== []) {
        $this->error('Legacy inventory is incomplete.');

        foreach (['missing_locale_files', 'missing_data_files', 'missing_code_sources'] as $key) {
            foreach ($inventory[$key] as $missingPath) {
                $this->line("- {$missingPath}");
            }
        }

        return self::FAILURE;
    }

    $this->info('Legacy inventory is complete.');

    return self::SUCCESS;
})->purpose('Audit the effective legacy content sources before importing');

Artisan::command('legacy:import-content', function (ImportLegacyContentAction $action) {
    $summary = $action->import();

    $this->info('Legacy content imported successfully.');
    $this->table(['Metric', 'Count'], [
        ['pages', $summary['pages']],
        ['page_translations', $summary['page_translations']],
        ['page_blocks', $summary['page_blocks']],
        ['page_block_translations', $summary['page_block_translations']],
        ['music_tracks', $summary['music_tracks']],
        ['music_track_translations', $summary['music_track_translations']],
        ['events', $summary['events']],
        ['media_assets', $summary['media_assets']],
        ['partners', $summary['partners']],
        ['social_links', $summary['social_links']],
        ['legal_documents', $summary['legal_documents']],
        ['legal_document_translations', $summary['legal_document_translations']],
        ['settings', $summary['settings']],
        ['settings_translations', $summary['settings_translations']],
        ['seo_meta', $summary['seo_meta']],
        ['downloadable_files', $summary['downloadable_files']],
    ]);

    return self::SUCCESS;
})->purpose('Import legacy JSON and hardcoded legacy content into CMS tables');

Artisan::command('automation:provision-database-access', function () {
    if (DB::getDriverName() !== 'pgsql') {
        $this->error('This command only supports PostgreSQL.');

        return self::FAILURE;
    }

    $username = (string) env('AUTOMATION_DB_READONLY_USERNAME', '');
    $password = (string) env('AUTOMATION_DB_READONLY_PASSWORD', '');
    $database = (string) env('DB_DATABASE', '');
    $views = [
        'automation_public_events',
        'automation_newsletter_subscribers',
        'automation_public_pages_seo',
    ];

    foreach (['username' => $username, 'database' => $database] as $label => $value) {
        if ($value === '' || ! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value)) {
            $this->error("Invalid {$label} for automation database provisioning.");

            return self::FAILURE;
        }
    }

    if ($password === '') {
        $this->error('AUTOMATION_DB_READONLY_PASSWORD is required.');

        return self::FAILURE;
    }

    $escapedPassword = str_replace("'", "''", $password);

    DB::unprepared(<<<SQL
        DO \$\$
        BEGIN
            IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = '{$username}') THEN
                CREATE ROLE {$username} LOGIN PASSWORD '{$escapedPassword}';
            ELSE
                ALTER ROLE {$username} WITH LOGIN PASSWORD '{$escapedPassword}';
            END IF;
        END
        \$\$;
    SQL);

    DB::statement("GRANT CONNECT ON DATABASE {$database} TO {$username}");
    DB::statement("GRANT USAGE ON SCHEMA public TO {$username}");
    DB::statement("REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA public FROM {$username}");
    DB::statement('GRANT SELECT ON '.implode(', ', $views)." TO {$username}");

    $this->info("Provisioned read-only automation user '{$username}' on approved views.");
    $this->line('Views: '.implode(', ', $views));

    return self::SUCCESS;
})->purpose('Provision the n8n read-only PostgreSQL role for approved automation views');

Artisan::command('automation:ping-n8n {workflow}', function (string $workflow, DispatchN8nWorkflowAction $action) {
    $log = $action->execute($workflow, [
        'event' => 'manual_ping',
        'requested_at' => now()->toIso8601String(),
    ]);

    $this->info("n8n workflow dispatched. Automation log: {$log->id}");

    return self::SUCCESS;
})->purpose('Send a signed test payload from Laravel to n8n');
