<?php

namespace App\Filament\Resources\VipResellerCategories\Pages;

use App\Filament\Resources\VipResellerCategories\VipResellerCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVipResellerCategories extends ListRecords
{
    protected static string $resource = VipResellerCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
