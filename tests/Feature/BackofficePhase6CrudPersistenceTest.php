<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\LegalDocument;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\SeoMeta;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficePhase6CrudPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_view_real_phase_6_indexes(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'home',
            'template' => 'home',
            'is_published' => true,
        ]);

        Event::query()->create([
            'slug' => 'phase6-event',
            'title' => 'Phase 6 Event',
            'location' => 'Barcelona',
            'is_featured' => true,
            'is_published' => true,
        ]);

        PageBlock::query()->create([
            'page_id' => $page->id,
            'key' => 'hero-1',
            'type' => 'hero_slide',
            'position' => 1,
            'is_active' => true,
        ]);

        Setting::query()->create([
            'group' => 'seo',
            'key' => 'robots',
            'type' => 'json',
            'is_public' => false,
        ]);

        SeoMeta::query()->create([
            'entity_type' => 'page',
            'entity_id' => $page->id,
            'locale' => 'es',
            'meta_title' => 'Home SEO',
        ]);

        $this->actingAs($editor)
            ->get('/backoffice/events')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'Eventos')
                ->has('table.rows', 1));

        $this->actingAs($editor)
            ->get('/backoffice/pages')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'Paginas')
                ->where('capabilities.canCreate', false)
                ->has('table.rows', 2)
                ->where('table.rows.0.cells.0.value', 'Home')
                ->where('table.rows.1.cells.0.value', 'Login'));

        $this->actingAs($editor)
            ->get('/backoffice/page-blocks')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'Bloques de pagina')
                ->has('table.rows', 1));

        $this->actingAs($editor)
            ->get('/backoffice/settings')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'Settings')
                ->has('table.rows', 1));

        $this->actingAs($editor)
            ->get('/backoffice/seo-metas')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'SEO meta')
                ->has('table.rows', 1));
    }

    public function test_readonly_can_view_phase_6_indexes_but_cannot_access_create_routes(): void
    {
        $readonly = $this->createUserWithRole('readonly');

        foreach ([
            '/backoffice/events',
            '/backoffice/pages',
            '/backoffice/page-blocks',
            '/backoffice/settings',
            '/backoffice/seo-metas',
        ] as $url) {
            $this->actingAs($readonly)
                ->get($url)
                ->assertOk();
        }

        foreach ([
            '/backoffice/events/create',
            '/backoffice/pages/create',
            '/backoffice/page-blocks/create',
            '/backoffice/settings/create',
            '/backoffice/seo-metas/create',
        ] as $url) {
            $this->actingAs($readonly)
                ->get($url)
                ->assertForbidden();
        }
    }

    public function test_editor_can_create_and_update_event_from_backoffice_preview(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->postWithCsrf($editor, '/backoffice/events/draft', [
                'slug' => 'phase6-event',
                'title' => 'Phase 6 Event',
                'location' => 'Barcelona',
                'external_url' => 'https://example.com/events/phase6',
                'is_featured' => true,
                'is_published' => true,
            ])
            ->assertRedirect();

        $event = Event::query()->where('slug', 'phase6-event')->firstOrFail();

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Phase 6 Event',
            'location' => 'Barcelona',
        ]);

        $this->postWithCsrf($editor, '/backoffice/events/draft/'.$event->id, [
                'slug' => 'phase6-event',
                'title' => 'Phase 6 Event Updated',
                'location' => 'Madrid',
                'is_featured' => false,
                'is_published' => true,
            ])
            ->assertRedirect('/backoffice/events/'.$event->id.'/edit');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Phase 6 Event Updated',
            'location' => 'Madrid',
        ]);
    }

    public function test_editor_can_create_page_and_manage_page_translation_from_backoffice_preview(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->postWithCsrf($editor, '/backoffice/pages/draft', [
                'slug' => 'phase6-page',
                'template' => 'home',
                'is_published' => true,
            ])
            ->assertRedirect();

        $page = Page::query()->where('slug', 'phase6-page')->firstOrFail();

        $this->postWithCsrf($editor, '/backoffice/pages/'.$page->id.'/translations', [
                'locale' => 'es',
                'title' => 'Pagina Phase 6',
                'meta_title' => 'Meta fase 6',
                'meta_description' => 'Descripcion fase 6',
                'content' => json_encode(['headline' => 'Hola'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect('/backoffice/pages/'.$page->id.'/edit');

        $translation = $page->translations()->firstOrFail();

        $this->assertDatabaseHas('page_translations', [
            'id' => $translation->id,
            'page_id' => $page->id,
            'locale' => 'es',
            'title' => 'Pagina Phase 6',
        ]);

        $this->postWithCsrf($editor, '/backoffice/pages/'.$page->id.'/translations/'.$translation->id, [
                'title' => 'Pagina Phase 6 Updated',
                'meta_title' => 'Meta fase 6 updated',
                'meta_description' => 'Descripcion actualizada',
                'content' => json_encode(['headline' => 'Hola de nuevo'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect('/backoffice/pages/'.$page->id.'/edit');

        $this->assertDatabaseHas('page_translations', [
            'id' => $translation->id,
            'title' => 'Pagina Phase 6 Updated',
        ]);
    }

    public function test_page_translation_forms_expose_locale_buttons_and_prefill_requested_locale(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'phase6-multilang-page',
            'template' => 'home',
            'is_published' => true,
        ]);

        $translation = $page->translations()->create([
            'locale' => 'es',
            'title' => 'Pagina ES',
            'content' => ['headline' => 'Hola'],
        ]);

        $this->actingAs($editor)
            ->get('/backoffice/pages/'.$page->id.'/translations/create?locale=fr')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.defaults.locale', 'fr')
                ->missing('form.testChecklist')
                ->has('actions', 7));

        $this->actingAs($editor)
            ->get('/backoffice/pages/'.$page->id.'/translations/'.$translation->id.'/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.defaults.locale', 'es')
                ->where('actions.1.label', 'ES')
                ->where('actions.2.label', 'EN +'));
    }

    public function test_home_editor_lists_editorial_sections_instead_of_technical_relations(): void
    {
        $editor = $this->createUserWithRole('editor');

        foreach (['home', 'about', 'music', 'calendar', 'media', 'contact'] as $slug) {
            Page::query()->create([
                'slug' => $slug,
                'template' => $slug,
                'is_published' => true,
            ]);
        }

        Setting::query()->create(['group' => 'header', 'key' => 'open_menu', 'type' => 'json', 'is_translatable' => true, 'is_public' => true]);
        Setting::query()->create(['group' => 'header', 'key' => 'menu', 'type' => 'json', 'is_translatable' => true, 'is_public' => true]);
        Setting::query()->create(['group' => 'intro', 'key' => 'welcome', 'type' => 'json', 'is_translatable' => true, 'is_public' => true]);
        Setting::query()->create(['group' => 'footer', 'key' => 'credits', 'type' => 'json', 'is_translatable' => true, 'is_public' => true]);
        Setting::query()->create(['group' => 'legal', 'key' => 'buttons', 'type' => 'json', 'is_translatable' => true, 'is_public' => true]);
        Setting::query()->create(['group' => 'media', 'key' => 'youtube_channel_url', 'type' => 'json', 'is_translatable' => true, 'is_public' => true]);

        foreach (['terms', 'privacy', 'cookies'] as $position => $slug) {
            LegalDocument::query()->create([
                'slug' => $slug,
                'document_type' => $slug,
                'position' => $position + 1,
                'is_published' => true,
            ]);
        }

        $home = Page::query()->where('slug', 'home')->firstOrFail();

        $this->actingAs($editor)
            ->get('/backoffice/pages/'.$home->id.'/edit?locale=en')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('title', 'Editar Home')
                ->where('form.hideSubmit', true)
                ->where('form.relationManagers.0.label', 'Hero / Home')
                ->where('form.relationManagers.1.label', 'About')
                ->where('form.relationManagers.6.label', 'Navegacion y footer')
                ->where('form.relationManagers.7.label', 'Modales legales')
                ->has('actions', 7));
    }

    public function test_hero_block_translation_form_exposes_typed_fields_and_shared_assets(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'home',
            'template' => 'home',
            'is_published' => true,
        ]);

        $block = PageBlock::query()->create([
            'page_id' => $page->id,
            'key' => 'hero-slide-01',
            'type' => 'hero_slide',
            'position' => 1,
            'is_active' => true,
            'settings' => [
                'logo' => '/logo.svg',
                'logoPosition' => 'left',
                'personImage' => '/person.webp',
                'elipseImage' => '/elipse.webp',
            ],
        ]);

        $translation = $block->translations()->create([
            'locale' => 'es',
            'content' => [
                'title' => 'Bienvenidos',
                'subtitle' => 'Subtitulo',
                'description' => 'Descripcion',
                'buttonText' => 'Escuchar',
                'link' => 'https://example.com',
                'event' => '2026-06-03',
            ],
        ]);

        $this->actingAs($editor)
            ->get('/backoffice/page-blocks/'.$block->id.'/translations/'.$translation->id.'/edit?locale=es&from=home')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.defaults.title', 'Bienvenidos')
                ->where('form.defaults.button_text', 'Escuchar')
                ->where('form.defaults.person_image', '/person.webp')
                ->where('form.defaults.logo_position', 'left')
                ->missing('form.testChecklist'));
    }

    public function test_editor_can_create_page_block_and_manage_translation_from_backoffice_preview(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'about',
            'template' => 'about',
        ]);

        $this->postWithCsrf($editor, '/backoffice/page-blocks/draft', [
                'page_id' => $page->id,
                'key' => 'about-step-01',
                'type' => 'about_step',
                'position' => 1,
                'is_active' => true,
                'settings' => json_encode(['variant' => 'main'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect();

        $block = PageBlock::query()->where('key', 'about-step-01')->firstOrFail();

        $this->postWithCsrf($editor, '/backoffice/page-blocks/'.$block->id.'/translations', [
                'locale' => 'en',
                'content' => json_encode(['title' => 'About step'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect('/backoffice/page-blocks/'.$block->id.'/edit');

        $translation = $block->translations()->firstOrFail();

        $this->assertDatabaseHas('page_block_translations', [
            'id' => $translation->id,
            'page_block_id' => $block->id,
            'locale' => 'en',
        ]);
    }

    public function test_editor_can_create_setting_and_manage_translation_from_backoffice_preview(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->postWithCsrf($editor, '/backoffice/settings/draft', [
                'group' => 'footer',
                'key' => 'credits',
                'type' => 'json',
                'position' => 1,
                'is_translatable' => true,
                'is_public' => true,
                'value' => json_encode(['label' => 'Credits'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect();

        $setting = Setting::query()->where('group', 'footer')->where('key', 'credits')->firstOrFail();

        $this->postWithCsrf($editor, '/backoffice/settings/'.$setting->id.'/translations', [
                'locale' => 'it',
                'value' => json_encode(['label' => 'Crediti'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect('/backoffice/settings/'.$setting->id.'/edit');

        $translation = $setting->translations()->firstOrFail();

        $this->assertDatabaseHas('settings_translations', [
            'id' => $translation->id,
            'setting_id' => $setting->id,
            'locale' => 'it',
        ]);
    }

    public function test_editor_can_upsert_seo_meta_from_backoffice_preview(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->postWithCsrf($editor, '/backoffice/seo-metas/draft', [
                'entity_type' => 'page',
                'entity_id' => 1,
                'locale' => 'es',
                'meta_title' => 'SEO Home',
                'meta_description' => 'Descripcion SEO',
                'canonical_url' => 'https://radiochi.test/',
                'open_graph' => json_encode(['title' => 'OG'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'twitter_card' => json_encode(['card' => 'summary'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'json_ld' => json_encode(['@type' => 'WebSite'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect();

        $this->postWithCsrf($editor, '/backoffice/seo-metas/draft', [
                'entity_type' => 'page',
                'entity_id' => 1,
                'locale' => 'es',
                'meta_title' => 'SEO Home Updated',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('seo_meta', [
            'entity_type' => 'page',
            'entity_id' => 1,
            'locale' => 'es',
            'meta_title' => 'SEO Home Updated',
        ]);

        $this->assertSame(1, SeoMeta::query()
            ->where('entity_type', 'page')
            ->where('entity_id', 1)
            ->where('locale', 'es')
            ->count());
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($roleName, 'web'));

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function postWithCsrf(User $user, string $uri, array $data = [])
    {
        $token = 'csrf-backoffice-phase6-test';

        return $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->post($uri, array_merge(['_token' => $token], $data));
    }
}
