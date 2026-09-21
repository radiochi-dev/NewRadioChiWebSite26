<?php

namespace App\Filament\Resources\PageBlocks\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Support\BackofficeLocales;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $title = 'Traducciones';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contenido traducido')
                    ->schema([
                        Select::make('locale')
                            ->label('Idioma')
                            ->options(BackofficeLocales::options())
                            ->required()
                            ->disabledOn('edit'),
                        Textarea::make('content')
                            ->label('Content JSON')
                            ->rows(12)
                            ->helperText('Mantiene el JSON editorial del bloque por locale.')
                            ->formatStateUsing(static fn (mixed $state): string => blank($state)
                                ? ''
                                : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                            ->dehydrateStateUsing(static function (?string $state): ?array {
                                if (blank($state)) {
                                    return null;
                                }

                                $decoded = json_decode($state, true);

                                return is_array($decoded) ? $decoded : null;
                            }),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('locale')
            ->columns([
                TextColumn::make('locale')
                    ->label('Idioma')
                    ->badge()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->since()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data, string $model): Model {
                        return $this->getOwnerRecord()
                            ->translations()
                            ->updateOrCreate(
                                ['locale' => $data['locale']],
                                Arr::except($data, ['locale']),
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
