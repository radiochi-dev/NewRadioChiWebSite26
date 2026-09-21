<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('location')
                    ->label('Ubicacion')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('event_starts_at')
                    ->label('Inicio')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
                IconColumn::make('is_published')
                    ->label('Publicado')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Publicado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('event_starts_at', 'desc')
            ->filters([
                TernaryFilter::make('is_featured')
                    ->label('Destacado'),
                TernaryFilter::make('is_published')
                    ->label('Publicado'),
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
