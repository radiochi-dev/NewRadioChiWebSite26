<?php

namespace Tests\Feature;

use App\Models\AutomationLog;
use App\Models\DownloadableFile;
use App\Models\LegalDocument;
use App\Models\LegalDocumentTranslation;
use App\Models\MusicTrack;
use App\Models\MusicTrackTranslation;
use App\Models\Page;
use App\Models\Partner;
use App\Models\RedirectRule;
use App\Models\Setting;
use App\Models\SettingTranslation;
use App\Models\SocialLink;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase4RemainingContentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_4_2_adds_the_remaining_editorial_tables_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('music_tracks'));
        $this->assertTrue(Schema::hasTable('music_track_translations'));
        $this->assertTrue(Schema::hasTable('partners'));
        $this->assertTrue(Schema::hasTable('social_links'));
        $this->assertTrue(Schema::hasTable('legal_documents'));
        $this->assertTrue(Schema::hasTable('legal_document_translations'));
        $this->assertTrue(Schema::hasTable('downloadable_files'));
        $this->assertTrue(Schema::hasTable('redirect_rules'));
        $this->assertTrue(Schema::hasTable('settings'));
        $this->assertTrue(Schema::hasTable('settings_translations'));
        $this->assertTrue(Schema::hasTable('automation_logs'));

        $this->assertTrue(Schema::hasColumns('music_tracks', [
            'slug',
            'platform',
            'stream_url',
            'position',
            'is_featured',
            'is_published',
            'published_at',
            'settings',
        ]));

        $this->assertTrue(Schema::hasColumns('music_track_translations', [
            'music_track_id',
            'locale',
            'artist_name',
            'title',
            'description',
        ]));

        $this->assertTrue(Schema::hasColumns('partners', [
            'slug',
            'name',
            'partner_type',
            'website_url',
            'logo_path',
            'position',
            'is_active',
            'settings',
        ]));

        $this->assertTrue(Schema::hasColumns('social_links', [
            'platform',
            'label',
            'url',
            'icon_key',
            'location',
            'position',
            'is_active',
            'settings',
        ]));

        $this->assertTrue(Schema::hasColumns('legal_documents', [
            'slug',
            'document_type',
            'version',
            'position',
            'is_published',
            'published_at',
            'settings',
        ]));

        $this->assertTrue(Schema::hasColumns('legal_document_translations', [
            'legal_document_id',
            'locale',
            'title',
            'summary',
            'content',
            'cta_label',
        ]));

        $this->assertTrue(Schema::hasColumns('downloadable_files', [
            'slug',
            'display_name',
            'disk',
            'file_path',
            'external_url',
            'collection',
            'attachable_type',
            'attachable_id',
            'position',
            'is_active',
            'settings',
        ]));

        $this->assertTrue(Schema::hasColumns('redirect_rules', [
            'source_path',
            'destination_url',
            'http_status',
            'locale',
            'is_active',
            'notes',
            'hit_count',
        ]));

        $this->assertTrue(Schema::hasColumns('settings', [
            'group',
            'key',
            'type',
            'value',
            'is_translatable',
            'is_public',
            'position',
            'settings',
        ]));

        $this->assertTrue(Schema::hasColumns('settings_translations', [
            'setting_id',
            'locale',
            'value',
        ]));

        $this->assertTrue(Schema::hasColumns('automation_logs', [
            'integration',
            'event',
            'status',
            'direction',
            'reference_type',
            'reference_id',
            'request_payload',
            'response_payload',
            'error_message',
            'processed_at',
        ]));
    }

    public function test_phase_4_2_models_stay_aligned_with_the_remaining_editorial_schema(): void
    {
        $this->assertSame('music_tracks', (new MusicTrack())->getTable());
        $this->assertSame('music_track_translations', (new MusicTrackTranslation())->getTable());
        $this->assertSame('partners', (new Partner())->getTable());
        $this->assertSame('social_links', (new SocialLink())->getTable());
        $this->assertSame('legal_documents', (new LegalDocument())->getTable());
        $this->assertSame('legal_document_translations', (new LegalDocumentTranslation())->getTable());
        $this->assertSame('downloadable_files', (new DownloadableFile())->getTable());
        $this->assertSame('redirect_rules', (new RedirectRule())->getTable());
        $this->assertSame('settings', (new Setting())->getTable());
        $this->assertSame('settings_translations', (new SettingTranslation())->getTable());
        $this->assertSame('automation_logs', (new AutomationLog())->getTable());
    }

    public function test_phase_4_3_keeps_foreign_keys_and_cascade_deletes_operational(): void
    {
        $musicTrack = MusicTrack::query()->create([
            'slug' => 'radiochi-live',
            'platform' => 'soundcloud',
            'stream_url' => 'https://soundcloud.com/radiochi/live',
            'position' => 1,
            'is_featured' => true,
        ]);

        $musicTranslation = $musicTrack->translations()->create([
            'locale' => 'es',
            'artist_name' => 'RadioChi',
            'title' => 'Live Session',
        ]);

        $legalDocument = LegalDocument::query()->create([
            'slug' => 'privacy-policy',
            'document_type' => 'privacy',
            'version' => '2026.09',
            'position' => 1,
            'is_published' => true,
        ]);

        $legalTranslation = $legalDocument->translations()->create([
            'locale' => 'es',
            'title' => 'Politica de privacidad',
            'content' => '<p>Contenido legal</p>',
        ]);

        $setting = Setting::query()->create([
            'group' => 'contact',
            'key' => 'marquee_lines',
            'type' => 'json',
            'value' => ['default' => ['booking@radiochi.com']],
            'is_translatable' => true,
            'is_public' => true,
            'position' => 1,
        ]);

        $settingTranslation = $setting->translations()->create([
            'locale' => 'es',
            'value' => ['lines' => ['booking@radiochi.com', 'press@radiochi.com']],
        ]);

        $musicTrack->delete();
        $legalDocument->delete();
        $setting->delete();

        $this->assertDatabaseMissing('music_track_translations', ['id' => $musicTranslation->id]);
        $this->assertDatabaseMissing('legal_document_translations', ['id' => $legalTranslation->id]);
        $this->assertDatabaseMissing('settings_translations', ['id' => $settingTranslation->id]);
    }

    public function test_phase_4_3_operational_tables_support_polymorphic_and_indexed_content_records(): void
    {
        $page = Page::query()->create([
            'slug' => 'contact',
            'template' => 'contact',
        ]);

        $block = $page->blocks()->create([
            'key' => 'contact-marquee',
            'type' => 'contact_marquee',
            'position' => 1,
            'is_active' => true,
        ]);

        $partner = Partner::query()->create([
            'slug' => 'ibiza-global-radio',
            'name' => 'Ibiza Global Radio',
            'partner_type' => 'sponsor',
            'website_url' => 'https://example.com',
            'position' => 1,
            'is_active' => true,
            'settings' => ['tier' => 'gold'],
        ]);

        $socialLink = SocialLink::query()->create([
            'platform' => 'instagram',
            'label' => 'Instagram',
            'url' => 'https://instagram.com/radiochi',
            'icon_key' => 'instagram',
            'location' => 'global',
            'position' => 1,
            'is_active' => true,
            'settings' => ['target' => '_blank'],
        ]);

        $downloadableFile = DownloadableFile::query()->create([
            'slug' => 'radiochi-press-kit',
            'display_name' => 'Press Kit',
            'description' => 'Media pack',
            'disk' => 'public',
            'file_path' => 'press/radiochi-kit.pdf',
            'file_name' => 'radiochi-kit.pdf',
            'mime_type' => 'application/pdf',
            'size' => 2048,
            'collection' => 'press',
            'attachable_type' => $block::class,
            'attachable_id' => $block->id,
            'position' => 1,
            'is_active' => true,
            'settings' => ['visibility' => 'public'],
        ]);

        $redirectRule = RedirectRule::query()->create([
            'source_path' => '/old-contact',
            'destination_url' => '/contact',
            'http_status' => 301,
            'locale' => 'es',
            'is_active' => true,
            'notes' => 'Legacy redirect',
            'hit_count' => 0,
        ]);

        $automationLog = AutomationLog::query()->create([
            'integration' => 'n8n',
            'event' => 'newsletter.sync',
            'status' => 'processed',
            'direction' => 'outbound',
            'reference_type' => $partner::class,
            'reference_id' => $partner->id,
            'request_payload' => ['partner' => $partner->slug],
            'response_payload' => ['result' => 'ok'],
            'processed_at' => now(),
        ]);

        $this->assertSame($block->id, $downloadableFile->attachable->id);
        $this->assertSame($partner->id, $automationLog->reference->id);
        $this->assertSame('global', $socialLink->location);
        $this->assertSame(301, $redirectRule->http_status);
    }

    public function test_phase_4_4_preserves_unique_locale_constraints_for_translatable_satellite_tables(): void
    {
        $setting = Setting::query()->create([
            'group' => 'legal',
            'key' => 'footer_links',
            'type' => 'json',
            'value' => ['default' => []],
            'is_translatable' => true,
            'is_public' => true,
        ]);

        $setting->translations()->create([
            'locale' => 'es',
            'value' => ['items' => ['privacidad']],
        ]);

        $this->expectException(QueryException::class);

        $setting->translations()->create([
            'locale' => 'es',
            'value' => ['items' => ['cookies']],
        ]);
    }
}
