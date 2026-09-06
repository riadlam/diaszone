<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\VipResellerPack;
use App\Models\VipResellerStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VipResellerFulfillmentService
{
    public function __construct(
        private VipResellerService $vip
    ) {}

    /**
     * Parse VIP success note into profile + login link.
     * Example: "t.nakahara - Sukses Terkirim | https://www.netflix.com/account?nftoken=..."
     *
     * @return array{profile: ?string, link: ?string, raw: ?string}
     */
    public static function parseDeliveryNote(?string $note): array
    {
        $raw = trim((string) $note);
        if ($raw === '') {
            return ['profile' => null, 'link' => null, 'raw' => null];
        }

        $profile = null;
        $link = null;

        if (str_contains($raw, ' | ')) {
            [$left, $right] = explode(' | ', $raw, 2);
            $profile = trim((string) preg_replace('/\s*-\s*Sukses Terkirim.*$/iu', '', $left));
            $right = trim($right);
            if (preg_match('#https?://\S+#', $right, $m)) {
                $link = rtrim($m[0], ".,;)]}\"'");
            }
        } elseif (preg_match('#https?://\S+#', $raw, $m)) {
            $link = rtrim($m[0], ".,;)]}\"'");
        }

        if ($profile === '') {
            $profile = null;
        }

        return [
            'profile' => $profile,
            'link' => $link,
            'raw' => $raw,
        ];
    }

    public static function mapProviderStatus(?string $status): string
    {
        return match (strtolower(trim((string) $status))) {
            'waiting', 'processing', 'pending' => 'waiting',
            'success', 'completed', 'paid', 'sukses' => 'success',
            'error', 'failed', 'canceled', 'cancelled' => 'error',
            default => 'waiting',
        };
    }

    /**
     * Place VIP orders for all VIP line items on a paid order. Leaves order as sending.
     *
     * @return array{success: bool, message: string}
     */
    public function fulfillPaidOrder(Order $order): array
    {
        return DB::transaction(function () use ($order) {
            $orderLocked = Order::query()->where('id', $order->id)->lockForUpdate()->first();
            if (! $orderLocked) {
                return ['success' => false, 'message' => 'Failed to lock order'];
            }

            $orderLocked->load(['orderItems.vipResellerPack', 'vipResellerPack']);

            $email = trim((string) ($orderLocked->customer_email ?? ''));
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Log::error('VIP fulfill: missing customer_email', ['order_id' => $orderLocked->id]);

                return ['success' => false, 'message' => 'Customer email is required for this product'];
            }

            $items = $orderLocked->orderItems->filter(fn (OrderItem $item) => $item->vipreseller_pack_id);
            if ($items->isEmpty() && $orderLocked->vipreseller_pack_id) {
                // Legacy single-pack order without items
                $pack = $orderLocked->vipResellerPack;
                if ($pack) {
                    $ok = $this->placeForItem($orderLocked, null, $pack, $email, 1);
                    if (! $ok['success']) {
                        return $ok;
                    }
                }
            }

            foreach ($items as $orderItem) {
                $pack = $orderItem->vipResellerPack;
                if (! $pack || ! $pack->is_active) {
                    return ['success' => false, 'message' => 'VIP pack not found or inactive'];
                }

                $qty = max(1, (int) ($orderItem->quantity ?? 1));
                $existing = VipResellerStatus::query()
                    ->where('order_item_id', $orderItem->id)
                    ->count();

                $needed = max(0, $qty - $existing);
                for ($i = 0; $i < $needed; $i++) {
                    $ok = $this->placeForItem($orderLocked, $orderItem, $pack, $email, 1);
                    if (! $ok['success']) {
                        return $ok;
                    }
                }
            }

            if ($orderLocked->status !== 'sending') {
                $orderLocked->status = 'sending';
                $orderLocked->save();
            }

            return [
                'success' => true,
                'message' => 'VIP order submitted; waiting for delivery',
                'status' => 'waiting',
            ];
        });
    }

    /**
     * @return array{success: bool, message: string}
     */
    private function placeForItem(
        Order $order,
        ?OrderItem $orderItem,
        VipResellerPack $pack,
        string $email,
        int $quantity
    ): array {
        for ($n = 0; $n < $quantity; $n++) {
            $result = $this->vip->placeServiceOrder($pack->code, $email, null);
            if (! ($result['result'] ?? false)) {
                Log::error('VIP placeServiceOrder failed', [
                    'order_id' => $order->id,
                    'pack_code' => $pack->code,
                    'message' => $result['message'] ?? null,
                ]);

                return [
                    'success' => false,
                    'message' => (string) ($result['message'] ?? 'Failed to place VIP order'),
                ];
            }

            $data = is_array($result['data'] ?? null) ? $result['data'] : [];
            $trxid = (string) ($data['trxid'] ?? '');
            if ($trxid === '') {
                return ['success' => false, 'message' => 'VIP order missing trxid'];
            }

            $mapped = self::mapProviderStatus($data['status'] ?? 'waiting');
            $note = (string) ($data['note'] ?? $result['message'] ?? '');

            VipResellerStatus::query()->updateOrCreate(
                ['trxid' => $trxid],
                [
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem?->id,
                    'buyer_sku_code' => $pack->code,
                    'customer_no' => $email,
                    'data' => $email,
                    'zone' => (string) ($data['zone'] ?? ''),
                    'status' => $mapped,
                    'note' => $note,
                    'message' => $note,
                    'price' => isset($data['price']) ? (float) $data['price'] : null,
                    'sn' => null,
                    'event' => 'create',
                    'additional_data' => [
                        'service' => $data['service'] ?? $pack->name,
                        'vipreseller_pack_id' => $pack->id,
                        'provider' => 'vipreseller',
                    ],
                ]
            );

            if ($mapped === 'success') {
                $this->applyDeliveryToStatus($trxid, $note, $mapped);
            }
        }

        return ['success' => true, 'message' => 'OK'];
    }

    public function applyStatusPayload(string $trxid, array $row): void
    {
        $mapped = self::mapProviderStatus($row['status'] ?? null);
        $note = (string) ($row['note'] ?? $row['message'] ?? '');
        $this->applyDeliveryToStatus($trxid, $note, $mapped, $row);
    }

    public function applyDeliveryToStatus(
        string $trxid,
        string $note,
        string $mappedStatus,
        array $extra = []
    ): void {
        $status = VipResellerStatus::query()->where('trxid', $trxid)->first();
        if (! $status) {
            $status = new VipResellerStatus(['trxid' => $trxid]);
        }

        $parsed = self::parseDeliveryNote($note);
        $additional = $status->additional_data ?? [];
        $additional['provider'] = 'vipreseller';
        if ($parsed['profile']) {
            $additional['delivery_profile'] = $parsed['profile'];
        }
        if ($parsed['link']) {
            $additional['delivery_link'] = $parsed['link'];
        }
        if (! empty($extra['service'])) {
            $additional['service'] = $extra['service'];
        }

        $status->status = $mappedStatus;
        $status->note = $note;
        $status->message = $note;
        if (isset($extra['price'])) {
            $status->price = (float) $extra['price'];
        }
        if (isset($extra['data'])) {
            $status->customer_no = (string) $extra['data'];
            $status->data = (string) $extra['data'];
        }
        // Store login URL in sn for easy admin/API access (Digiflazz pattern).
        if ($mappedStatus === 'success' && $parsed['link']) {
            $status->sn = $parsed['link'];
        }
        $status->additional_data = $additional;
        $status->save();

        $order = $status->order;
        if (! $order && $status->customer_no) {
            $order = Order::query()
                ->where('customer_email', $status->customer_no)
                ->whereIn('status', ['sending', 'completed'])
                ->whereNotNull('vipreseller_pack_id')
                ->latest()
                ->first();
            if ($order) {
                $status->order_id = $order->id;
                $status->save();
            }
        }

        if (! $order) {
            return;
        }

        if ($mappedStatus === 'waiting' && $order->status !== 'sending' && $order->status !== 'completed') {
            $order->status = 'sending';
            $order->save();
        }

        if ($mappedStatus === 'success' && $order->status !== 'completed') {
            if ($this->allVipItemsDelivered($order)) {
                $order->status = 'completed';
                $order->save();

                if ($order->tlg_message_id) {
                    try {
                        $order->load(['orderItems.vipResellerPack', 'user']);
                        $updatedMessage = TelegramService::formatOrderMessage($order);
                        $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '✅ <b>Order Confirmed & Completed</b>', $updatedMessage);
                        TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                    } catch (\Throwable $e) {
                        Log::warning('VIP fulfill: telegram update failed', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        if ($mappedStatus === 'error') {
            $order->notes = trim(($order->notes ? $order->notes."\n" : '').'VIP error: '.$note);
            if ($order->status === 'completed') {
                $order->status = 'sending';
            }
            $order->save();
        }
    }

    public function allVipItemsDelivered(Order $order): bool
    {
        $order->loadMissing('orderItems');
        $items = $order->orderItems->filter(fn (OrderItem $i) => $i->vipreseller_pack_id);
        if ($items->isEmpty()) {
            // Single-pack VIP order
            $success = VipResellerStatus::query()
                ->where('order_id', $order->id)
                ->where('status', 'success')
                ->count();

            return $success >= 1;
        }

        foreach ($items as $item) {
            $need = max(1, (int) ($item->quantity ?? 1));
            $got = VipResellerStatus::query()
                ->where('order_item_id', $item->id)
                ->where('status', 'success')
                ->count();
            if ($got < $need) {
                return false;
            }
        }

        return true;
    }

    /**
     * Poll pending VIP statuses from the provider API.
     */
    public function pollPendingStatuses(int $limit = 40): int
    {
        $rows = VipResellerStatus::query()
            ->whereIn('status', ['waiting', 'processing'])
            ->whereNotNull('trxid')
            ->whereHas('order', fn ($q) => $q->where('status', 'sending'))
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $updated = 0;
        foreach ($rows as $row) {
            $resp = $this->vip->checkOrderStatus((string) $row->trxid);
            if (! ($resp['result'] ?? false)) {
                continue;
            }

            $data = $resp['data'] ?? null;
            // Status API returns a list
            if (is_array($data) && array_is_list($data)) {
                foreach ($data as $entry) {
                    if (! is_array($entry)) {
                        continue;
                    }
                    $trx = (string) ($entry['trxid'] ?? $row->trxid);
                    $this->applyStatusPayload($trx, $entry);
                    $updated++;
                }
            } elseif (is_array($data)) {
                $this->applyStatusPayload((string) ($data['trxid'] ?? $row->trxid), $data);
                $updated++;
            }
        }

        return $updated;
    }
}
