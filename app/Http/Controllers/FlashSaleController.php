<?php

namespace App\Http\Controllers;

use App\Models\FlashSaleOffer;
use App\Services\FlashSaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class FlashSaleController extends Controller
{
    public function __construct(
        private readonly FlashSaleService $flashSales
    ) {}

    public function checkout(Request $request, FlashSaleOffer $offer): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => __('flash_sale.login_required'),
                'require_login' => true,
            ], 401);
        }

        $key = 'flash_sale_checkout_'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'success' => false,
                'message' => __('flash_sale.too_many_attempts'),
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'user_id' => 'nullable|string|max:64',
            'zone_id' => 'nullable|string|max:64',
            'player_id' => 'nullable|string|max:64',
            'player_id_ff' => 'nullable|string|max:64',
            'player_id_pubg' => 'nullable|string|max:64',
            'player_id_hok' => 'nullable|string|max:64',
            'user_id_bs' => 'nullable|string|max:64',
            'server_bs' => 'nullable|string|max:64',
            'save_id' => 'nullable|string|max:64',
            'server' => 'nullable|string|max:64',
        ]);

        try {
            $order = $this->flashSales->createCheckoutOrder($offer, Auth::user(), $data);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: __('flash_sale.not_available'),
                'errors' => $e->errors(),
            ], 422);
        }

        $encryptedId = Crypt::encryptString((string) $order->id);

        return response()->json([
            'success' => true,
            'encrypted_order_id' => $encryptedId,
            'redirect_url' => route('select-payment', [
                'order_id' => $encryptedId,
                'flash' => 1,
            ]),
        ]);
    }

    /**
     * Attach Baridimob / Crypto to an existing flash order and return the payment URL.
     */
    public function preparePayment(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => __('flash_sale.login_required'),
                'require_login' => true,
            ], 401);
        }

        $data = $request->validate([
            'encrypted_order_id' => 'required|string',
            'payment_method' => 'required|string|in:bmccp,cryptocurrency,baridimob',
        ]);

        try {
            $orderId = Crypt::decryptString($data['encrypted_order_id']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Invalid order.'], 400);
        }

        $order = \App\Models\Order::with('flashSaleOffer')->find($orderId);
        if (! $order || (int) $order->user_id !== (int) Auth::id() || ! $order->isFlashSale()) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if (! in_array($order->status, ['pending', 'pending_bmccp', 'pending_cryptopay'], true)) {
            return response()->json(['success' => false, 'message' => 'Order cannot be paid.'], 422);
        }

        $method = $data['payment_method'] === 'baridimob' ? 'bmccp' : $data['payment_method'];
        $status = $method === 'bmccp' ? 'pending_bmccp' : 'pending_cryptopay';

        $order->payment_method = $method;
        $order->status = $status;
        $order->save();

        $encryptedId = Crypt::encryptString((string) $order->id);
        $redirect = $method === 'bmccp'
            ? route('baridimob-form', ['encrypted_order_id' => $encryptedId])
            : url('/select/crypto/'.rawurlencode($encryptedId));

        return response()->json([
            'success' => true,
            'encrypted_order_id' => $encryptedId,
            'redirect_url' => $redirect,
        ]);
    }
}
