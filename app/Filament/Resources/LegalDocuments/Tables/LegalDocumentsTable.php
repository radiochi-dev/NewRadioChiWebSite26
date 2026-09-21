<?php

namespace App\Filament\Resources\LegalDocuments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LegalDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('document_type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),
                TextColumn::make('version')
                    ->label('Version')
                    ->toggleable(),
                TextColumn::make('position')
                    ->label('Posicion')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('translations_count')
                    ->label('Traducciones')
                    ->counts('translations'),
                IconColumn::make('is_published')
                    ->label('Publicado')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('position')
            ->filters([
                SelectFilter::make('document_type')
                    ->options([
                        'terms' => 'Terminos',
                        'privacy' => 'Privacidad',
                        'cookies' => 'Cookies',
                        'custom' => 'Custom',
                    ]),
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
