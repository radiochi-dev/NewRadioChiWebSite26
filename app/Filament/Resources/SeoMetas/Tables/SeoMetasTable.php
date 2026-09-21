<?php

namespace App\Filament\Resources\SeoMetas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SeoMetasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entity_type')
                    ->label('Entity type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('entity_id')
                    ->label('Entity ID')
                    ->sortable(),
                TextColumn::make('locale')
                    ->label('Idioma')
                    ->badge()
                    ->sortable(),
                TextColumn::make('meta_title')
                    ->label('Meta title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('canonical_url')
                    ->label('Canonical URL')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->canonical_url)
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('locale')
                    ->label('Idioma')
                    ->options([
                        'es' => 'es',
                        'en' => 'en',
                        'ca' => 'ca',
                        'fr' => 'fr',
                        'it' => 'it',
                        'de' => 'de',
                    ]),
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
