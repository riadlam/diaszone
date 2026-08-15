<?php

namespace App\Services;

use App\Models\DigiflazzStatus;
use App\Models\Order;
use App\Models\WheelEvent;
use App\Models\WheelSpinLedger;
use App\Models\WheelUserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WheelQualificationService
{
    public const GAME_TYPE = 'mobilelegends';

    /**
     * Credit one spin for a successful Digiflazz status when it qualifies.
     * Idempotent via unique source_key.
     */
    public function creditFromDigiflazzStatus(DigiflazzStatus $status): bool
    {
        if (! $this->isSuccessfulStatus($status)) {
            return false;
        }

        $order = $status->order_id
            ? Order::with(['diamondPack', 'orderItems.diamondPack'])->find($status->order_id)
            : null;

        if (! $order || ! $order->user_id) {
            return false;
        }

        if (! $this->orderQualifies($order, $status)) {
            return false;
        }

        $event = $this->activeEventCovering($order->created_at);
        if (! $event) {
            return false;
        }

        $sourceKey = $this->sourceKeyForStatus($event->id, $status);

        return $this->creditSpin(
            userId: (int) $order->user_id,
            event: $event,
            sourceType: 'digiflazz_status',
            sourceKey: $sourceKey,
            digiflazzStatusId: $status->id,
            orderId: $order->id,
            meta: [
                'ref_id' => $status->ref_id,
                'trxid' => $status->trxid,
            ]
        );
    }

    /**
     * Backfill all qualifying Digiflazz successes for an event window.
     */
    public function backfillEvent(WheelEvent $event): int
    {
        $credited = 0;

        DigiflazzStatus::query()
            ->where(function ($q) {
                $q->whereRaw('LOWER(status) = ?', ['sukses'])
                    ->orWhere('rc', '00');
            })
            ->whereHas('order', function ($q) use ($event) {
                $q->whereNotNull('user_id')
                    ->whereNull('flexy_id')
                    ->where(function ($payment) {
                        $payment->whereNull('payment_method')
                            ->orWhereRaw('LOWER(payment_method) <> ?', ['flexy']);
                    })
                    ->where(function ($seller) {
                        $seller->whereNull('seller_id')
                            ->orWhere('is_direct_topup', false);
                    })
                    ->where(function ($coupon) {
                        $coupon->whereNull('payment_method')
                            ->orWhereRaw('LOWER(payment_method) <> ?', ['coupon_free']);
                    })
                    ->where('created_at', '>=', $event->starts_at)
                    ->where('created_at', '<', $event->ends_at);
            })
            ->with(['order.diamondPack', 'order.orderItems.diamondPack', 'diamondPack'])
            ->orderBy('id')
            ->chunkById(200, function ($statuses) use ($event, &$credited) {
                foreach ($statuses as $status) {
                    $order = $status->order;
                    if (! $order || ! $this->isMobileLegendsTopup($order, $status)) {
                        continue;
                    }

                    $sourceKey = $this->sourceKeyForStatus($event->id, $status);
                    if ($this->creditSpin(
                        userId: (int) $order->user_id,
                        event: $event,
                        sourceType: 'digiflazz_status',
                        sourceKey: $sourceKey,
                        digiflazzStatusId: $status->id,
                        orderId: $order->id,
                        meta: [
                            'ref_id' => $status->ref_id,
                            'trxid' => $status->trxid,
                            'backfill' => true,
                        ]
                    )) {
                        $credited++;
                    }
                }
            });

        Log::info('WheelQualificationService: backfill complete', [
            'event_id' => $event->id,
            'credited' => $credited,
        ]);

        return $credited;
    }

    public function creditSpin(
        int $userId,
        WheelEvent $event,
        string $sourceType,
        string $sourceKey,
        ?int $digiflazzStatusId = null,
        ?int $orderId = null,
        ?array $meta = null
    ): bool {
        try {
            return DB::transaction(function () use (
                $userId,
                $event,
                $sourceType,
                $sourceKey,
                $digiflazzStatusId,
                $orderId,
                $meta
            ) {
                if (WheelSpinLedger::where('source_type', $sourceType)
                    ->where('source_key', $sourceKey)
                    ->lockForUpdate()
                    ->exists()) {
                    return false;
                }

                $progress = WheelUserProgress::where('user_id', $userId)
                    ->where('game_type', self::GAME_TYPE)
                    ->lockForUpdate()
                    ->first();

                if (! $progress) {
                    $progress = WheelUserProgress::create([
                        'user_id' => $userId,
                        'game_type' => self::GAME_TYPE,
                        'current_reward_id' => $this->firstRewardId($event),
                        'draws_toward_current' => 0,
                        'total_spins_earned' => 0,
                        'total_spins_used' => 0,
                        'total_rewards_unlocked' => 0,
                        'version' => 0,
                    ]);
                    $progress = WheelUserProgress::where('id', $progress->id)->lockForUpdate()->first();
                } elseif (! $progress->current_reward_id) {
                    $progress->current_reward_id = $this->firstRewardId($event);
                }

                WheelSpinLedger::create([
                    'user_id' => $userId,
                    'game_type' => self::GAME_TYPE,
                    'wheel_event_id' => $event->id,
                    'entry_type' => 'credit',
                    'amount' => 1,
                    'source_type' => $sourceType,
                    'source_key' => $sourceKey,
                    'digiflazz_status_id' => $digiflazzStatusId,
                    'order_id' => $orderId,
                    'meta' => $meta,
                ]);

                $progress->total_spins_earned = $progress->total_spins_earned + 1;
                $progress->version = $progress->version + 1;
                $progress->save();

                return true;
            });
        } catch (\Throwable $e) {
            // Unique constraint race — treat as already credited.
            if (str_contains($e->getMessage(), 'wheel_spin_source_unique')
                || str_contains($e->getMessage(), 'Duplicate')) {
                return false;
            }

            Log::error('WheelQualificationService: credit failed', [
                'user_id' => $userId,
                'source_key' => $sourceKey,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function activeEventCovering($moment): ?WheelEvent
    {
        $at = $moment instanceof \DateTimeInterface
            ? \Carbon\Carbon::instance($moment)
            : \Carbon\Carbon::parse($moment);

        return WheelEvent::query()
            ->forGame(self::GAME_TYPE)
            ->where('is_active', true)
            ->where('starts_at', '<=', $at)
            ->where('ends_at', '>', $at)
            ->orderByDesc('starts_at')
            ->first();
    }

    public function currentActiveEvent(): ?WheelEvent
    {
        return WheelEvent::query()
            ->forGame(self::GAME_TYPE)
            ->currentlyActive()
            ->with(['activeRewards.diamondPack', 'activeRewards.eligiblePacks'])
            ->orderByDesc('starts_at')
            ->first();
    }

    private function orderQualifies(Order $order, DigiflazzStatus $status): bool
    {
        if ($order->flexy_id) {
            return false;
        }

        $paymentMethod = strtolower((string) $order->payment_method);
        if ($paymentMethod === 'flexy' || $paymentMethod === 'coupon_free') {
            return false;
        }

        if ($order->seller_id && $order->is_direct_topup) {
            return false;
        }

        return $this->isMobileLegendsTopup($order, $status);
    }

    private function isMobileLegendsTopup(Order $order, DigiflazzStatus $status): bool
    {
        $pack = $status->diamondPack
            ?? ($status->diamond_pack_id ? $status->diamondPack()->first() : null);

        if (! $pack && $status->order_item_id) {
            $item = $order->orderItems->firstWhere('id', $status->order_item_id);
            $pack = $item?->diamondPack;
        }

        if (! $pack) {
            $pack = $order->diamondPack;
        }

        return $pack && $pack->game_type === self::GAME_TYPE;
    }

    private function isSuccessfulStatus(DigiflazzStatus $status): bool
    {
        return strtolower((string) $status->status) === 'sukses'
            || (string) $status->rc === '00';
    }

    private function sourceKeyForStatus(int $eventId, DigiflazzStatus $status): string
    {
        $identity = $status->trxid ?: ($status->ref_id ?: ('id:'.$status->id));

        return 'event:'.$eventId.':df:'.$identity;
    }

    private function firstRewardId(WheelEvent $event): ?int
    {
        return $event->activeRewards()->orderBy('sort_order')->orderBy('id')->value('id');
    }
}
