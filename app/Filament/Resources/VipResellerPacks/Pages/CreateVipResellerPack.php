<?php

namespace App\Filament\Resources\VipResellerPacks\Pages;

use App\Filament\Resources\VipResellerPacks\VipResellerPackResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVipResellerPack extends CreateRecord
{
    protected static string $resource = VipResellerPackResource::class;

    public function mount(): void
    {
        parent::mount();

        $categoryId = request()->integer('category_id');
        if ($categoryId > 0) {
            $this->form->fill([
                'category_id' => $categoryId,
                'is_active' => true,
                'discount_percentage' => 0,
                'price_dzd' => 0,
                'provider_status' => 'available',
                'sort_order' => 0,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = trim((string) ($data['code'] ?? ''));

        if (empty($data['category_id']) && request()->filled('category_id')) {
            $data['category_id'] = (int) request()->query('category_id');
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
