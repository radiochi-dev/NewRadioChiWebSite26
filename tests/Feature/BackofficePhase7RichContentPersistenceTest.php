<?php

namespace Tests\Feature;

use App\Models\DownloadableFile;
use App\Models\MediaAsset;
use App\Models\MusicTrack;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Partner;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficePhase7RichContentPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_view_real_phase_7_indexes(): void
    {
        $editor = $this->createUserWithRole('editor');
        $track = MusicTrack::query()->create([
            'slug' => 'phase7-track',
            'platform' => 'spotify',
            'position' => 1,
            'is_published' => true,
        ]);
        $track->translations()->create([
            'locale' => 'es',
            'title' => 'Track Phase 7',
        ]);

        MediaAsset::query()->create([
            'disk' => 'public',
            'path' => 'media/phase7-poster.jpg',
            'filename' => 'phase7-poster.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 4096,
        ]);

        Partner::query()->create([
            'slug' => 'phase7-sponsor',
            'name' => 'Phase 7 Sponsor',
            'partner_type' => 'sponsor',
            'position' => 1,
            'is_active' => true,
        ]);

        SocialLink::query()->create([
            'platform' => 'instagram',
            'label' => 'Instagram',
            'url' => 'https://instagram.com/radiochi',
            'location' => 'global',
            'position' => 1,
            'is_active' => true,
        ]);

        DownloadableFile::query()->create([
            'slug' => 'phase7-press-kit',
            'display_name' => 'Phase 7 Press Kit',
            'disk' => 'public',
            'position' => 1,
            'is_active' => true,
        ]);

        foreach ([
            '/backoffice/music-tracks' => 'Tracks musicales',
            '/backoffice/media-assets' => 'Assets',
            '/backoffice/partners' => 'Sponsors',
            '/backoffice/social-links' => 'Redes sociales',
            '/backoffice/downloadable-files' => 'Archivos descargables',
        ] as $url => $title) {
            $this->actingAs($editor)
                ->get($url)
                ->assertOk()
                ->assertInertia(fn (Assert $inertia) => $inertia
                    ->component('Backoffice/Preview/ModuleIndex')
                    ->where('title', $title)
                    ->has('table.rows', 1));
        }

        $this->actingAs($editor)
            ->get('/backoffice/media-assets')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'Assets')
                ->has('gallery.items', 1)
                ->where('gallery.items.0.filename', 'phase7-poster.jpg')
                ->where('gallery.items.0.formatKey', 'image'));
    }

    public function test_media_assets_gallery_filters_by_name_page_and_format(): void
    {
        $editor = $this->createUserWithRole('editor');

        $page = Page::query()->create([
            'slug' => 'home',
            'template' => 'home',
            'is_published' => true,
        ]);

        PageBlock::query()->create([
            'page_id' => $page->id,
            'key' => 'hero-slide-01',
            'type' => 'hero_slide',
            'position' => 1,
            'is_active' => true,
            'settings' => [
                'personImage' => '/storage/backoffice/media/2026/09/home-hero.webp',
            ],
        ]);

        MediaAsset::query()->create([
            'disk' => 'public',
            'path' => '/storage/backoffice/media/2026/09/home-hero.webp',
            'filename' => 'home-hero.webp',
            'mime_type' => 'image/webp',
            'size' => 2048,
        ]);

        MediaAsset::query()->create([
            'disk' => 'external',
            'path' => 'youtube:phase7-video',
            'filename' => 'phase7-video',
            'mime_type' => 'video/youtube',
            'metadata' => [
                'kind' => 'video',
                'youtube_id' => 'phase7-video',
                'thumbnail' => 'https://img.youtube.com/vi/phase7-video/hqdefault.jpg',
            ],
        ]);

        $this->actingAs($editor)
            ->get('/backoffice/media-assets?search=home&filters[assigned_page]=home&filters[format]=image')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('filters.0.value', 'home')
                ->where('filters.1.value', 'image')
                ->has('gallery.items', 1)
                ->where('gallery.items.0.filename', 'home-hero.webp')
                ->where('gallery.items.0.assignedPages.0', 'Home')
                ->where('gallery.items.0.formatKey', 'image'));
    }

    public function test_readonly_can_view_phase_7_indexes_but_cannot_access_create_routes(): void
    {
        $readonly = $this->createUserWithRole('readonly');

        foreach ([
            '/backoffice/music-tracks',
            '/backoffice/media-assets',
            '/backoffice/partners',
            '/backoffice/social-links',
            '/backoffice/downloadable-files',
        ] as $url) {
            $this->actingAs($readonly)->get($url)->assertOk();
        }

        foreach ([
            '/backoffice/music-tracks/create',
            '/backoffice/media-assets/create',
            '/backoffice/partners/create',
            '/backoffice/social-links/create',
            '/backoffice/downloadable-files/create',
        ] as $url) {
            $this->actingAs($readonly)->get($url)->assertForbidden();
        }
    }

    public function test_editor_can_create_music_track_and_manage_translation_from_backoffice_preview(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor)
            ->post('/backoffice/music-tracks/draft', [
                'slug' => 'phase7-track',
                'platform' => 'soundcloud',
                'stream_url' => 'https://soundcloud.com/radiochi/phase7-track',
                'external_url' => 'https://soundcloud.com/radiochi/phase7-track',
                'genre' => 'House',
                'year' => 2026,
                'position' => 1,
                'is_featured' => true,
                'is_published' => true,
                'settings' => json_encode(['legacy_id' => 701], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect();

        $track = MusicTrack::query()->where('slug', 'phase7-track')->firstOrFail();

        $this->actingAs($editor)
            ->get('/backoffice/music-tracks/'.$track->id.'/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->has('form.relationManagers', 1));

        $this->actingAs($editor)
            ->post('/backoffice/music-tracks/'.$track->id.'/translations', [
                'locale' => 'en',
                'artist_name' => 'RadioChi',
                'title' => 'Phase 7 Track',
                'description' => 'Translated body',
            ])
            ->assertRedirect('/backoffice/music-tracks/'.$track->id.'/edit');

        $translation = $track->translations()->firstOrFail();

        $this->assertDatabaseHas('music_track_translations', [
            'id' => $translation->id,
            'music_track_id' => $track->id,
            'locale' => 'en',
            'title' => 'Phase 7 Track',
        ]);
    }

    public function test_music_track_and_partner_forms_expose_image_fields_without_text_paths(): void
    {
        $editor = $this->createUserWithRole('editor');

        MediaAsset::query()->create([
            'disk' => 'public',
            'path' => '/assets/img/logos/example.webp',
            'filename' => 'example.webp',
            'mime_type' => 'image/webp',
            'size' => 1024,
            'width' => 600,
            'height' => 300,
        ]);

        $this->actingAs($editor)
            ->get('/backoffice/music-tracks/create')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.sections.0.fields.2.type', 'image')
                ->where('form.sections.0.fields.3.type', 'image')
                ->has('form.mediaLibrary', 1)
                ->where('form.mediaUploadUrl', '/backoffice/media-assets/uploads/images'));

        $this->actingAs($editor)
            ->get('/backoffice/partners/create')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.sections.0.fields.4.type', 'image')
                ->has('form.mediaLibrary', 1)
                ->where('form.mediaUploadUrl', '/backoffice/media-assets/uploads/images'));
    }

    public function test_editor_can_create_media_asset_partner_and_social_link_from_backoffice_preview(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor)
            ->post('/backoffice/media-assets/draft', [
                'disk' => 'public',
                'path' => 'media/phase7-photo.jpg',
                'filename' => 'phase7-photo.jpg',
                'mime_type' => 'image/jpeg',
                'size' => 8192,
                'width' => 1200,
                'height' => 628,
                'alt_text' => 'Foto phase 7',
                'metadata' => json_encode(['kind' => 'photo', 'caption' => 'Caption'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect();

        $this->actingAs($editor)
            ->post('/backoffice/partners/draft', [
                'slug' => 'phase7-partner',
                'name' => 'Phase 7 Partner',
                'partner_type' => 'media',
                'website_url' => 'https://example.com/partner',
                'logo_path' => '/logos/phase7.webp',
                'position' => 1,
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->actingAs($editor)
            ->post('/backoffice/social-links/draft', [
                'platform' => 'spotify',
                'label' => 'Spotify',
                'url' => 'https://spotify.com/radiochi',
                'icon_key' => 'spotify',
                'position' => 1,
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('media_assets', ['path' => 'media/phase7-photo.jpg', 'filename' => 'phase7-photo.jpg']);
        $this->assertDatabaseHas('partners', ['slug' => 'phase7-partner', 'partner_type' => 'media']);
        $this->assertDatabaseHas('social_links', ['platform' => 'spotify', 'location' => 'global']);
    }

    public function test_editor_cannot_create_duplicate_social_platforms_in_shared_source(): void
    {
        $editor = $this->createUserWithRole('editor');

        SocialLink::query()->create([
            'platform' => 'spotify',
            'label' => 'Spotify',
            'url' => 'https://spotify.com/original',
            'location' => 'global',
            'position' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($editor)
            ->from('/backoffice/social-links/create')
            ->post('/backoffice/social-links/draft', [
                'platform' => 'Spotify',
                'label' => 'Spotify duplicado',
                'url' => 'https://spotify.com/duplicated',
                'icon_key' => 'spotify',
                'position' => 2,
                'is_active' => true,
            ])
            ->assertRedirect('/backoffice/social-links/create');

        $this->assertDatabaseCount('social_links', 1);
        $this->assertDatabaseHas('social_links', [
            'platform' => 'spotify',
            'url' => 'https://spotify.com/original',
            'location' => 'global',
        ]);
    }

    public function test_editor_can_create_downloadable_file_with_polymorphic_attachment_from_backoffice_preview(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'contact',
            'template' => 'contact',
        ]);
        $block = PageBlock::query()->create([
            'page_id' => $page->id,
            'key' => 'contact-downloads',
            'type' => 'downloads',
            'position' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($editor)
            ->post('/backoffice/downloadable-files/draft', [
                'slug' => 'phase7-kit',
                'display_name' => 'Phase 7 Kit',
                'description' => 'Media pack',
                'disk' => 'public',
                'file_path' => 'press/phase7-kit.pdf',
                'file_name' => 'phase7-kit.pdf',
                'mime_type' => 'application/pdf',
                'size' => 2048,
                'collection' => 'press',
                'attachable_type' => PageBlock::class,
                'attachable_id' => $block->id,
                'position' => 1,
                'is_active' => true,
                'settings' => json_encode(['visibility' => 'public'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect();

        $file = DownloadableFile::query()->where('slug', 'phase7-kit')->firstOrFail();

        $this->assertDatabaseHas('downloadable_files', [
            'id' => $file->id,
            'attachable_type' => PageBlock::class,
            'attachable_id' => $block->id,
        ]);
    }

    public function test_downloadable_file_requires_existing_polymorphic_target_when_attachable_pair_is_sent(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor)
            ->post('/backoffice/downloadable-files/draft', [
                'slug' => 'phase7-invalid-kit',
                'display_name' => 'Phase 7 Invalid Kit',
                'disk' => 'public',
                'attachable_type' => PageBlock::class,
                'attachable_id' => 999999,
            ])
            ->assertSessionHasErrors('attachable_id');
    }

    public function test_editor_can_upload_image_to_backoffice_media_library(): void
    {
        Storage::fake('public');

        $editor = $this->createUserWithRole('editor');

        $pngFixture = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+iVhQAAAAASUVORK5CYII=');

        $response = $this->actingAs($editor)
            ->post('/backoffice/media-assets/uploads/images', [
                'image' => UploadedFile::fake()->createWithContent('hero-slide.png', $pngFixture),
            ]);

        $response->assertCreated();
        $this->assertTrue(str_ends_with((string) $response->json('asset.filename'), '.png'));
        $this->assertTrue(str_starts_with((string) $response->json('asset.previewUrl'), '/storage/backoffice/media/'));

        $asset = MediaAsset::query()->latest('id')->firstOrFail();

        Storage::disk('public')->assertExists(str_replace('/storage/', '', $asset->path));

        $this->assertDatabaseHas('media_assets', [
            'id' => $asset->id,
            'disk' => 'public',
            'mime_type' => 'image/png',
        ]);
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($roleName, 'web'));

        return $user;
    }
}
