<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->label('Grupo')
                    ->badge()
                    ->sortable(),
                TextColumn::make('key')
                    ->label('Clave')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),
                TextColumn::make('translations_count')
                    ->label('Traducciones')
                    ->counts('translations'),
                IconColumn::make('is_translatable')
                    ->label('Translatable')
                    ->boolean(),
                IconColumn::make('is_public')
                    ->label('Publico')
                    ->boolean(),
                TextColumn::make('position')
                    ->label('Posicion')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
            ])
            ->defaultSort('group')
            ->filters([
                SelectFilter::make('group')
                    ->options([
                        'localization' => 'localization',
                        'header' => 'header',
                        'intro' => 'intro',
                        'footer' => 'footer',
                        'legal' => 'legal',
                        'contact' => 'contact',
                        'seo' => 'seo',
                        'legacy' => 'legacy',
                    ]),
                TernaryFilter::make('is_translatable')
                    ->label('Translatable'),
                TernaryFilter::make('is_public')
                    ->label('Publico'),
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
