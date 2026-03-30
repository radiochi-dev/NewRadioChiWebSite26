<?php

namespace Database\Seeders;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CalendarEventsSeeder extends Seeder
{
    public function run(): void
    {
        $events = $this->eventsFromLegacy();

        foreach ($events as $eventData) {
            Event::query()->updateOrCreate(
                ['slug' => $eventData['slug']],
                $eventData,
            );
        }
    }

    private function eventsFromLegacy(): array
    {
        $legacyPath = env('RADIOCHI_LEGACY_CALENDAR_PATH', 'C:\Users\fernandocardona\Documents\ContentWorkPC26\RadioChi-Website-2025\src\data\calendarevents.json');

        if (is_file($legacyPath)) {
            $content = file_get_contents($legacyPath);
            $parsed = json_decode($content ?: '', true);
            $legacyEvents = $parsed['events'] ?? [];

            if (is_array($legacyEvents) && count($legacyEvents) > 0) {
                return collect($legacyEvents)
                    ->map(function (array $legacyEvent): array {
                        $title = trim((string) ($legacyEvent['title'] ?? 'Evento'));
                        $country = trim((string) ($legacyEvent['country'] ?? ''));
                        $location = trim((string) ($legacyEvent['location'] ?? ''));
                        $startsAt = $this->normalizeLegacyDate($legacyEvent['dateStart'] ?? null);
                        $endsAt = $this->normalizeLegacyDate($legacyEvent['dateEnd'] ?? null);

                        return [
                            'slug' => Str::slug($title.' '.($legacyEvent['id'] ?? '')),
                            'title' => $title,
                            'excerpt' => $location,
                            'event_starts_at' => $startsAt,
                            'event_ends_at' => $endsAt,
                            'location' => trim($location.' '.($country !== '' ? ', '.$country : ''), ', '),
                            'external_url' => $legacyEvent['linkEvent'] ?? null,
                            'is_featured' => (bool) ($legacyEvent['id'] ?? false),
                            'is_published' => true,
                            'published_at' => now(),
                        ];
                    })
                    ->all();
            }
        }

        return [
            [
                'slug' => 'international-bear-convergence-2026',
                'title' => 'International Bear Convergence',
                'excerpt' => 'Palm Springs',
                'event_starts_at' => '2026-02-19 20:00:00',
                'event_ends_at' => '2026-02-23 20:00:00',
                'location' => 'Palm Springs, USA',
                'external_url' => 'https://www.ibcpalmsprings.com/',
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'slug' => 'sitges-pride-2026',
                'title' => 'Sitges Pride 2026',
                'excerpt' => 'Sitges',
                'event_starts_at' => null,
                'event_ends_at' => null,
                'location' => 'Sitges, Spain',
                'external_url' => 'https://sitgespride.com/',
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'slug' => 'sitges-bears-week-sept-2026',
                'title' => 'Sitges Bears Week Sept 2026',
                'excerpt' => 'Sitges',
                'event_starts_at' => null,
                'event_ends_at' => null,
                'location' => 'Sitges, Spain',
                'external_url' => 'https://bearssitges.org/bears-sitges-week/',
                'is_featured' => false,
                'is_published' => true,
                'published_at' => now(),
            ],
        ];
    }

    private function normalizeLegacyDate(?string $value): ?string
    {
        $cleaned = trim((string) $value);

        if ($cleaned === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-y', $cleaned)->startOfDay()->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
