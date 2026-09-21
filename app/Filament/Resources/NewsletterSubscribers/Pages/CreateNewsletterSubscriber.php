<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Actions\Newsletter\PrepareNewsletterSubscriberData;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterSubscriber extends CreateRecord
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return app(PrepareNewsletterSubscriberData::class)->execute($data);
    }
}
