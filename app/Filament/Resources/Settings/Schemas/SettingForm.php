<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Setting global')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('group')
                                    ->label('Grupo')
                                    ->required()
                                    ->maxLength(120),
                                TextInput::make('key')
                                    ->label('Clave')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'string' => 'string',
                                        'json' => 'json',
                                        'boolean' => 'boolean',
                                        'number' => 'number',
                                        'url' => 'url',
                                        'html' => 'html',
                                    ])
                                    ->default('string')
                                    ->required(),
                                TextInput::make('position')
                                    ->label('Posicion')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Toggle::make('is_translatable')
                                    ->label('Translatable')
                                    ->default(false),
                                Toggle::make('is_public')
                                    ->label('Publico')
                                    ->default(false),
                            ]),
                        Textarea::make('value')
                            ->label('Value JSON')
                            ->rows(10)
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
