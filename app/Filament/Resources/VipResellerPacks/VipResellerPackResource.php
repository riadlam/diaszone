<?php

namespace App\Filament\Resources\VipResellerPacks;

use App\Filament\Resources\VipResellerPacks\Pages\CreateVipResellerPack;
use App\Filament\Resources\VipResellerPacks\Pages\EditVipResellerPack;
use App\Filament\Resources\VipResellerPacks\Pages\ListVipResellerPacks;
use App\Filament\Resources\VipResellerPacks\Schemas\VipResellerPackForm;
use App\Filament\Resources\VipResellerPacks\Tables\VipResellerPacksTable;
use App\Models\VipResellerPack;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VipResellerPackResource extends Resource
{
    protected static ?string $model = VipResellerPack::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'VIP Reseller';

    protected static ?string $navigationLabel = 'VIP Packs';

    protected static ?string $modelLabel = 'VIP pack';

    protected static ?string $pluralModelLabel = 'VIP packs';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'vipreseller-packs';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return VipResellerPackForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VipResellerPacksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVipResellerPacks::route('/'),
            'create' => CreateVipResellerPack::route('/create'),
            'edit' => EditVipResellerPack::route('/{record}/edit'),
        ];
    }
}
