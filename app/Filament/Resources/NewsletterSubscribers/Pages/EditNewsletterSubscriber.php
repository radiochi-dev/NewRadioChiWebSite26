<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Actions\Newsletter\PrepareNewsletterSubscriberData;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsletterSubscriber extends EditRecord
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return app(PrepareNewsletterSubscriberData::class)->execute($data, $this->getRecord());
    }
}
