<?php

namespace App\Filament\Resources\RedirectRules;

use App\Filament\Resources\RedirectRules\Pages\CreateRedirectRule;
use App\Filament\Resources\RedirectRules\Pages\EditRedirectRule;
use App\Filament\Resources\RedirectRules\Pages\ListRedirectRules;
use App\Filament\Resources\RedirectRules\Schemas\RedirectRuleForm;
use App\Filament\Resources\RedirectRules\Tables\RedirectRulesTable;
use App\Models\RedirectRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RedirectRuleResource extends Resource
{
    protected static ?string $model = RedirectRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Redireccion';

    protected static ?string $pluralModelLabel = 'Redirecciones';

    protected static ?string $recordTitleAttribute = 'source_path';

    public static function form(Schema $schema): Schema
    {
        return RedirectRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RedirectRulesTable::configure($table);
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
            'index' => ListRedirectRules::route('/'),
            'create' => CreateRedirectRule::route('/create'),
            'edit' => EditRedirectRule::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }
}
