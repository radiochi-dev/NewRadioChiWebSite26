<?php

namespace App\Filament\Resources\NewsletterSubscribers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsletterSubscriberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del suscriptor')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('email')
                                    ->required()
                                    ->email()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->maxLength(255),
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true),
                                DateTimePicker::make('subscribed_at')
                                    ->label('Suscrito en')
                                    ->native(false)
                                    ->seconds(false)
                                    ->disabled()
                                    ->dehydrated(false),
                                DateTimePicker::make('unsubscribed_at')
                                    ->label('Baja en')
                                    ->native(false)
                                    ->seconds(false)
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
            ]);
    }
}
