<?php

namespace App\Filament\Resources\MediaAssets\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MediaAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de asset')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('disk')
                                    ->required()
                                    ->maxLength(60)
                                    ->default('public'),
                                TextInput::make('path')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('filename')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('mime_type')
                                    ->label('MIME type')
                                    ->maxLength(120),
                                TextInput::make('size')
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('width')
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('height')
                                    ->numeric()
                                    ->minValue(0),
                            ]),
                        Textarea::make('alt_text')
                            ->label('Alt text')
                            ->rows(3)
                            ->maxLength(255),
                        KeyValue::make('metadata')
                            ->label('Metadata')
                            ->keyLabel('Clave')
                            ->valueLabel('Valor'),
                    ]),
            ]);
    }
}
