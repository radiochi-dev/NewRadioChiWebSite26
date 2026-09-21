<?php

namespace App\Filament\Resources\MediaAssets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('filename')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('disk')
                    ->sortable(),
                TextColumn::make('path')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->path),
                TextColumn::make('mime_type')
                    ->label('MIME type')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('size')
                    ->formatStateUsing(fn (?int $state): string => match (true) {
                        $state === null => '-',
                        $state >= 1024 * 1024 => number_format($state / (1024 * 1024), 2).' MB',
                        $state >= 1024 => number_format($state / 1024, 2).' KB',
                        default => $state.' B',
                    })
                    ->sortable(),
                TextColumn::make('width')
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('height')
                    ->numeric(decimalPlaces: 0)
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
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
