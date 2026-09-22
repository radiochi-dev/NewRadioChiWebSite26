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
            'location' => 'footer',
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
                'location' => 'contact',
                'position' => 1,
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('media_assets', ['path' => 'media/phase7-photo.jpg', 'filename' => 'phase7-photo.jpg']);
        $this->assertDatabaseHas('partners', ['slug' => 'phase7-partner', 'partner_type' => 'media']);
        $this->assertDatabaseHas('social_links', ['platform' => 'spotify', 'location' => 'contact']);
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

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($roleName, 'web'));

        return $user;
    }
}

