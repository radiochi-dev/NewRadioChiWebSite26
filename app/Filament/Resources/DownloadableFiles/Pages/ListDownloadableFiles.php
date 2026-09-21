<?php

namespace App\Filament\Resources\DownloadableFiles\Pages;

use App\Filament\Resources\DownloadableFiles\DownloadableFileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDownloadableFiles extends ListRecords
{
    protected static string $resource = DownloadableFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
