<?php

namespace App\Filament\Resources\DownloadableFiles;

use App\Filament\Resources\DownloadableFiles\Pages\CreateDownloadableFile;
use App\Filament\Resources\DownloadableFiles\Pages\EditDownloadableFile;
use App\Filament\Resources\DownloadableFiles\Pages\ListDownloadableFiles;
use App\Filament\Resources\DownloadableFiles\Schemas\DownloadableFileForm;
use App\Filament\Resources\DownloadableFiles\Tables\DownloadableFilesTable;
use App\Models\DownloadableFile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DownloadableFileResource extends Resource
{
    protected static ?string $model = DownloadableFile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Media';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Archivo descargable';

    protected static ?string $pluralModelLabel = 'Archivos descargables';

    protected static ?string $recordTitleAttribute = 'display_name';

    public static function form(Schema $schema): Schema
    {
        return DownloadableFileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DownloadableFilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDownloadableFiles::route('/'),
            'create' => CreateDownloadableFile::route('/create'),
            'edit' => EditDownloadableFile::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'gray';
    }
}
