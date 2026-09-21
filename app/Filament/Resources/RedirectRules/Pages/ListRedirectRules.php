<?php

namespace App\Filament\Resources\RedirectRules\Pages;

use App\Filament\Resources\RedirectRules\RedirectRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRedirectRules extends ListRecords
{
    protected static string $resource = RedirectRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
