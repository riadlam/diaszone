<?php

namespace App\Filament\Resources\VipResellerPacks\Pages;

use App\Filament\Resources\VipResellerPacks\VipResellerPackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVipResellerPacks extends ListRecords
{
    protected static string $resource = VipResellerPackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
