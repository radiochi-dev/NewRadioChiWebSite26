<?php

namespace App\Filament\Resources\DownloadableFiles\Pages;

use App\Filament\Resources\DownloadableFiles\DownloadableFileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDownloadableFile extends EditRecord
{
    protected static string $resource = DownloadableFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
