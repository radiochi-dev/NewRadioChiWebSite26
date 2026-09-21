<?php

namespace App\Filament\Resources\NewsletterCampaigns\Tables;

use App\Actions\Newsletter\QueueNewsletterCampaign;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NewsletterCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'queued' => 'warning',
                        'sent' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('scheduled_at')
                    ->label('Programada')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->label('Enviada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('sent_count')
                    ->label('Enviados')
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'draft',
                        'queued' => 'queued',
                        'sent' => 'sent',
                        'cancelled' => 'cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('queueCampaign')
                    ->label('Encolar')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn ($record): bool => in_array($record->status, ['draft', 'cancelled'], true))
                    ->requiresConfirmation()
                    ->action(fn ($record) => app(QueueNewsletterCampaign::class)->execute($record)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
