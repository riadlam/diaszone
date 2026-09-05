<?php

namespace App\Filament\Resources\VipResellerCategories\Pages;

use App\Filament\Resources\VipResellerCategories\VipResellerCategoryResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditVipResellerCategory extends EditRecord
{
    protected static string $resource = VipResellerCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewStorefront')
                ->label('View on site')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => route('digital.category', $this->getRecord()->slug))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = Str::slug((string) $data['name']);
        }

        return $data;
    }
}
