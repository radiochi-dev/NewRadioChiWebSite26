<?php

namespace Tests\Feature;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\MediaAssets\MediaAssetResource;
use App\Filament\Resources\MediaAssets\Pages\CreateMediaAsset;
use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use App\Filament\Resources\NewsletterCampaigns\Pages\CreateNewsletterCampaign;
use App\Filament\Resources\NewsletterCampaigns\Pages\EditNewsletterCampaign;
use App\Filament\Resources\NewsletterCampaigns\Pages\ListNewsletterCampaigns;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Filament\Resources\NewsletterSubscribers\Pages\CreateNewsletterSubscriber;
use App\Filament\Resources\NewsletterSubscribers\Pages\EditNewsletterSubscriber;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\RelationManagers\TranslationsRelationManager;
use App\Filament\Resources\SeoMetas\Pages\CreateSeoMeta;
use App\Filament\Resources\SeoMetas\Pages\EditSeoMeta;
use App\Filament\Resources\SeoMetas\SeoMetaResource;
use App\Jobs\SendNewsletterCampaignJob;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\SeoMeta;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentEditorialResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_access_the_editorial_resources_index_routes(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor)
            ->get(EventResource::getUrl())
            ->assertOk();

        $this->actingAs($editor)
            ->get(PageResource::getUrl())
            ->assertOk();

        $this->actingAs($editor)
            ->get(MediaAssetResource::getUrl())
            ->assertOk();

        $this->actingAs($editor)
            ->get(SeoMetaResource::getUrl())
            ->assertOk();

        $this->actingAs($editor)
            ->get(NewsletterSubscriberResource::getUrl())
            ->assertOk();

        $this->actingAs($editor)
            ->get(NewsletterCampaignResource::getUrl())
            ->assertOk();
    }

    public function test_readonly_role_can_view_indexes_but_cannot_access_create_routes(): void
    {
        $readonly = $this->createUserWithRole('readonly');

        $this->actingAs($readonly)
            ->get(EventResource::getUrl())
            ->assertOk();

        $this->actingAs($readonly)
            ->get(EventResource::getUrl('create'))
            ->assertForbidden();

        $this->actingAs($readonly)
            ->get(PageResource::getUrl('create'))
            ->assertForbidden();

        $this->actingAs($readonly)
            ->get(MediaAssetResource::getUrl('create'))
            ->assertForbidden();

        $this->actingAs($readonly)
            ->get(SeoMetaResource::getUrl('create'))
            ->assertForbidden();

        $this->actingAs($readonly)
            ->get(NewsletterSubscriberResource::getUrl('create'))
            ->assertForbidden();

        $this->actingAs($readonly)
            ->get(NewsletterCampaignResource::getUrl('create'))
            ->assertForbidden();
    }

    public function test_editor_can_create_event_from_filament_resource(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor);

        Livewire::test(CreateEvent::class)
            ->fillForm([
                'slug' => 'filament-event',
                'title' => 'Filament Event',
                'location' => 'Barcelona',
                'is_featured' => true,
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('events', [
            'slug' => 'filament-event',
            'title' => 'Filament Event',
            'location' => 'Barcelona',
        ]);
    }

    public function test_editor_can_create_page_from_filament_resource(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor);

        Livewire::test(\App\Filament\Resources\Pages\Pages\CreatePage::class)
            ->fillForm([
                'slug' => 'filament-page',
                'template' => 'home',
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', [
            'slug' => 'filament-page',
            'template' => 'home',
            'is_published' => 1,
        ]);
    }

    public function test_editor_can_create_media_asset_from_filament_resource(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor);

        Livewire::test(CreateMediaAsset::class)
            ->fillForm([
                'disk' => 'public',
                'path' => 'media/poster.jpg',
                'filename' => 'poster.jpg',
                'mime_type' => 'image/jpeg',
                'size' => 4096,
                'width' => 1200,
                'height' => 628,
                'alt_text' => 'Poster principal',
                'metadata' => [
                    'source' => 'filament-test',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('media_assets', [
            'path' => 'media/poster.jpg',
            'filename' => 'poster.jpg',
            'mime_type' => 'image/jpeg',
        ]);
    }

    public function test_editor_can_create_newsletter_subscriber_from_filament_resource(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor);

        Livewire::test(CreateNewsletterSubscriber::class)
            ->fillForm([
                'email' => 'newsletter@example.com',
                'name' => 'RadioChi Fan',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'newsletter@example.com',
            'name' => 'RadioChi Fan',
            'is_active' => 1,
        ]);

        $this->assertNotNull(NewsletterSubscriber::query()->where('email', 'newsletter@example.com')->value('subscribed_at'));
    }

    public function test_editor_can_deactivate_newsletter_subscriber_and_set_unsubscribe_timestamp(): void
    {
        $editor = $this->createUserWithRole('editor');
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'subscriber@example.com',
            'name' => 'Existing Subscriber',
            'is_active' => true,
            'subscribed_at' => now()->subDay(),
        ]);

        $this->actingAs($editor);

        Livewire::test(EditNewsletterSubscriber::class, ['record' => $subscriber->getRouteKey()])
            ->fillForm([
                'email' => 'subscriber@example.com',
                'name' => 'Existing Subscriber',
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('newsletter_subscribers', [
            'id' => $subscriber->id,
            'is_active' => 0,
        ]);

        $this->assertNotNull($subscriber->fresh()->unsubscribed_at);
    }

    public function test_editor_can_create_newsletter_campaign_from_filament_resource(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor);

        Livewire::test(CreateNewsletterCampaign::class)
            ->fillForm([
                'name' => 'Lanzamiento Autumn',
                'subject' => 'Nuevo programa en RadioChi',
                'html_body' => '<p>Bienvenidos</p>',
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('newsletter_campaigns', [
            'name' => 'Lanzamiento Autumn',
            'subject' => 'Nuevo programa en RadioChi',
            'status' => 'draft',
        ]);
    }

    public function test_editor_can_queue_newsletter_campaign_from_filament_table_action(): void
    {
        Queue::fake();

        $editor = $this->createUserWithRole('editor');
        $campaign = NewsletterCampaign::query()->create([
            'name' => 'Launch Campaign',
            'subject' => 'RadioChi Launch',
            'html_body' => '<p>Launch body</p>',
            'status' => 'draft',
        ]);

        $this->actingAs($editor);

        Livewire::test(ListNewsletterCampaigns::class)
            ->callTableAction('queueCampaign', $campaign)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('newsletter_campaigns', [
            'id' => $campaign->id,
            'status' => 'queued',
        ]);

        Queue::assertPushed(SendNewsletterCampaignJob::class);
    }

    public function test_editor_can_create_seo_meta_from_filament_resource(): void
    {
        $editor = $this->createUserWithRole('editor');

        $this->actingAs($editor);

        Livewire::test(CreateSeoMeta::class)
            ->fillForm([
                'entity_type' => 'page',
                'entity_id' => 1,
                'locale' => 'es',
                'meta_title' => 'SEO Home',
                'meta_description' => 'Descripcion SEO principal',
                'canonical_url' => 'https://radiochi.test/',
                'open_graph' => json_encode([
                    'title' => 'OG Home',
                    'type' => 'website',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'twitter_card' => json_encode([
                    'card' => 'summary_large_image',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'json_ld' => json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('seo_meta', [
            'entity_type' => 'page',
            'entity_id' => 1,
            'locale' => 'es',
            'meta_title' => 'SEO Home',
        ]);
    }

    public function test_editor_can_upsert_existing_seo_meta_from_filament_resource(): void
    {
        $editor = $this->createUserWithRole('editor');

        SeoMeta::query()->create([
            'entity_type' => 'page',
            'entity_id' => 1,
            'locale' => 'es',
            'meta_title' => 'Old SEO',
        ]);

        $this->actingAs($editor);

        Livewire::test(CreateSeoMeta::class)
            ->fillForm([
                'entity_type' => 'page',
                'entity_id' => 1,
                'locale' => 'es',
                'meta_title' => 'Updated SEO',
                'meta_description' => 'Nueva descripcion',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('seo_meta', [
            'entity_type' => 'page',
            'entity_id' => 1,
            'locale' => 'es',
            'meta_title' => 'Updated SEO',
        ]);

        $this->assertSame(1, SeoMeta::query()
            ->where('entity_type', 'page')
            ->where('entity_id', 1)
            ->where('locale', 'es')
            ->count());
    }

    public function test_editor_can_update_seo_meta_from_filament_edit_page(): void
    {
        $editor = $this->createUserWithRole('editor');
        $seoMeta = SeoMeta::query()->create([
            'entity_type' => 'event',
            'entity_id' => 25,
            'locale' => 'en',
            'meta_title' => 'Event SEO',
            'canonical_url' => 'https://radiochi.test/en/events/25',
        ]);

        $this->actingAs($editor);

        Livewire::test(EditSeoMeta::class, ['record' => $seoMeta->getRouteKey()])
            ->fillForm([
                'entity_type' => 'event',
                'entity_id' => 25,
                'locale' => 'en',
                'meta_title' => 'Updated Event SEO',
                'meta_description' => 'Updated meta description',
                'canonical_url' => 'https://radiochi.test/en/events/updated-25',
                'open_graph' => json_encode([
                    'title' => 'Updated OG Event',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'twitter_card' => '',
                'json_ld' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('seo_meta', [
            'id' => $seoMeta->id,
            'meta_title' => 'Updated Event SEO',
            'canonical_url' => 'https://radiochi.test/en/events/updated-25',
        ]);
    }

    public function test_page_edit_page_renders_translations_relation_manager(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'home-page',
            'template' => 'home',
            'is_published' => true,
        ]);

        $this->actingAs($editor)
            ->get(PageResource::getUrl('edit', ['record' => $page]))
            ->assertOk()
            ->assertSeeLivewire(TranslationsRelationManager::class);
    }

    public function test_editor_can_create_page_translation_from_relation_manager(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'about-page',
            'template' => 'about',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('backoffice'));
        $this->actingAs($editor);

        Livewire::test(TranslationsRelationManager::class, [
            'ownerRecord' => $page,
            'pageClass' => EditPage::class,
        ])
            ->assertOk()
            ->callAction(TestAction::make(CreateAction::class)->table(), data: [
                'locale' => 'es',
                'title' => 'Sobre RadioChi',
                'meta_title' => 'Meta Sobre',
                'meta_description' => 'Descripcion editorial',
                'content' => json_encode([
                    'headline' => 'Hola',
                    'cta' => 'Escuchar ahora',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('page_translations', [
            'page_id' => $page->id,
            'locale' => 'es',
            'title' => 'Sobre RadioChi',
            'meta_title' => 'Meta Sobre',
        ]);
    }

    public function test_editor_can_update_page_translation_from_relation_manager(): void
    {
        $editor = $this->createUserWithRole('editor');
        $page = Page::query()->create([
            'slug' => 'contact-page',
            'template' => 'contact',
        ]);

        $translation = $page->translations()->create([
            'locale' => 'en',
            'title' => 'Contact',
            'meta_title' => 'Contact Meta',
            'meta_description' => 'Initial description',
            'content' => [
                'headline' => 'Contact us',
            ],
        ]);

        Filament::setCurrentPanel(Filament::getPanel('backoffice'));
        $this->actingAs($editor);

        Livewire::test(TranslationsRelationManager::class, [
            'ownerRecord' => $page,
            'pageClass' => EditPage::class,
        ])
            ->assertOk()
            ->callAction(TestAction::make(EditAction::class)->table($translation), data: [
                'locale' => 'en',
                'title' => 'Contact Updated',
                'meta_title' => 'Updated Meta',
                'meta_description' => 'Updated description',
                'content' => json_encode([
                    'headline' => 'Contact us updated',
                    'cta' => 'Write now',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('page_translations', [
            'id' => $translation->id,
            'title' => 'Contact Updated',
            'meta_title' => 'Updated Meta',
        ]);
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();

        $user->assignRole(Role::findByName($roleName, 'web'));

        return $user;
    }
}
