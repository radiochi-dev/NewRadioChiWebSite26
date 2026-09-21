<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterLog;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\SeoMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase4EditorialBaselineTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_4_1_keeps_the_existing_editorial_tables_available(): void
    {
        $this->assertTrue(Schema::hasTable('pages'));
        $this->assertTrue(Schema::hasTable('page_translations'));
        $this->assertTrue(Schema::hasTable('events'));
        $this->assertTrue(Schema::hasTable('seo_meta'));
        $this->assertTrue(Schema::hasTable('newsletter_subscribers'));
        $this->assertTrue(Schema::hasTable('newsletter_campaigns'));
        $this->assertTrue(Schema::hasTable('newsletter_logs'));
    }

    public function test_phase_4_1_preserves_the_expected_columns_for_existing_editorial_tables(): void
    {
        $this->assertTrue(Schema::hasColumns('pages', [
            'slug',
            'template',
            'is_published',
            'published_at',
        ]));

        $this->assertTrue(Schema::hasColumns('page_translations', [
            'page_id',
            'locale',
            'title',
            'meta_title',
            'meta_description',
            'content',
        ]));

        $this->assertTrue(Schema::hasColumns('events', [
            'slug',
            'title',
            'excerpt',
            'body',
            'event_starts_at',
            'event_ends_at',
            'location',
            'external_url',
            'is_featured',
            'is_published',
            'published_at',
        ]));

        $this->assertTrue(Schema::hasColumns('seo_meta', [
            'entity_type',
            'entity_id',
            'locale',
            'meta_title',
            'meta_description',
            'canonical_url',
            'open_graph',
            'twitter_card',
            'json_ld',
        ]));

        $this->assertTrue(Schema::hasColumns('newsletter_subscribers', [
            'email',
            'name',
            'is_active',
            'subscribed_at',
            'unsubscribed_at',
        ]));

        $this->assertTrue(Schema::hasColumns('newsletter_campaigns', [
            'name',
            'subject',
            'html_body',
            'status',
            'scheduled_at',
            'sent_at',
            'sent_count',
        ]));

        $this->assertTrue(Schema::hasColumns('newsletter_logs', [
            'campaign_id',
            'subscriber_id',
            'status',
            'error_message',
            'processed_at',
        ]));
    }

    public function test_phase_4_1_models_stay_aligned_with_the_existing_editorial_schema(): void
    {
        $this->assertSame('pages', (new Page())->getTable());
        $this->assertSame('page_translations', (new PageTranslation())->getTable());
        $this->assertSame('events', (new Event())->getTable());
        $this->assertSame('seo_meta', (new SeoMeta())->getTable());
        $this->assertSame('newsletter_subscribers', (new NewsletterSubscriber())->getTable());
        $this->assertSame('newsletter_campaigns', (new NewsletterCampaign())->getTable());
        $this->assertSame('newsletter_logs', (new NewsletterLog())->getTable());
    }
}
