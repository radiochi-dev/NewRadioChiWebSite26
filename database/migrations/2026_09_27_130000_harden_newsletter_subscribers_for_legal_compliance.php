<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropNewsletterAutomationView();

        DB::table('newsletter_subscribers')
            ->orderBy('id')
            ->get(['id', 'email'])
            ->each(function (object $subscriber): void {
                DB::table('newsletter_subscribers')
                    ->where('id', $subscriber->id)
                    ->update([
                        'email' => Str::lower(trim((string) $subscriber->email)),
                    ]);
            });

        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            $table->boolean('is_active')->default(false)->change();
            $table->string('confirmation_token', 64)->nullable()->after('unsubscribed_at');
            $table->string('unsubscribe_token', 64)->nullable()->after('confirmation_token');
            $table->string('ip_address', 45)->nullable()->after('unsubscribe_token');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('consent_text_version')->default('v1.0')->after('user_agent');
            $table->index('confirmation_token', 'newsletter_subscribers_confirmation_token_index');
            $table->unique('unsubscribe_token', 'newsletter_subscribers_unsubscribe_token_unique');
        });

        DB::table('newsletter_subscribers')
            ->orderBy('id')
            ->get(['id', 'unsubscribe_token'])
            ->each(function (object $subscriber): void {
                DB::table('newsletter_subscribers')
                    ->where('id', $subscriber->id)
                    ->update([
                        'unsubscribe_token' => $subscriber->unsubscribe_token ?: Str::random(64),
                    ]);
            });

        $this->createNewsletterAutomationView();
    }

    public function down(): void
    {
        $this->dropNewsletterAutomationView();

        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            $table->dropUnique('newsletter_subscribers_unsubscribe_token_unique');
            $table->dropIndex('newsletter_subscribers_confirmation_token_index');
            $table->dropColumn([
                'confirmation_token',
                'unsubscribe_token',
                'ip_address',
                'user_agent',
                'consent_text_version',
            ]);
            $table->boolean('is_active')->default(true)->change();
        });

        $this->createNewsletterAutomationView();
    }

    private function dropNewsletterAutomationView(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS automation_newsletter_subscribers');
    }

    private function createNewsletterAutomationView(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

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
    }
};
