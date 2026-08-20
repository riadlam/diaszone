<?php

namespace App\Filament\Resources\FlashSales;

use App\Filament\Resources\FlashSales\Pages\CreateFlashSaleOffer;
use App\Filament\Resources\FlashSales\Pages\EditFlashSaleOffer;
use App\Filament\Resources\FlashSales\Pages\ListFlashSaleOffers;
use App\Filament\Resources\FlashSales\Schemas\FlashSaleOfferForm;
use App\Filament\Resources\FlashSales\Tables\FlashSaleOffersTable;
use App\Models\FlashSaleOffer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FlashSaleOfferResource extends Resource
{
    protected static ?string $model = FlashSaleOffer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $navigationLabel = 'Flash Sales';

    protected static ?string $modelLabel = 'flash sale';

    protected static ?string $pluralModelLabel = 'flash sales';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'flash-sales';

    public static function form(Schema $schema): Schema
    {
        return FlashSaleOfferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlashSaleOffersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFlashSaleOffers::route('/'),
            'create' => CreateFlashSaleOffer::route('/create'),
            'edit' => EditFlashSaleOffer::route('/{record}/edit'),
        ];
    }
}
