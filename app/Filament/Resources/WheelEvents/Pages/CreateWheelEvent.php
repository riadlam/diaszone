<?php

namespace App\Filament\Resources\WheelEvents\Pages;

use App\Filament\Resources\WheelEvents\WheelEventResource;
use App\Services\WheelQualificationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateWheelEvent extends CreateRecord
{
    protected static string $resource = WheelEventResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['game_type'] = $data['game_type'] ?? WheelQualificationService::GAME_TYPE;
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
