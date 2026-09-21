<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Translatable\HasTranslations;
use Tests\TestCase;

class CmsBasePackagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_backoffice_redirects_guests_to_filament_login(): void
    {
        $response = $this->get('/backoffice');

        $response->assertRedirect();
        $this->assertStringContainsString('/backoffice/login', (string) $response->headers->get('Location'));
    }

    public function test_editor_role_can_access_backoffice_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findByName('editor', 'web'));

        $this->actingAs($user)
            ->get('/backoffice')
            ->assertOk();
    }

    public function test_legacy_super_admin_is_synced_to_spatie_role(): void
    {
        $user = User::query()->updateOrCreate([
            'email' => User::SUPER_ADMIN_EMAIL,
        ], [
            'name' => 'Fernando Cardona Toro',
            'password' => bcrypt('12345678'),
            'role' => 'SuperAdmin',
        ]);

        $user->syncLegacyRoleToSpatieRole();

        $this->assertTrue($user->fresh()->hasRole('super_admin'));

        $this->actingAs($user->fresh())
            ->get('/backoffice')
            ->assertOk();
    }

    public function test_event_model_can_store_media_with_medialibrary(): void
    {
        Storage::fake('public');

        $event = Event::query()->create([
            'slug' => 'cms-media-test',
            'title' => 'CMS Media Test',
        ]);

        $event
            ->addMedia(UploadedFile::fake()->create('poster.jpg', 64, 'image/jpeg'))
            ->toMediaCollection();

        $this->assertDatabaseHas('media', [
            'model_type' => Event::class,
            'model_id' => $event->id,
            'collection_name' => 'default',
        ]);
    }

    public function test_user_changes_are_logged_with_activitylog(): void
    {
        $user = User::factory()->create([
            'role' => 'Editor',
        ]);

        $before = Activity::query()->count();

        $user->update([
            'name' => 'Updated CMS User',
        ]);

        $this->assertGreaterThan($before, Activity::query()->count());
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
        ]);
    }

    public function test_spatie_translatable_is_ready_for_json_backed_models(): void
    {
        Schema::create('cms_translatable_fixtures', function (Blueprint $table): void {
            $table->id();
            $table->json('title')->nullable();
            $table->timestamps();
        });

        $model = new class extends Model
        {
            use HasTranslations;

            protected $table = 'cms_translatable_fixtures';

            protected $guarded = [];

            public array $translatable = ['title'];

            protected function casts(): array
            {
                return [
                    'title' => 'array',
                ];
            }
        };

        $record = $model->newQuery()->create([
            'title' => [
                'es' => 'Hola RadioChi',
                'en' => 'Hello RadioChi',
            ],
        ]);

        app()->setLocale('es');
        $this->assertSame('Hola RadioChi', $record->fresh()->title);

        app()->setLocale('en');
        $this->assertSame('Hello RadioChi', $record->fresh()->title);

        Schema::dropIfExists('cms_translatable_fixtures');
    }
}
