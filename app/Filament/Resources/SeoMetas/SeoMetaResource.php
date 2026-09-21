<?php

namespace App\Filament\Resources\SeoMetas;

use App\Filament\Resources\SeoMetas\Pages\CreateSeoMeta;
use App\Filament\Resources\SeoMetas\Pages\EditSeoMeta;
use App\Filament\Resources\SeoMetas\Pages\ListSeoMetas;
use App\Filament\Resources\SeoMetas\Schemas\SeoMetaForm;
use App\Filament\Resources\SeoMetas\Tables\SeoMetasTable;
use App\Models\SeoMeta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SeoMetaResource extends Resource
{
    protected static ?string $model = SeoMeta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'SEO meta';

    protected static ?string $pluralModelLabel = 'SEO meta';

    protected static ?string $recordTitleAttribute = 'meta_title';

    public static function form(Schema $schema): Schema
    {
        return SeoMetaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeoMetasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeoMetas::route('/'),
            'create' => CreateSeoMeta::route('/create'),
            'edit' => EditSeoMeta::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}
