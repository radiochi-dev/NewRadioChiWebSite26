<?php

namespace App\Filament\Resources\MusicTracks\Pages;

use App\Filament\Resources\MusicTracks\MusicTrackResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMusicTrack extends EditRecord
{
    protected static string $resource = MusicTrackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
