<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\DiamondPack;
use App\Services\VipResellerService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CouponController extends Controller
{
    protected VipResellerService $vipResellerService;
    protected TelegramService $telegramService;

    public function __construct(VipResellerService $vipResellerService, TelegramService $telegramService)
    {
        $this->vipResellerService = $vipResellerService;
        $this->telegramService = $telegramService;
    }

    /**
     * Validate a coupon code
     */
    public function validate(Request $request)
    {
        // Must be logged in
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => __('coupons.login_required'),
                'error_code' => 'LOGIN_REQUIRED'
            ], 401);
        }

        // Rate limiting - 10 attempts per minute
        $key = 'coupon-validate:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'success' => false,
                'message' => __('coupons.rate_limited'),
                'error_code' => 'RATE_LIMITED'
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $request->validate([
            'code' => 'required|string|max:50',
            'game_code' => 'required|string|in:mlbb,freefire,pubg',
            'package_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::findByCode($request->code);

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => __('coupons.invalid_code'),
                'error_code' => 'INVALID_CODE'
            ], 404);
        }

        // Check if coupon is valid
        if (!$coupon->isValid()) {
            $message = __('coupons.expired');
            if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
                $message = __('coupons.max_uses_reached');
            }
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'COUPON_EXPIRED'
            ], 400);
        }

        // Check if user can use this coupon
        if (!$coupon->canBeUsedByUser(Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => __('coupons.already_used'),
                'error_code' => 'ALREADY_USED'
            ], 400);
        }

        // Check if coupon applies to this package
        if (!$coupon->appliesToPackage($request->game_code, $request->package_id)) {
            return response()->json([
                'success' => false,
                'message' => __('coupons.not_applicable'),
                'error_code' => 'NOT_APPLICABLE'
            ], 400);
        }

        // Check minimum order amount
        if ($coupon->min_order_amount && $request->amount < $coupon->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => __('coupons.min_amount_required', ['amount' => $coupon->min_order_amount]),
                'error_code' => 'MIN_AMOUNT'
            ], 400);
        }

        // Calculate discount
        $discountInfo = $coupon->calculateDiscount($request->amount);

        return response()->json([
            'success' => true,
            'message' => __('coupons.applied'),
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
            ],
            'discount' => $discountInfo,
            'is_free' => $discountInfo['is_free'],
        ]);
    }

    /**
     * Process a 100% discount (free) order
     * This bypasses payment gateway and directly processes the order
     */
    public function processFreeOrder(Request $request)
    {
        // Must be logged in
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => __('coupons.login_required'),
                'error_code' => 'LOGIN_REQUIRED'
            ], 401);
        }

        $request->validate([
            'coupon_code' => 'required|string|max:50',
            'order_id' => 'required|integer',
            'secure_token' => 'required|string',
        ]);

        $user = Auth::user();
        $coupon = Coupon::findByCode($request->coupon_code);
        $order = Order::findOrFail($request->order_id);

        // Security checks
        if (!$coupon) {
            Log::warning('Free order attempt with invalid coupon', [
                'user_id' => $user->id,
                'coupon_code' => $request->coupon_code
            ]);
            return response()->json([
                'success' => false,
                'message' => __('coupons.invalid_code'),
                'error_code' => 'INVALID_CODE'
            ], 400);
        }

        // Verify secure token (like webhook signature verification)
        if (!$coupon->verifySecureToken($request->secure_token, $user->id, $order->id)) {
            Log::warning('Free order attempt with invalid token', [
                'user_id' => $user->id,
                'coupon_id' => $coupon->id,
                'order_id' => $order->id
            ]);
            return response()->json([
                'success' => false,
                'message' => __('coupons.invalid_request'),
                'error_code' => 'INVALID_TOKEN'
            ], 403);
        }

        // Verify coupon is 100% discount
        if (!$coupon->isFullDiscount()) {
            return response()->json([
                'success' => false,
                'message' => __('coupons.not_free_coupon'),
                'error_code' => 'NOT_FREE'
            ], 400);
        }

        // Verify order belongs to user
        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('coupons.invalid_order'),
                'error_code' => 'INVALID_ORDER'
            ], 403);
        }

        // Verify order is pending
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => __('coupons.order_not_pending'),
                'error_code' => 'ORDER_NOT_PENDING'
            ], 400);
        }

        // Check coupon can still be used
        if (!$coupon->canBeUsedByUser($user->id)) {
            return response()->json([
                'success' => false,
                'message' => __('coupons.already_used'),
                'error_code' => 'ALREADY_USED'
            ], 400);
        }

        // Start transaction
        DB::beginTransaction();
        try {
            // Get the diamond pack
            $diamondPack = DiamondPack::findOrFail($order->diamond_pack_id);
            $originalPrice = $diamondPack->price;

            // Calculate discount
            $discountInfo = $coupon->calculateDiscount($originalPrice);

            // Update order with coupon info
            $order->update([
                'coupon_id' => $coupon->id,
                'discount_amount' => $discountInfo['discount_amount'],
                'original_price' => $discountInfo['original_amount'],
                'final_price' => $discountInfo['final_amount'],
                'status' => 'processing',
            ]);

            // Record coupon usage
            CouponUsage::create([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'discount_applied' => $discountInfo['discount_amount'],
                'original_amount' => $discountInfo['original_amount'],
                'final_amount' => $discountInfo['final_amount'],
            ]);

            // Increment coupon usage count
            $coupon->incrementUsage();

            // Process the top-up via VIP Reseller
            $topUpResult = $this->vipResellerService->topUpMlbb(
                $order->player_id,
                $order->server_id,
                $diamondPack->diamonds
            );

            if ($topUpResult['success']) {
                $order->update([
                    'status' => 'completed',
                    'vip_reseller_order_id' => $topUpResult['order_id'] ?? null,
                ]);

                DB::commit();

                // Send Telegram notification for free order (fraud monitoring)
                $this->sendFreeOrderNotification($user, $order, $coupon, $diamondPack);

                return response()->json([
                    'success' => true,
                    'message' => __('coupons.order_completed'),
                    'redirect_url' => route('order.success', ['order' => $order->id]),
                ]);
            } else {
                // Top-up failed
                $order->update(['status' => 'failed']);
                DB::commit();

                Log::error('Free order top-up failed', [
                    'order_id' => $order->id,
                    'error' => $topUpResult['message'] ?? 'Unknown error'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => __('coupons.topup_failed'),
                    'error_code' => 'TOPUP_FAILED'
                ], 500);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Free order processing error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('coupons.processing_error'),
                'error_code' => 'PROCESSING_ERROR'
            ], 500);
        }
    }

    /**
     * Send Telegram notification for free orders (fraud monitoring)
     */
    protected function sendFreeOrderNotification($user, $order, $coupon, $diamondPack)
    {
        try {
            $message = "🎁 *FREE ORDER PROCESSED*\n\n"
                . "👤 User: {$user->name} (ID: {$user->id})\n"
                . "📧 Email: {$user->email}\n"
                . "🎮 Player: {$order->player_id} (Server: {$order->server_id})\n"
                . "💎 Pack: {$diamondPack->diamonds} diamonds\n"
                . "💰 Original Price: {$diamondPack->price} DZD\n"
                . "🎟️ Coupon: {$coupon->code}\n"
                . "📅 Time: " . now()->format('Y-m-d H:i:s');

            $this->telegramService->sendMessage($message);
        } catch (\Exception $e) {
            Log::warning('Failed to send free order Telegram notification', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove applied coupon
     */
    public function remove(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => __('coupons.removed'),
        ]);
    }
}
