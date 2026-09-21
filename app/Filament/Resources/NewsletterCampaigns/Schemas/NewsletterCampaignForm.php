<?php

namespace App\Filament\Resources\NewsletterCampaigns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsletterCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de campana')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('subject')
                                    ->label('Asunto')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'draft' => 'draft',
                                        'queued' => 'queued',
                                        'sent' => 'sent',
                                        'cancelled' => 'cancelled',
                                    ])
                                    ->visibleOn('edit')
                                    ->required(),
                                DateTimePicker::make('scheduled_at')
                                    ->label('Programada para')
                                    ->native(false)
                                    ->seconds(false),
                                DateTimePicker::make('sent_at')
                                    ->label('Enviada en')
                                    ->native(false)
                                    ->seconds(false)
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('sent_count')
                                    ->label('Enviados')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                        Textarea::make('html_body')
                            ->label('HTML body')
                            ->required()
                            ->rows(16)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
