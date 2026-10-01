<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->upsertSetting(
            'general',
            'site_profile',
            'json',
            [
                'site_title' => config('app.name'),
                'site_tagline' => '',
                'admin_email' => config('mail.from.address'),
                'timezone' => config('app.timezone'),
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
                'registration_enabled' => false,
            ],
            false,
            false,
            1,
        );

        $this->upsertSetting(
            'media',
            'upload_defaults',
            'json',
            [
                'organize_by_date' => true,
                'thumbnail' => ['width' => null, 'height' => null],
                'medium' => ['width' => null, 'height' => null],
                'large' => ['width' => null, 'height' => null],
            ],
            false,
            false,
            2,
        );

        $this->upsertSetting(
            'calendar',
            'visibility',
            'json',
            [
                'mode' => 'auto',
                'manual_enabled' => true,
                'minimum_upcoming_events' => 5,
                'lookahead_days' => 365,
            ],
            false,
            true,
            1,
        );

        $youtubeUrl = data_get(
            DB::table('settings')
                ->where('group', 'media')
                ->where('key', 'youtube_channel_url')
                ->value('value'),
            'value',
        );

        if (! is_string($youtubeUrl) || trim($youtubeUrl) === '') {
            $youtubeUrl = 'https://www.youtube.com/channel/TUCANALAQUI';
        }

        $existingYoutube = DB::table('social_links')
            ->where('platform', 'youtube')
            ->where('location', 'global')
            ->exists();

        if (! $existingYoutube) {
            $nextPosition = (int) DB::table('social_links')->max('position') + 1;

            DB::table('social_links')->insert([
                'platform' => 'youtube',
                'label' => 'YouTube',
                'url' => $youtubeUrl,
                'icon_key' => 'youtube',
                'location' => 'global',
                'position' => $nextPosition > 0 ? $nextPosition : 1,
                'is_active' => true,
                'settings' => json_encode(['source' => 'settings-backfill'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'general')
            ->where('key', 'site_profile')
            ->delete();

        DB::table('settings')
            ->where('group', 'media')
            ->where('key', 'upload_defaults')
            ->delete();

        DB::table('settings')
            ->where('group', 'calendar')
            ->where('key', 'visibility')
            ->delete();

        DB::table('social_links')
            ->where('platform', 'youtube')
            ->where('location', 'global')
            ->whereJsonContains('settings->source', 'settings-backfill')
            ->delete();
    }

    private function upsertSetting(
        string $group,
        string $key,
        string $type,
        array $value,
        bool $isTranslatable,
        bool $isPublic,
        int $position,
    ): void {
        $record = DB::table('settings')
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        $payload = [
            'type' => $type,
            'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_translatable' => $isTranslatable,
            'is_public' => $isPublic,
            'position' => $position,
            'updated_at' => now(),
        ];

        if ($record) {
            DB::table('settings')
                ->where('id', $record->id)
                ->update($payload);

            return;
        }

        DB::table('settings')->insert([
            'group' => $group,
            'key' => $key,
            ...$payload,
            'settings' => null,
            'created_at' => now(),
        ]);
    }
};
