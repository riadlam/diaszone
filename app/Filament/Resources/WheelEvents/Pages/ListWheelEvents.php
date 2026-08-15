<?php

namespace App\Filament\Resources\WheelEvents\Pages;

use App\Filament\Resources\WheelEvents\WheelEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWheelEvents extends ListRecords
{
    protected static string $resource = WheelEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
