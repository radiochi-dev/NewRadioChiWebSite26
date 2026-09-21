<?php

namespace App\Filament\Resources\RedirectRules\Schemas;

use App\Support\BackofficeLocales;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RedirectRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Regla de redireccion')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('source_path')
                                    ->label('Origen')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('destination_url')
                                    ->label('Destino')
                                    ->required()
                                    ->maxLength(65535),
                                Select::make('http_status')
                                    ->label('HTTP status')
                                    ->options([
                                        301 => '301',
                                        302 => '302',
                                        307 => '307',
                                        308 => '308',
                                    ])
                                    ->default(301)
                                    ->required(),
                                Select::make('locale')
                                    ->label('Locale')
                                    ->options(BackofficeLocales::options())
                                    ->native(false),
                                TextInput::make('hit_count')
                                    ->label('Hits')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true),
                            ]),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(4),
                    ]),
            ]);
    }
}
