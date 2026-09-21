<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de pagina')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('template')
                                    ->required()
                                    ->maxLength(120),
                                DateTimePicker::make('published_at')
                                    ->label('Publicado en')
                                    ->native(false)
                                    ->seconds(false),
                                Toggle::make('is_published')
                                    ->label('Publicada'),
                            ]),
                    ]),
            ]);
    }
}
