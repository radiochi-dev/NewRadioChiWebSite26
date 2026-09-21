<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW automation_public_events AS
            SELECT
                id,
                slug,
                title,
                location,
                country,
                poster_path,
                external_url,
                event_starts_at,
                event_ends_at,
                published_at
            FROM events
            WHERE is_published = TRUE
        SQL);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW automation_newsletter_subscribers AS
            SELECT
                id,
                email,
                name,
                subscribed_at
            FROM newsletter_subscribers
            WHERE is_active = TRUE
        SQL);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW automation_public_pages_seo AS
            SELECT
                pages.id AS page_id,
                pages.slug,
                pages.template,
                pages.published_at,
                seo_meta.locale,
                seo_meta.meta_title,
                seo_meta.meta_description,
                seo_meta.canonical_url
            FROM pages
            LEFT JOIN seo_meta
                ON seo_meta.entity_type = 'App\\Models\\Page'
                AND seo_meta.entity_id = pages.id
            WHERE pages.is_published = TRUE
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS automation_public_pages_seo');
        DB::statement('DROP VIEW IF EXISTS automation_newsletter_subscribers');
        DB::statement('DROP VIEW IF EXISTS automation_public_events');
    }
};
