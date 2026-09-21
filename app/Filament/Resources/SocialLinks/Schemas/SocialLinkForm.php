<?php

namespace App\Filament\Resources\SocialLinks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SocialLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Red social')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('platform')
                                    ->label('Plataforma')
                                    ->required()
                                    ->maxLength(120),
                                TextInput::make('label')
                                    ->label('Etiqueta')
                                    ->maxLength(255),
                                TextInput::make('url')
                                    ->label('URL')
                                    ->required()
                                    ->url()
                                    ->maxLength(65535),
                                TextInput::make('icon_key')
                                    ->label('Icon key')
                                    ->maxLength(120),
                                Select::make('location')
                                    ->label('Ubicacion')
                                    ->options([
                                        'global' => 'Global',
                                        'contact' => 'Contact',
                                        'footer' => 'Footer',
                                    ])
                                    ->default('global')
                                    ->required(),
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
