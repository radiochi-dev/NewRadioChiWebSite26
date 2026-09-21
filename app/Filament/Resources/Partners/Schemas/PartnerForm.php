<?php

namespace App\Filament\Resources\Partners\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sponsor')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('partner_type')
                                    ->label('Tipo')
                                    ->options([
                                        'sponsor' => 'Sponsor',
                                        'partner' => 'Partner',
                                        'media' => 'Media',
                                    ])
                                    ->default('sponsor')
                                    ->required(),
                                TextInput::make('website_url')
                                    ->label('URL web')
                                    ->url()
                                    ->maxLength(65535),
                                TextInput::make('logo_path')
                                    ->label('Ruta logo')
                                    ->maxLength(255),
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
