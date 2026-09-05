<?php

namespace App\Filament\Resources\VipResellerCategories\Pages;

use App\Filament\Resources\VipResellerCategories\VipResellerCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListVipResellerCategories extends ListRecords
{
    protected static string $resource = VipResellerCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('New VIP category')
                ->modalWidth('3xl')
                ->mutateFormDataUsing(function (array $data): array {
                    if (empty($data['slug']) && ! empty($data['name'])) {
                        $data['slug'] = Str::slug((string) $data['name']);
                    }

                    return $data;
                }),
        ];
    }
}
