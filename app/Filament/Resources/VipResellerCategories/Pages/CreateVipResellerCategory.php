<?php

namespace App\Filament\Resources\VipResellerCategories\Pages;

use App\Filament\Resources\VipResellerCategories\VipResellerCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateVipResellerCategory extends CreateRecord
{
    protected static string $resource = VipResellerCategoryResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = Str::slug((string) $data['name']);
        }

        return $data;
    }
}
