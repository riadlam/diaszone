<?php

namespace App\Filament\Resources\VipResellerPacks\Pages;

use App\Filament\Resources\VipResellerCategories\VipResellerCategoryResource;
use App\Filament\Resources\VipResellerPacks\VipResellerPackResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVipResellerPack extends EditRecord
{
    protected static string $resource = VipResellerPackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openCategory')
                ->label('Category')
                ->icon('heroicon-o-tag')
                ->color('gray')
                ->url(fn (): string => VipResellerCategoryResource::getUrl('edit', [
                    'record' => $this->getRecord()->category_id,
                ]))
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->getRecord()->category_id)),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['code'] = trim((string) ($data['code'] ?? ''));

        return $data;
    }
}
