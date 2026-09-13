<?php

namespace App\Services;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\VipResellerStatus;
use Illuminate\Support\Facades\Log;

class MlPackFulfillment
{
    public function __construct(protected VipResellerService $vip) {}

    /**
     * Top up one Mobile Legends unit. VIP when the pack code is set, otherwise Digiflazz.
     *
     * @return array{result: bool, data: mixed, message: string, provider?: string}
     */
    public function place(DiamondPack $pack, Order $order, ?int $orderItemId = null, ?string $refId = null): array
    {
        if (! $pack->usesVipReseller()) {
            $digiflazz = app(DigiflazzService::class);

            if ($refId) {
                return $digiflazz->placeOrderWithRefId($pack, $order, $refId, $orderItemId);
            }

            return $digiflazz->placeOrder($pack, $order);
        }

        return $this->placeVip($pack, $order, $orderItemId, $refId);
    }

    /**
     * @return array{result: bool, data: mixed, message: string, provider: string}
     */
    protected function placeVip(DiamondPack $pack, Order $order, ?int $orderItemId, ?string $refId): array
    {
        $service = trim((string) $pack->vip_reseller_code);
        $result = $this->vip->placeGameOrder(
            $service,
            (string) $order->user_id_ml,
            (string) $order->zone_id_ml
        );

        $apiData = is_array($result['data'] ?? null) ? $result['data'] : [];
        $mapped = 'error';
        if (($result['result'] ?? false) === true) {
            $mapped = match (strtolower((string) ($apiData['status'] ?? 'waiting'))) {
                'success', 'completed', 'paid' => 'success',
                'processing' => 'processing',
                default => 'waiting',
            };
        }

        $rawPrice = $apiData['price'] ?? null;
        $statusData = [
            'order_id' => $order->id,
            'order_item_id' => $orderItemId,
            'diamond_pack_id' => $pack->id,
            'ref_id' => $refId,
            'trxid' => $apiData['trxid'] ?? null,
            'buyer_sku_code' => $service,
            'customer_no' => $order->user_id_ml,
            'status' => $mapped,
            'message' => $apiData['note'] ?? ($result['message'] ?? null),
            'price' => is_numeric($rawPrice) ? (int) $rawPrice : null,
            'event' => ($result['result'] ?? false) === true ? 'create' : null,
            'additional_data' => [
                'provider' => 'vipreseller',
                'service' => $service,
                'zone' => $apiData['zone'] ?? $order->zone_id_ml,
                'data_no' => $apiData['data'] ?? $order->user_id_ml,
                'price' => $rawPrice,
                'full_response' => $result,
            ],
        ];

        if (! empty($statusData['trxid'])) {
            VipResellerStatus::updateOrCreate(['trxid' => $statusData['trxid']], $statusData);
        } else {
            VipResellerStatus::create($statusData);
        }

        Log::info('ML pack routed to VIP Reseller', [
            'order_id' => $order->id,
            'order_item_id' => $orderItemId,
            'service' => $service,
            'trxid' => $statusData['trxid'],
            'status' => $mapped,
        ]);

        $result['provider'] = 'vipreseller';

        return $result;
    }

    /**
     * True when at least one line still has to go through Digiflazz.
     */
    public function orderNeedsDigiflazz(Order $order): bool
    {
        $order->loadMissing('orderItems.diamondPack', 'diamondPack');

        if ($order->orderItems->isNotEmpty()) {
            return $order->orderItems->contains(
                fn ($item) => $item->diamondPack && ! $item->diamondPack->usesVipReseller()
            );
        }

        return ! ($order->diamondPack?->usesVipReseller() ?? false);
    }
}
