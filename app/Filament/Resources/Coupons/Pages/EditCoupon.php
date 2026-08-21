<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Resources\Coupons\CouponResource;
use App\Models\Coupon;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => $this->getRecord()->created_by !== 'wheel_event'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var Coupon $record */
        $record = $this->getRecord();

        if ($record->created_by === 'wheel_event') {
            // Keep wheel reward coupons tied to their minted code/identity.
            $data['code'] = $record->code;
            $data['created_by'] = $record->created_by;
        } else {
            $data['code'] = strtoupper(trim((string) ($data['code'] ?? $record->code)));
        }

        return $this->normalizeScope($data);
    }

    protected function beforeSave(): void
    {
        $data = $this->form->getState();
        if (($data['applies_to'] ?? 'all') !== 'specific') {
            return;
        }

        $games = array_values(array_filter((array) ($data['allowed_games'] ?? [])));
        if ($games === []) {
            throw ValidationException::withMessages([
                'data.allowed_games' => 'Select at least one game when the coupon applies to specific games/packs.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeScope(array $data): array
    {
        if (($data['applies_to'] ?? 'all') === 'all') {
            $data['allowed_games'] = null;
            $data['allowed_packages'] = null;

            return $data;
        }

        $games = array_values(array_filter((array) ($data['allowed_games'] ?? [])));
        $packages = array_values(array_filter(array_map('intval', (array) ($data['allowed_packages'] ?? []))));

        $data['allowed_games'] = $games !== [] ? $games : null;
        $data['allowed_packages'] = $packages !== [] ? $packages : null;

        return $data;
    }
}
