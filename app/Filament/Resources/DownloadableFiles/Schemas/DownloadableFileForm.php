<?php

namespace App\Filament\Resources\DownloadableFiles\Schemas;

use App\Models\Event;
use App\Models\LegalDocument;
use App\Models\MusicTrack;
use App\Models\PageBlock;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DownloadableFileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Archivo descargable')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('display_name')
                                    ->label('Nombre visible')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('disk')
                                    ->required()
                                    ->maxLength(60)
                                    ->default('public'),
                                TextInput::make('collection')
                                    ->label('Coleccion')
                                    ->maxLength(120),
                                TextInput::make('file_path')
                                    ->label('Ruta de archivo')
                                    ->maxLength(255),
                                TextInput::make('file_name')
                                    ->label('Nombre de archivo')
                                    ->maxLength(255),
                                TextInput::make('mime_type')
                                    ->label('MIME type')
                                    ->maxLength(120),
                                TextInput::make('size')
                                    ->label('Tamano bytes')
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('external_url')
                                    ->label('URL externa')
                                    ->url()
                                    ->maxLength(65535),
                                Select::make('attachable_type')
                                    ->label('Tipo relacionado')
                                    ->options([
                                        PageBlock::class => 'PageBlock',
                                        MusicTrack::class => 'MusicTrack',
                                        LegalDocument::class => 'LegalDocument',
                                        Event::class => 'Event',
                                    ])
                                    ->native(false),
                                TextInput::make('attachable_id')
                                    ->label('ID relacionado')
                                    ->numeric()
                                    ->minValue(1),
                                TextInput::make('position')
                                    ->label('Posicion')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true),
                            ]),
                        Textarea::make('description')
                            ->label('Descripcion')
                            ->rows(4),
                        Textarea::make('settings')
                            ->label('Settings JSON')
                            ->rows(8)
                            ->formatStateUsing(static fn (mixed $state): string => blank($state)
                                ? ''
                                : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                            ->dehydrateStateUsing(static function (?string $state): ?array {
                                if (blank($state)) {
                                    return null;
                                }

                                $decoded = json_decode($state, true);

                                return is_array($decoded) ? $decoded : null;
                            }),
                    ]),
            ]);
    }
}
