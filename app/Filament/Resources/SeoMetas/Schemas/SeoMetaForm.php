<?php

namespace App\Filament\Resources\SeoMetas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SeoMetaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identidad SEO')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('entity_type')
                                    ->label('Entity type')
                                    ->required()
                                    ->disabledOn('edit')
                                    ->maxLength(255),
                                TextInput::make('entity_id')
                                    ->label('Entity ID')
                                    ->integer()
                                    ->required()
                                    ->disabledOn('edit')
                                    ->minValue(1),
                                Select::make('locale')
                                    ->label('Idioma')
                                    ->options([
                                        'es' => 'es',
                                        'en' => 'en',
                                        'ca' => 'ca',
                                        'fr' => 'fr',
                                        'it' => 'it',
                                        'de' => 'de',
                                    ])
                                    ->disabledOn('edit')
                                    ->required(),
                            ]),
                    ]),
                Section::make('Metadata')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta title')
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->label('Meta description')
                            ->rows(3),
                        TextInput::make('canonical_url')
                            ->label('Canonical URL')
                            ->url()
                            ->maxLength(255),
                    ]),
                Section::make('Payloads JSON')
                    ->schema([
                        Textarea::make('open_graph')
                            ->label('Open Graph JSON')
                            ->rows(8)
                            ->helperText('Mantiene la estructura JSON legacy actual.')
                            ->formatStateUsing(fn (mixed $state): string => self::formatJsonState($state))
                            ->dehydrateStateUsing(fn (?string $state): ?array => self::decodeJsonState($state)),
                        Textarea::make('twitter_card')
                            ->label('Twitter Card JSON')
                            ->rows(8)
                            ->helperText('Mantiene la estructura JSON legacy actual.')
                            ->formatStateUsing(fn (mixed $state): string => self::formatJsonState($state))
                            ->dehydrateStateUsing(fn (?string $state): ?array => self::decodeJsonState($state)),
                        Textarea::make('json_ld')
                            ->label('JSON-LD')
                            ->rows(10)
                            ->helperText('Mantiene la estructura JSON legacy actual.')
                            ->formatStateUsing(fn (mixed $state): string => self::formatJsonState($state))
                            ->dehydrateStateUsing(fn (?string $state): ?array => self::decodeJsonState($state)),
                    ]),
            ]);
    }

    public static function formatJsonState(mixed $state): string
    {
        if (blank($state)) {
            return '';
        }

        return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function decodeJsonState(?string $state): ?array
    {
        if (blank($state)) {
            return null;
        }

        $decoded = json_decode($state, true);

        return is_array($decoded) ? $decoded : null;
    }
}
