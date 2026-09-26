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
            ->assertRedirect('/backoffice/pages');

        $this->actingAs($editor)
            ->get('/backoffice/settings')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'Configuracion del sitio')
                ->has('table.rows', 1));

        $this->actingAs($editor)
            ->get('/backoffice/seo-metas')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'SEO meta')
                ->has('form.localeActions', 6)
                ->where('form.localeActions.0.label', 'ES')
                ->where('form.sections.0.title', 'Metadata SEO · ES')
                ->missing('table')
                ->where('filters', []));
    }

    public function test_readonly_can_view_phase_6_indexes_but_cannot_access_create_routes(): void
    {
        $readonly = $this->createUserWithRole('readonly');

        foreach ([
            '/backoffice/events',
            '/backoffice/pages',
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
            '/backoffice/settings/create',
            '/backoffice/seo-metas/create',
        ] as $url) {
            $this->actingAs($readonly)
                ->get($url)
                ->assertForbidden();
        }

        $this->actingAs($readonly)
            ->get('/backoffice/page-blocks')
            ->assertRedirect('/backoffice/pages');

        $this->actingAs($readonly)
            ->get('/backoffice/page-blocks/create')
            ->assertRedirect('/backoffice/pages');
    }

    public function test_super_admin_can_manage_users_but_editor_cannot_open_the_module(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');
        $editor = $this->createUserWithRole('editor');
        $managedUser = User::factory()->create([
            'name' => 'Managed User',
            'email' => 'managed.user@gmail.com',
            'role' => 'Editor',
        ]);
        $managedUser->assignRole(Role::findOrCreate('editor', 'web'));

        $this->actingAs($superAdmin)
            ->get('/backoffice/users')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('title', 'Usuarios')
                ->where('capabilities.canCreate', true)
                ->has('table.rows', 4));

        $this->actingAs($editor)
            ->get('/backoffice/users')
            ->assertForbidden();

        $this->actingAs($editor)
            ->get('/backoffice/users/create')
            ->assertForbidden();
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
            ->assertRedirect('/backoffice/pages/phase6-page/edit');

        $page = Page::query()->where('slug', 'phase6-page')->firstOrFail();

        $this->postWithCsrf($editor, '/backoffice/pages/'.$page->slug.'/translations', [
                'locale' => 'es',
                'title' => 'Pagina Phase 6',
                'meta_title' => 'Meta fase 6',
                'meta_description' => 'Descripcion fase 6',
                'content' => json_encode(['headline' => 'Hola'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect('/backoffice/pages/'.$page->slug.'/edit');

        $translation = $page->translations()->firstOrFail();

        $this->assertDatabaseHas('page_translations', [
            'id' => $translation->id,
            'page_id' => $page->id,
            'locale' => 'es',
            'title' => 'Pagina Phase 6',
        ]);

        $this->postWithCsrf($editor, '/backoffice/pages/'.$page->slug.'/translations/'.$translation->id, [
                'title' => 'Pagina Phase 6 Updated',
                'meta_title' => 'Meta fase 6 updated',
                'meta_description' => 'Descripcion actualizada',
                'content' => json_encode(['headline' => 'Hola de nuevo'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertRedirect('/backoffice/pages/'.$page->slug.'/edit');

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
            ->get('/backoffice/pages/'.$page->slug.'/translations/create?locale=fr')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.defaults.locale', 'fr')
                ->missing('form.testChecklist')
                ->where('actions.0.label', 'Volver')
                ->has('form.localeActions', 6)
                ->where('form.localeActions.0.label', 'ES')
                ->where('form.localeActions.3.label', 'FR +'));

        $this->actingAs($editor)
            ->get('/backoffice/pages/'.$page->slug.'/translations/'.$translation->id.'/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.defaults.locale', 'es')
                ->where('form.localeActions.0.label', 'ES')
                ->where('form.localeActions.1.label', 'EN +'));
    }

    public function test_legacy_numeric_page_translation_edit_url_still_resolves_after_slug_cutover(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'legacy-page-route',
            'template' => 'home',
            'is_published' => true,
        ]);

        $translation = $page->translations()->create([
            'locale' => 'es',
            'title' => 'Legacy translation',
            'content' => ['headline' => 'Hola'],
        ]);

        $this->actingAs($editor)
            ->get('/backoffice/pages/'.$page->id.'/translations/'.$translation->id.'/edit?from='.$page->id.'&locale=es')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.defaults.locale', 'es'));
    }

    public function test_home_editor_lists_only_home_components_instead_of_other_pages(): void
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
            ->get('/backoffice/pages/'.$home->slug.'/edit?locale=en')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('title', 'Editar Home')
                ->where('form.hideModePanel', true)
                ->where('form.hideSubmit', true)
                ->where('form.relationManagersTitle', 'Componentes administrables de la pagina')
                ->has('form.tabbedEditors', 1)
                ->where('form.tabbedEditors.0.label', 'Hero / Home')
                ->where('form.tabbedEditors.0.createAction.label', 'Nuevo slide')
                ->where('form.tabbedEditors.0.tabs.0.label', 'Contenido base')
                ->has('form.relationManagers', 0)
                ->has('actions', 7));
    }

    public function test_editor_can_create_and_delete_home_slide_from_page_editor_surface(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'home',
            'template' => 'home',
            'is_published' => true,
        ]);

        $this->postWithCsrf($editor, '/backoffice/page-blocks/actions/create-block?record='.$page->id.'&type=hero_slide&locale=es&from=home')
            ->assertRedirect();

        $block = PageBlock::query()->where('page_id', $page->id)->where('type', 'hero_slide')->firstOrFail();

        $this->assertDatabaseHas('page_blocks', [
            'id' => $block->id,
            'page_id' => $page->id,
            'key' => 'hero-slide-01',
            'type' => 'hero_slide',
        ]);

        $this->postWithCsrf($editor, '/backoffice/page-blocks/actions/delete-block?record='.$block->id.'&locale=es&from=home')
            ->assertRedirect('/backoffice/pages/home/edit?locale=es&focus=page-'.$page->id);

        $this->assertDatabaseMissing('page_blocks', [
            'id' => $block->id,
        ]);
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
            ->get('/backoffice/pages/home/components/hero-slide-01/edit?locale=es&from=home')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.defaults.title', 'Bienvenidos')
                ->where('form.defaults.button_text', 'Escuchar')
                ->where('form.defaults.person_image', '/person.webp')
                ->where('form.defaults.logo_position', 'left')
                ->where('form.sections.0.fields.7.type', 'image')
                ->where('form.sections.0.fields.9.type', 'image')
                ->where('form.sections.0.fields.10.type', 'image')
                ->has('form.mediaLibrary')
                ->where('form.mediaUploadUrl', '/backoffice/media-assets/uploads/images')
                ->missing('form.testChecklist'));
    }

    public function test_component_editor_submits_without_numeric_page_block_urls(): void
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
            'settings' => [],
        ]);

        $this->postWithCsrf($editor, '/backoffice/pages/home/components/hero-slide-01?locale=es', [
                'locale' => 'es',
                'title' => 'Hero principal',
                'subtitle' => 'Subtitulo hero',
                'description' => 'Descripcion hero',
            ])
            ->assertRedirect('/backoffice/pages/home/components/hero-slide-01/edit?locale=es');

        $this->assertDatabaseHas('page_block_translations', [
            'page_block_id' => $block->id,
            'locale' => 'es',
        ]);
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
            ->assertRedirect('/backoffice/pages/about/components/about-step-01/edit?locale=en');

        $translation = $block->translations()->firstOrFail();

        $this->assertDatabaseHas('page_block_translations', [
            'id' => $translation->id,
            'page_block_id' => $block->id,
            'locale' => 'en',
        ]);
    }

    public function test_editor_can_create_setting_and_manage_translation_from_integrated_locale_editor(): void
    {
        $editor = $this->createUserWithRole('editor');

        $response = $this->postWithCsrf($editor, '/backoffice/settings/draft', [
                'group' => 'footer',
                'key' => 'credits',
                'type' => 'json',
                'position' => 1,
                'is_translatable' => true,
                'is_public' => true,
                'value' => json_encode(['label' => 'Credits'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

        $setting = Setting::query()->where('group', 'footer')->where('key', 'credits')->firstOrFail();
        $response->assertRedirect('/backoffice/settings/'.$setting->id.'/edit?locale=es');

        $this->actingAs($editor)
            ->get('/backoffice/settings/'.$setting->id.'/edit?locale=it')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.localeActions.0.label', 'ES')
                ->where('form.localeActions.4.label', 'IT +')
                ->where('form.sections.1.title', 'Contenido traducible · IT')
                ->where('form.sections.1.fields.0.key', 'copyright')
                ->where('form.sections.1.fields.1.key', 'rights'));

        $this->postWithCsrf($editor, '/backoffice/settings/draft/'.$setting->id.'?locale=it', [
                'group' => 'footer',
                'key' => 'credits',
                'type' => 'json',
                'position' => 1,
                'is_translatable' => true,
                'is_public' => true,
                'copyright' => 'RadioChi 2026',
                'rights' => 'Tutti i diritti riservati',
            ])
            ->assertRedirect('/backoffice/settings/'.$setting->id.'/edit?locale=it');

        $translation = $setting->translations()->where('locale', 'it')->firstOrFail();

        $this->assertDatabaseHas('settings_translations', [
            'id' => $translation->id,
            'setting_id' => $setting->id,
            'locale' => 'it',
        ]);
    }

    public function test_super_admin_can_create_and_update_user_from_backoffice_preview(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');

        $response = $this->postWithCsrf($superAdmin, '/backoffice/users/draft', [
            'name' => 'RadioChi Manager',
            'email' => 'radiochi.manager@gmail.com',
            'password' => 'secure-password-123',
            'role_name' => 'marketing',
            'email_verified_at' => '2026-09-26T10:30',
        ]);

        $user = User::query()->where('email', 'radiochi.manager@gmail.com')->firstOrFail();

        $response->assertRedirect('/backoffice/users/'.$user->id.'/edit');
        $this->assertTrue($user->fresh()->hasRole('marketing'));
        $this->assertSame('Marketing', $user->fresh()->role);

        $this->postWithCsrf($superAdmin, '/backoffice/users/draft/'.$user->id, [
            'name' => 'RadioChi Editor Lead',
            'email' => 'radiochi.manager@gmail.com',
            'password' => '',
            'role_name' => 'editor',
            'email_verified_at' => '2026-09-26T10:30',
        ])->assertRedirect('/backoffice/users/'.$user->id.'/edit');

        $user->refresh();

        $this->assertSame('RadioChi Editor Lead', $user->name);
        $this->assertTrue($user->hasRole('editor'));
        $this->assertSame('Editor', $user->role);
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

        $seoMeta = SeoMeta::query()
            ->where('entity_type', 'page')
            ->where('entity_id', 1)
            ->where('locale', 'es')
            ->firstOrFail();

        $this->actingAs($editor)
            ->get('/backoffice/seo-metas?entity_type=page&entity_id=1&locale=en')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleIndex')
                ->where('form.defaults.entity_type', 'page')
                ->where('form.defaults.entity_id', 1)
                ->where('form.defaults.locale', 'en')
                ->where('form.localeActions.1.label', 'EN +')
                ->where('form.sections.0.title', 'Metadata SEO · EN'));

        $this->actingAs($editor)
            ->get('/backoffice/seo-metas/'.$seoMeta->id.'/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.localeActions.0.label', 'ES')
                ->where('form.localeActions.1.label', 'EN +')
                ->has('form.sections', 1)
                ->where('form.sections.0.title', 'Metadata SEO · ES')
                ->where('form.sections.0.fields.0.key', 'meta_title'));

        $this->actingAs($editor)
            ->get('/backoffice/seo-metas/create?entity_type=page&entity_id=1&locale=en')
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Backoffice/Preview/ModuleForm')
                ->where('form.defaults.entity_type', 'page')
                ->where('form.defaults.entity_id', '1')
                ->where('form.defaults.locale', 'en')
                ->where('form.localeActions.0.label', 'ES')
                ->where('form.localeActions.1.label', 'EN +')
                ->has('form.sections', 1)
                ->where('form.sections.0.title', 'Metadata SEO · EN'));
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
