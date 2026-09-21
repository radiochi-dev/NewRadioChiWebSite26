<?php

namespace App\Filament\Resources\Pages\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
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
                        Grid::make(2)
                            ->schema([
                                Select::make('locale')
                                    ->label('Idioma')
                                    ->options($this->getLocaleOptions())
                                    ->required()
                                    ->disabledOn('edit'),
                                TextInput::make('title')
                                    ->label('Titulo')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('meta_title')
                                    ->label('Meta title')
                                    ->maxLength(255),
                            ]),
                        Textarea::make('meta_description')
                            ->label('Meta description')
                            ->rows(3),
                        Textarea::make('content')
                            ->label('Content JSON')
                            ->rows(12)
                            ->helperText('Mantiene el JSON editorial actual sin cambiar su estructura.')
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
                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('meta_title')
                    ->label('Meta title')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('locale')
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

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->translations()->count();
    }

    private function getLocaleOptions(): array
    {
        return [
            'es' => 'es',
            'en' => 'en',
            'ca' => 'ca',
            'fr' => 'fr',
            'it' => 'it',
            'de' => 'de',
        ];
    }
}
