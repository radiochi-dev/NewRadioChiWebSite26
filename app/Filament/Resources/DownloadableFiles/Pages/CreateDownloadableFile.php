<?php

namespace App\Filament\Resources\DownloadableFiles\Pages;

use App\Filament\Resources\DownloadableFiles\DownloadableFileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDownloadableFile extends CreateRecord
{
    protected static string $resource = DownloadableFileResource::class;
}
