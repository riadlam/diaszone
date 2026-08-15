<?php

namespace App\Observers;

use App\Models\DigiflazzStatus;
use App\Services\WheelQualificationService;
use Illuminate\Support\Facades\Log;

class DigiflazzStatusObserver
{
    /**
     * Credit a wheel spin whenever a delivery is recorded as successful, no
     * matter which flow wrote it (checkout response, webhook, status cron or a
     * manual admin retry). Crediting is idempotent per delivery.
     */
    public function saved(DigiflazzStatus $status): void
    {
        try {
            app(WheelQualificationService::class)->creditFromDigiflazzStatus($status);
        } catch (\Throwable $e) {
            Log::warning('DigiflazzStatusObserver: wheel spin credit failed', [
                'digiflazz_status_id' => $status->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
