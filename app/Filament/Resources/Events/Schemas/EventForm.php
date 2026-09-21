<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del evento')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('title')
                                    ->label('Titulo')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('location')
                                    ->label('Ubicacion')
                                    ->maxLength(255),
                                TextInput::make('external_url')
                                    ->label('URL externa')
                                    ->url()
                                    ->maxLength(255),
                                DateTimePicker::make('event_starts_at')
                                    ->label('Inicio')
                                    ->native(false)
                                    ->seconds(false),
                                DateTimePicker::make('event_ends_at')
                                    ->label('Fin')
                                    ->native(false)
                                    ->seconds(false),
                                DateTimePicker::make('published_at')
                                    ->label('Publicado en')
                                    ->native(false)
                                    ->seconds(false),
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('is_featured')
                                            ->label('Destacado'),
                                        Toggle::make('is_published')
                                            ->label('Publicado'),
                                    ]),
                            ]),
                        Textarea::make('excerpt')
                            ->label('Extracto')
                            ->rows(4),
                        Textarea::make('body')
                            ->label('Contenido')
                            ->rows(10),
                    ])
                    ->columns(1),
            ]);
    }
}
