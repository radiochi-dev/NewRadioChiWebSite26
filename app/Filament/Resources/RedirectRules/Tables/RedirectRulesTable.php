<?php

namespace App\Filament\Resources\RedirectRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RedirectRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_path')
                    ->label('Origen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('destination_url')
                    ->label('Destino')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->destination_url),
                TextColumn::make('http_status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('locale')
                    ->label('Locale')
                    ->badge()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
                TextColumn::make('hit_count')
                    ->label('Hits')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('http_status')
                    ->options([
                        301 => '301',
                        302 => '302',
                        307 => '307',
                        308 => '308',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Activa'),
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
