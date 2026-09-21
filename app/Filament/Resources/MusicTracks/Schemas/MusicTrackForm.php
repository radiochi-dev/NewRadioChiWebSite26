<?php

namespace App\Filament\Resources\MusicTracks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MusicTrackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Track musical')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Select::make('platform')
                                    ->label('Plataforma')
                                    ->options([
                                        'soundcloud' => 'SoundCloud',
                                        'spotify' => 'Spotify',
                                        'youtube' => 'YouTube',
                                        'apple_music' => 'Apple Music',
                                        'custom' => 'Custom',
                                    ])
                                    ->default('soundcloud')
                                    ->required(),
                                TextInput::make('label_image_path')
                                    ->label('Ruta label image')
                                    ->maxLength(255),
                                TextInput::make('cover_image_path')
                                    ->label('Ruta cover image')
                                    ->maxLength(255),
                                TextInput::make('stream_url')
                                    ->label('URL stream')
                                    ->url()
                                    ->maxLength(65535),
                                TextInput::make('external_url')
                                    ->label('URL externa')
                                    ->url()
                                    ->maxLength(65535),
                                TextInput::make('genre')
                                    ->label('Genero')
                                    ->maxLength(120),
                                TextInput::make('year')
                                    ->label('Ano')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(2100),
                                TextInput::make('position')
                                    ->label('Posicion')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                DateTimePicker::make('published_at')
                                    ->label('Publicado en')
                                    ->native(false)
                                    ->seconds(false),
                                Toggle::make('is_featured')
                                    ->label('Destacado')
                                    ->default(false),
                                Toggle::make('is_published')
                                    ->label('Publicado')
                                    ->default(true),
                            ]),
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
