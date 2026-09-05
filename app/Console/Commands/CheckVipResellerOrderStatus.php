<?php

namespace App\Console\Commands;

use App\Services\VipResellerFulfillmentService;
use Illuminate\Console\Command;

class CheckVipResellerOrderStatus extends Command
{
    protected $signature = 'vipreseller:check-order-status {--limit=40}';

    protected $description = 'Poll VIP Reseller for pending (waiting) digital orders and complete deliveries';

    public function handle(VipResellerFulfillmentService $fulfillment): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $updated = $fulfillment->pollPendingStatuses($limit);
        $this->info("Updated {$updated} VIP status row(s).");

        return self::SUCCESS;
    }
}
