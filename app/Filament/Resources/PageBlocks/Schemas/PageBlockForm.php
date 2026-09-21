<?php

namespace App\Filament\Resources\PageBlocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bloque editorial')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('page_id')
                                    ->label('Pagina')
                                    ->relationship('page', 'slug')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('key')
                                    ->label('Clave')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('type')
                                    ->label('Tipo')
                                    ->required()
                                    ->maxLength(120),
                                TextInput::make('position')
                                    ->label('Posicion')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true),
                            ]),
                        Textarea::make('settings')
                            ->label('Settings JSON')
                            ->rows(10)
                            ->helperText('Mantiene la configuracion estructurada del bloque sin alterar el frontend publico.')
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
