<?php

namespace App\Filament\Resources\VipResellerCategories\Pages;

use App\Filament\Resources\VipResellerCategories\VipResellerCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVipResellerCategory extends EditRecord
{
    protected static string $resource = VipResellerCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
