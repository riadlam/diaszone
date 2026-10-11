<?php

namespace App\Filament\Resources\Giveaways\Pages;

use App\Filament\Resources\Giveaways\GiveawayResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGiveaway extends EditRecord
{
    protected static string $resource = GiveawayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
