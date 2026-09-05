<?php

namespace App\Filament\Resources\VipResellerCategories;

use App\Filament\Resources\VipResellerCategories\Pages\CreateVipResellerCategory;
use App\Filament\Resources\VipResellerCategories\Pages\EditVipResellerCategory;
use App\Filament\Resources\VipResellerCategories\Pages\ListVipResellerCategories;
use App\Filament\Resources\VipResellerCategories\RelationManagers\PacksRelationManager;
use App\Filament\Resources\VipResellerCategories\Schemas\VipResellerCategoryForm;
use App\Filament\Resources\VipResellerCategories\Tables\VipResellerCategoriesTable;
use App\Models\VipResellerCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VipResellerCategoryResource extends Resource
{
    protected static ?string $model = VipResellerCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'VIP Reseller';

    protected static ?string $navigationLabel = 'VIP Categories';

    protected static ?string $modelLabel = 'VIP category';

    protected static ?string $pluralModelLabel = 'VIP categories';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'vipreseller-categories';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return VipResellerCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VipResellerCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PacksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVipResellerCategories::route('/'),
            'create' => CreateVipResellerCategory::route('/create'),
            'edit' => EditVipResellerCategory::route('/{record}/edit'),
        ];
    }
}
