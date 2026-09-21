<?php

namespace App\Filament\Resources\SocialLinks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SocialLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Etiqueta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('platform')
                    ->label('Plataforma')
                    ->badge()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Ubicacion')
                    ->badge()
                    ->sortable(),
                TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->url),
                TextColumn::make('position')
                    ->label('Posicion')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->defaultSort('position')
            ->filters([
                SelectFilter::make('location')
                    ->options([
                        'global' => 'Global',
                        'contact' => 'Contact',
                        'footer' => 'Footer',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
