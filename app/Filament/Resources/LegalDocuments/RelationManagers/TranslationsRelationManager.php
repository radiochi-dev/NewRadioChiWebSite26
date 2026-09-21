<?php

namespace App\Filament\Resources\LegalDocuments\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Support\BackofficeLocales;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                Section::make('Contenido legal traducido')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('locale')
                                    ->label('Idioma')
                                    ->options(BackofficeLocales::options())
                                    ->required()
                                    ->disabledOn('edit'),
                                TextInput::make('title')
                                    ->label('Titulo')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('cta_label')
                                    ->label('CTA label')
                                    ->maxLength(255),
                            ]),
                        Textarea::make('summary')
                            ->label('Resumen')
                            ->rows(3),
                        Textarea::make('content')
                            ->label('Contenido')
                            ->rows(14)
                            ->required(),
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
