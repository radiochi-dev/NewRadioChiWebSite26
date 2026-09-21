<?php

namespace App\Filament\Resources\NewsletterCampaigns\Pages;

use App\Actions\Newsletter\QueueNewsletterCampaign;
use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsletterCampaign extends EditRecord
{
    protected static string $resource = NewsletterCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('queueCampaign')
                ->label('Encolar')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->visible(fn (): bool => in_array($this->getRecord()->status, ['draft', 'cancelled'], true))
                ->requiresConfirmation()
                ->action(fn () => app(QueueNewsletterCampaign::class)->execute($this->getRecord())),
            DeleteAction::make(),
        ];
    }
}
