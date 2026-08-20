<?php

namespace App\Filament\Resources\FlashSales\Pages;

use App\Filament\Resources\FlashSales\FlashSaleOfferResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFlashSaleOffer extends CreateRecord
{
    protected static string $resource = FlashSaleOfferResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
