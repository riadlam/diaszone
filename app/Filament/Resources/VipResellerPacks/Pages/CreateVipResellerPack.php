<?php

namespace App\Filament\Resources\VipResellerPacks\Pages;

use App\Filament\Resources\VipResellerPacks\VipResellerPackResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVipResellerPack extends CreateRecord
{
    protected static string $resource = VipResellerPackResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = trim((string) ($data['code'] ?? ''));

        return $data;
    }
}
