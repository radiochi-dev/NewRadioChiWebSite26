<?php

namespace Tests\Feature;

use App\Filament\Resources\DownloadableFiles\DownloadableFileResource;
use App\Filament\Resources\DownloadableFiles\Pages\CreateDownloadableFile;
use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use App\Filament\Resources\LegalDocuments\Pages\CreateLegalDocument;
use App\Filament\Resources\LegalDocuments\Pages\EditLegalDocument;
use App\Filament\Resources\LegalDocuments\RelationManagers\TranslationsRelationManager as LegalDocumentTranslationsRelationManager;
use App\Filament\Resources\MusicTracks\MusicTrackResource;
use App\Filament\Resources\MusicTracks\Pages\CreateMusicTrack;
use App\Filament\Resources\MusicTracks\Pages\EditMusicTrack;
use App\Filament\Resources\MusicTracks\RelationManagers\TranslationsRelationManager as MusicTrackTranslationsRelationManager;
use App\Filament\Resources\PageBlocks\PageBlockResource;
use App\Filament\Resources\PageBlocks\Pages\CreatePageBlock;
use App\Filament\Resources\PageBlocks\Pages\EditPageBlock;
use App\Filament\Resources\PageBlocks\RelationManagers\TranslationsRelationManager as PageBlockTranslationsRelationManager;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\RelationManagers\BlocksRelationManager;
use App\Filament\Resources\Partners\Pages\CreatePartner;
use App\Filament\Resources\Partners\PartnerResource;
use App\Filament\Resources\RedirectRules\Pages\CreateRedirectRule;
use App\Filament\Resources\RedirectRules\RedirectRuleResource;
use App\Filament\Resources\Settings\Pages\CreateSetting;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Settings\RelationManagers\TranslationsRelationManager as SettingTranslationsRelationManager;
use App\Filament\Resources\Settings\SettingResource;
use App\Filament\Resources\SocialLinks\Pages\CreateSocialLink;
use App\Filament\Resources\SocialLinks\SocialLinkResource;
use App\Models\LegalDocument;
use App\Models\MusicTrack;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Setting;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Phase6CmsResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_access_phase_6_resource_indexes(): void
    {
        $editor = $this->createUserWithRole('editor');

        foreach ([
            PageBlockResource::getUrl(),
            MusicTrackResource::getUrl(),
            DownloadableFileResource::getUrl(),
            PartnerResource::getUrl(),
            SocialLinkResource::getUrl(),
            LegalDocumentResource::getUrl(),
            SettingResource::getUrl(),
            RedirectRuleResource::getUrl(),
        ] as $url) {
            $this->actingAs($editor)
                ->get($url)
                ->assertOk();
        }
    }

    public function test_readonly_role_cannot_access_phase_6_create_routes(): void
    {
        $readonly = $this->createUserWithRole('readonly');

        foreach ([
            PageBlockResource::getUrl('create'),
            MusicTrackResource::getUrl('create'),
            DownloadableFileResource::getUrl('create'),
            PartnerResource::getUrl('create'),
            SocialLinkResource::getUrl('create'),
            LegalDocumentResource::getUrl('create'),
            SettingResource::getUrl('create'),
            RedirectRuleResource::getUrl('create'),
        ] as $url) {
            $this->actingAs($readonly)
                ->get($url)
                ->assertForbidden();
        }
    }

    public function test_editor_can_create_phase_6_resources_from_filament(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'home',
            'template' => 'home',
            'is_published' => true,
        ]);

        $this->actingAs($editor);

        Livewire::test(CreatePageBlock::class)
            ->fillForm([
                'page_id' => $page->id,
                'key' => 'hero-slide-phase6',
                'type' => 'hero_slide',
                'position' => 1,
                'is_active' => true,
                'settings' => json_encode(['variant' => 'main'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateMusicTrack::class)
            ->fillForm([
                'slug' => 'phase6-track',
                'platform' => 'soundcloud',
                'stream_url' => 'https://soundcloud.com/radiochi/phase6-track',
                'external_url' => 'https://soundcloud.com/radiochi/phase6-track',
                'genre' => 'House',
                'year' => 2026,
                'position' => 1,
                'is_featured' => true,
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateDownloadableFile::class)
            ->fillForm([
                'slug' => 'phase6-press-kit',
                'display_name' => 'Phase 6 Press Kit',
                'disk' => 'public',
                'file_path' => 'press/phase6-kit.pdf',
                'file_name' => 'phase6-kit.pdf',
                'mime_type' => 'application/pdf',
                'size' => 2048,
                'collection' => 'press',
                'position' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreatePartner::class)
            ->fillForm([
                'slug' => 'phase6-sponsor',
                'name' => 'Phase 6 Sponsor',
                'partner_type' => 'sponsor',
                'website_url' => 'https://example.com/partner',
                'position' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateSocialLink::class)
            ->fillForm([
                'platform' => 'instagram',
                'label' => 'Instagram',
                'url' => 'https://instagram.com/radiochi',
                'icon_key' => 'instagram',
                'location' => 'footer',
                'position' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateLegalDocument::class)
            ->fillForm([
                'slug' => 'phase6-privacy',
                'document_type' => 'privacy',
                'version' => '2026.1',
                'position' => 1,
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateSetting::class)
            ->fillForm([
                'group' => 'seo',
                'key' => 'sitemap_rules',
                'type' => 'json',
                'position' => 1,
                'is_translatable' => false,
                'is_public' => false,
                'value' => json_encode(['exclude' => ['/drafts']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateRedirectRule::class)
            ->fillForm([
                'source_path' => '/old-home',
                'destination_url' => '/home',
                'http_status' => 301,
                'locale' => 'es',
                'is_active' => true,
                'hit_count' => 0,
                'notes' => 'Legacy home redirect',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('page_blocks', ['key' => 'hero-slide-phase6', 'type' => 'hero_slide']);
        $this->assertDatabaseHas('music_tracks', ['slug' => 'phase6-track', 'platform' => 'soundcloud']);
        $this->assertDatabaseHas('downloadable_files', ['slug' => 'phase6-press-kit', 'display_name' => 'Phase 6 Press Kit']);
        $this->assertDatabaseHas('partners', ['slug' => 'phase6-sponsor', 'name' => 'Phase 6 Sponsor']);
        $this->assertDatabaseHas('social_links', ['platform' => 'instagram', 'location' => 'footer']);
        $this->assertDatabaseHas('legal_documents', ['slug' => 'phase6-privacy', 'document_type' => 'privacy']);
        $this->assertDatabaseHas('settings', ['group' => 'seo', 'key' => 'sitemap_rules']);
        $this->assertDatabaseHas('redirect_rules', ['source_path' => '/old-home', 'destination_url' => '/home']);
    }

    public function test_page_edit_page_renders_blocks_relation_manager(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'contact',
            'template' => 'contact',
        ]);

        $this->actingAs($editor)
            ->get(PageResource::getUrl('edit', ['record' => $page]))
            ->assertOk();

        Filament::setCurrentPanel(Filament::getPanel('backoffice'));

        Livewire::test(BlocksRelationManager::class, [
            'ownerRecord' => $page,
            'pageClass' => EditPage::class,
        ])
            ->assertOk()
            ->assertTableColumnExists('key')
            ->assertTableColumnExists('type');
    }

    public function test_editor_can_create_page_block_translation_from_relation_manager(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create(['slug' => 'about', 'template' => 'about']);
        $block = PageBlock::query()->create([
            'page_id' => $page->id,
            'key' => 'about-step-01',
            'type' => 'about_step',
            'position' => 1,
            'is_active' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('backoffice'));
        $this->actingAs($editor);

        Livewire::test(PageBlockTranslationsRelationManager::class, [
            'ownerRecord' => $block,
            'pageClass' => EditPageBlock::class,
        ])
            ->assertOk()
            ->callAction(TestAction::make(CreateAction::class)->table(), data: [
                'locale' => 'es',
                'content' => json_encode(['title' => 'Paso editorial'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('page_block_translations', [
            'page_block_id' => $block->id,
            'locale' => 'es',
        ]);
    }

    public function test_editor_can_create_music_track_translation_from_relation_manager(): void
    {
        $editor = $this->createUserWithRole('editor');
        $track = MusicTrack::query()->create([
            'slug' => 'phase6-track-translation',
            'platform' => 'soundcloud',
            'position' => 1,
            'is_published' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('backoffice'));
        $this->actingAs($editor);

        Livewire::test(MusicTrackTranslationsRelationManager::class, [
            'ownerRecord' => $track,
            'pageClass' => EditMusicTrack::class,
        ])
            ->assertOk()
            ->callAction(TestAction::make(CreateAction::class)->table(), data: [
                'locale' => 'en',
                'artist_name' => 'RadioChi',
                'title' => 'Phase 6 Track',
                'description' => 'Translated body',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('music_track_translations', [
            'music_track_id' => $track->id,
            'locale' => 'en',
            'title' => 'Phase 6 Track',
        ]);
    }

    public function test_editor_can_create_legal_document_translation_from_relation_manager(): void
    {
        $editor = $this->createUserWithRole('editor');
        $document = LegalDocument::query()->create([
            'slug' => 'phase6-terms',
            'document_type' => 'terms',
            'position' => 1,
            'is_published' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('backoffice'));
        $this->actingAs($editor);

        Livewire::test(LegalDocumentTranslationsRelationManager::class, [
            'ownerRecord' => $document,
            'pageClass' => EditLegalDocument::class,
        ])
            ->assertOk()
            ->callAction(TestAction::make(CreateAction::class)->table(), data: [
                'locale' => 'fr',
                'title' => 'Conditions',
                'summary' => 'Resume legal',
                'content' => '<p>Conditions legales</p>',
                'cta_label' => 'Lire',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('legal_document_translations', [
            'legal_document_id' => $document->id,
            'locale' => 'fr',
            'title' => 'Conditions',
        ]);
    }

    public function test_editor_can_create_setting_translation_from_relation_manager(): void
    {
        $editor = $this->createUserWithRole('editor');
        $setting = Setting::query()->create([
            'group' => 'footer',
            'key' => 'credits',
            'type' => 'json',
            'is_translatable' => true,
            'is_public' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('backoffice'));
        $this->actingAs($editor);

        Livewire::test(SettingTranslationsRelationManager::class, [
            'ownerRecord' => $setting,
            'pageClass' => EditSetting::class,
        ])
            ->assertOk()
            ->callAction(TestAction::make(CreateAction::class)->table(), data: [
                'locale' => 'it',
                'value' => json_encode(['label' => 'Diritti riservati'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('settings_translations', [
            'setting_id' => $setting->id,
            'locale' => 'it',
        ]);
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();

        $user->assignRole(Role::findByName($roleName, 'web'));

        return $user;
    }
}
