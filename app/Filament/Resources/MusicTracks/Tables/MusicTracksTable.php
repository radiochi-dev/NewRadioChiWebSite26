<?php

namespace App\Filament\Resources\MusicTracks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MusicTracksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('platform')
                    ->label('Plataforma')
                    ->badge()
                    ->sortable(),
                TextColumn::make('genre')
                    ->label('Genero')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('position')
                    ->label('Posicion')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('translations_count')
                    ->label('Traducciones')
                    ->counts('translations'),
                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
                IconColumn::make('is_published')
                    ->label('Publicado')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Publicado en')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('position')
            ->filters([
                SelectFilter::make('platform')
                    ->options([
                        'soundcloud' => 'SoundCloud',
                        'spotify' => 'Spotify',
                        'youtube' => 'YouTube',
                        'apple_music' => 'Apple Music',
                        'custom' => 'Custom',
                    ]),
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
