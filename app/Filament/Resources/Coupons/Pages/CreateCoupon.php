<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->normalizeScope($data);
        $data['code'] = strtoupper(trim((string) ($data['code'] ?? '')));
        $data['created_by'] = 'admin';
        $data['used_count'] = 0;

        if (Auth::check()) {
            $data['description'] = trim((string) ($data['description'] ?? ''));
            if ($data['description'] === '') {
                $data['description'] = 'Created by '.Auth::user()->email.' via admin';
            }
        }

        return $data;
    }

    protected function beforeCreate(): void
    {
        $this->assertSpecificScopeHasGames();
    }

    protected function assertSpecificScopeHasGames(): void
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
