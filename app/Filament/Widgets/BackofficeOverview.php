<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\MediaAsset;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\SeoMeta;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BackofficeOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Eventos', Event::count())
                ->icon('heroicon-o-calendar-days')
                ->color('warning'),
            Stat::make('Paginas', Page::count())
                ->icon('heroicon-o-document-text')
                ->color('primary'),
            Stat::make('Media', MediaAsset::count())
                ->icon('heroicon-o-photo')
                ->color('info'),
            Stat::make('SEO', SeoMeta::count())
                ->icon('heroicon-o-globe-alt')
                ->color('success'),
            Stat::make('Suscriptores', NewsletterSubscriber::count())
                ->icon('heroicon-o-users')
                ->color('gray'),
            Stat::make('Campanas', NewsletterCampaign::count())
                ->icon('heroicon-o-megaphone')
                ->color('danger'),
        ];
    }
}
