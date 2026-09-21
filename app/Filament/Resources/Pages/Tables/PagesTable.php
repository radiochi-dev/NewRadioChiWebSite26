<?php

namespace App\Filament\Resources\Pages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('template')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('translations_count')
                    ->label('Traducciones')
                    ->counts('translations'),
                IconColumn::make('is_published')
                    ->label('Publicada')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Publicado en')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Publicada'),
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
