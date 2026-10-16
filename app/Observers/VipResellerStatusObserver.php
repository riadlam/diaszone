<?php

namespace App\Observers;

use App\Models\VipResellerStatus;
use App\Services\WheelQualificationService;
use Illuminate\Support\Facades\Log;

class VipResellerStatusObserver
{
    /**
     * Credit a wheel spin when a VIP Reseller Mobile Legends delivery succeeds.
     * Idempotent per transaction via WheelQualificationService.
     */
    public function saved(VipResellerStatus $status): void
    {
        try {
            app(WheelQualificationService::class)->creditFromVipResellerStatus($status);
        } catch (\Throwable $e) {
            Log::warning('VipResellerStatusObserver: wheel spin credit failed', [
                'vipreseller_status_id' => $status->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
