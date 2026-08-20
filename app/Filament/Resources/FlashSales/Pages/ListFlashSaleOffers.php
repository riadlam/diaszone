<?php

namespace App\Filament\Resources\FlashSales\Pages;

use App\Filament\Resources\FlashSales\FlashSaleOfferResource;
use Filament\Resources\Pages\ListRecords;

class ListFlashSaleOffers extends ListRecords
{
    protected static string $resource = FlashSaleOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
