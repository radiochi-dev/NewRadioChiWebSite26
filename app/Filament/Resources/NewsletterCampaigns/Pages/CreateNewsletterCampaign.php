<?php

namespace App\Filament\Resources\NewsletterCampaigns\Pages;

use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterCampaign extends CreateRecord
{
    protected static string $resource = NewsletterCampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'draft';

        return $data;
    }
}
