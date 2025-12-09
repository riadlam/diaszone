<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SellerGamePrice;
use App\Services\ChargilyPayV2Service;
use App\Services\VipResellerService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class SellerStorefrontController extends Controller
{
    public function __construct()
    {
        // Services resolved on demand to prevent unnecessary initialization
    }

    /**
     * Show seller's store home page
     */
    public function home(string $username)
    {
        $seller = Seller::where('username', $username)
            ->where('status', 'active')
            ->firstOrFail();

        // If seller has disabled website, return 404
        if (!$seller->website_enabled) {
            abort(404);
        }

        // Get available games for this seller
        $gameTypes = DiamondPack::where('is_active', true)
            ->select('game_type')
            ->distinct()
            ->pluck('game_type');

        // Filter by seller's allowed games
        if (!empty($seller->allowed_games)) {
            $gameTypes = $gameTypes->intersect($seller->allowed_games);
        }

        $games = $gameTypes->map(function ($type) {
            return [
                'type' => $type,
                'name' => $this->getGameName($type),
                'icon' => $this->getGameIcon($type),
            ];
        });

        return view('seller.storefront.home', compact('seller', 'games'));
    }

    /**
     * Show game page with packs
     */
    public function gamePage(string $username, string $gameType)
    {
        $seller = Seller::where('username', $username)
            ->where('status', 'active')
            ->firstOrFail();

        if (!$seller->website_enabled) {
            abort(404);
        }

        // Check if seller can sell this game
        if (!$seller->canSellGame($gameType)) {
            abort(404);
        }

        // Get packs for this game (paginated)
        $packs = DiamondPack::where('game_type', $gameType)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('diamonds')
            ->paginate(24); // show 24 packs per page

        // Get seller's custom prices for the current page of packs
        $sellerPrices = $seller->gamePrices()
            ->whereIn('diamond_pack_id', $packs->pluck('id')->toArray())
            ->where('is_active', true)
            ->get()
            ->keyBy('diamond_pack_id');

        // Map packs with seller prices
        // Map packs for the current page
        $packsWithPrices = $packs->getCollection()->map(function ($pack) use ($sellerPrices) {
            $customPrice = $sellerPrices->get($pack->id);
            
            // Skip packs that seller has disabled
            if ($customPrice && !$customPrice->is_active) {
                return null;
            }

            return [
                'id' => $pack->id,
                'name' => $pack->name,
                'code' => $pack->code,
                'diamonds' => $pack->diamonds,
                'bonus_diamonds' => $pack->bonus_diamonds,
                'total_diamonds' => $pack->total_diamonds,
                'price_dzd' => $customPrice ? $customPrice->custom_price_dzd : $pack->price_dzd,
                'base_price_dzd' => $pack->base_price_dzd ?? $pack->price_dzd,
                'price_usd' => $customPrice ? $customPrice->custom_price_usd : $pack->price_usd,
            ];
        })->filter();

        $gameName = $this->getGameName($gameType);

        // Pass paginator to view so we can render navigation
        return view('seller.storefront.game', compact('seller', 'gameType', 'gameName', 'packsWithPrices', 'packs'));
    }

    /**
     * Get packs API for JavaScript
     */
    public function getPacksApi(string $username, string $gameType)
    {
        $seller = Seller::where('username', $username)
            ->where('status', 'active')
            ->firstOrFail();

        if (!$seller->website_enabled) {
            abort(404);
        }

        if (!$seller->canSellGame($gameType)) {
            return response()->json(['error' => 'Game not available'], 404);
        }

        $packs = DiamondPack::where('game_type', $gameType)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('diamonds')
            ->get();

        $sellerPrices = $seller->gamePrices()
            ->whereIn('diamond_pack_id', $packs->pluck('id'))
            ->where('is_active', true)
            ->get()
            ->keyBy('diamond_pack_id');

        $packsData = $packs->map(function ($pack) use ($sellerPrices) {
            $customPrice = $sellerPrices->get($pack->id);
            
            if ($customPrice && !$customPrice->is_active) {
                return null;
            }

            return [
                'id' => $pack->id,
                'name' => $pack->name,
                'diamonds' => $pack->diamonds,
                'bonus_diamonds' => $pack->bonus_diamonds,
                'total_diamonds' => $pack->total_diamonds,
                'price_dzd' => (float) ($customPrice ? $customPrice->custom_price_dzd : $pack->price_dzd),
                'base_price_dzd' => (float) ($pack->base_price_dzd ?? $pack->price_dzd),
                'price_usd' => (float) ($customPrice ? $customPrice->custom_price_usd : $pack->price_usd),
            ];
        })->filter()->values();

        return response()->json($packsData);
    }

    /**
     * Show payment method selection page
     */
    public function showPaymentMethod(Request $request, string $username)
    {
        $seller = Seller::where('username', $username)
            ->where('status', 'active')
            ->firstOrFail();

        // Validate input from form
        $validated = $request->validate([
            'pack_id' => 'required|exists:diamond_packs,id',
            'game_type' => 'required|string',
            'player_id' => 'required|string',
            'zone_id' => 'nullable|string',
        ]);

        $pack = DiamondPack::findOrFail($validated['pack_id']);

        // Check if seller can sell this game
        if (!$seller->canSellGame($pack->game_type)) {
            abort(404);
        }

        // Get seller's price
        $customPrice = $seller->getCustomPrice($pack->id);
        $sellingPrice = $customPrice ? $customPrice->custom_price_dzd : $pack->price_dzd;

        $gameName = $this->getGameName($validated['game_type']);
        $currencyLabel = match($validated['game_type']) {
            'pubgmobile' => 'UC',
            'honorofkings' => 'Tokens',
            'bloodstrike' => 'Golds',
            default => 'Diamonds'
        };

        $pack_data = [
            'id' => $pack->id,
            'name' => $pack->name,
            'diamonds' => $pack->diamonds,
            'bonus_diamonds' => $pack->bonus_diamonds,
            'price_dzd' => $sellingPrice,
        ];

        $playerData = [
            'player_id' => $validated['player_id'],
            'zone_id' => $validated['zone_id'] ?? null,
        ];

        return view('seller.storefront.payment-method', [
            'seller' => $seller,
            'gameType' => $validated['game_type'],
            'gameName' => $gameName,
            'pack' => $pack_data,
            'currencyLabel' => $currencyLabel,
            'playerData' => $playerData,
        ]);
    }

    /**
     * Process payment method selection and create order
     */
    public function processPayment(Request $request, string $username)
    {
        $seller = Seller::where('username', $username)
            ->where('status', 'active')
            ->firstOrFail();

        Log::info('Processing payment request', [
            'username' => $username,
            'params' => $request->all(),
            'has_file' => $request->hasFile('receipt')
        ]);

        $validated = $request->validate([
              'pack_id' => 'required|exists:diamond_packs,id',
              'game_type' => 'required|string',
              'player_id' => 'required|string',
              'zone_id' => 'nullable|string',
              'payment_method' => 'required|in:baridimob,flexy',
              // receipt should be required when using flexy
              'receipt' => 'required_if:payment_method,flexy|file|mimes:png,jpg,jpeg,pdf|max:10240',
              'description' => 'nullable|string|max:500',
        ]);

        $pack = DiamondPack::findOrFail($validated['pack_id']);

        // Check if seller can sell this game
        if (!$seller->canSellGame($pack->game_type)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'This game is not available'], 400);
            }
            return back()->withErrors(['error' => 'This game is not available']);
        }

        // Get seller's price
        $customPrice = $seller->getCustomPrice($pack->id);
        $sellingPrice = $customPrice ? $customPrice->custom_price_dzd : $pack->price_dzd;
        // Seller internal cost should use pack.base_price_dzd when available
        $baseCost = $pack->base_price_dzd ?? $pack->price_dzd;
        $profit = $sellingPrice - $baseCost;

        // Ensure seller allows Flexy when chosen
        if (($validated['payment_method'] ?? '') === 'flexy' && !$seller->flexy_enabled) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Flexy payment is disabled for this seller'], 400);
            }
            return back()->withErrors(['error' => 'Flexy payment is disabled for this seller']);
        }

        // Check seller wallet balance — skip for Flexy transfers because buyer sends receipt
        if (($validated['payment_method'] ?? '') !== 'flexy') {
            if ($seller->wallet_balance < $baseCost) {
                Log::info('Seller out of stock for non-Flexy payment', ['seller_id' => $seller->id, 'wallet_balance' => $seller->wallet_balance, 'base_cost' => $baseCost]);
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'This seller is currently out of stock'], 400);
                }
                return back()->withErrors(['error' => 'This seller is currently out of stock']);
            }
        } else {
            Log::info('Flexy payment – skipping seller wallet balance check', ['seller_id' => $seller->id, 'wallet_balance' => $seller->wallet_balance, 'base_cost' => $baseCost]);
        }

        try {
            DB::beginTransaction();

            // Handle Flexy payment
            if ($validated['payment_method'] === 'flexy') {
                Log::info('Processing Flexy payment', ['has_file' => $request->hasFile('receipt')]);
                if (!$request->hasFile('receipt')) {
                    throw new \Exception('Receipt file is required for Flexy payment');
                }

                // Store receipt file
                $receiptPath = $request->file('receipt')->store('flexy-receipts', 'public');

                // We intentionally do NOT call Chargily/VipReseller here — Flexy is manual transfer waiting verification
                Log::info('Creating Flexy order without triggering Chargily or VipReseller', ['order_for_pack' => $pack->id, 'seller_id' => $seller->id]);

                // Create order with flexy payment
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'seller_id' => $seller->id,
                    'diamond_pack_id' => $pack->id,
                    'status' => 'pending_flexy_verification',
                    // Use pack's game_type (safer) for assigning game-specific identifiers
                    'user_id_ml' => $pack->game_type === 'mobilelegends' ? $validated['player_id'] : null,
                    'zone_id_ml' => $pack->game_type === 'mobilelegends' ? ($validated['zone_id'] ?? null) : null,
                    'player_id_ff' => $pack->game_type === 'freefire' ? $validated['player_id'] : null,
                    'player_id_pubg' => $pack->game_type === 'pubgmobile' ? $validated['player_id'] : null,
                    'player_id_hok' => $pack->game_type === 'honorofkings' ? $validated['player_id'] : null,
                    'user_id_bs' => $pack->game_type === 'bloodstrike' ? $validated['player_id'] : null,
                    'server_bs' => $pack->game_type === 'bloodstrike' ? ($validated['zone_id'] ?? null) : null,
                    'wallet_deducted' => false,
                    'seller_cost' => $baseCost,
                    'seller_profit' => $profit,
                    'is_direct_topup' => false,
                    'original_price' => $sellingPrice,
                    'final_price' => $sellingPrice,
                    'payment_method' => 'flexy',
                    'flexy_receipt' => $receiptPath,
                    'flexy_description' => $validated['description'] ?? null,
                ]);

                DB::commit();

                if ($request->expectsJson()) {
                    $encryptedOrderId = Crypt::encryptString($order->id);
                    $successUrl = route('seller.payment.success', ['encrypted_order_id' => $encryptedOrderId]);
                    return response()->json([
                        'success' => true,
                        'message' => 'Transfer recorded. Your order will be processed once payment is verified.',
                        'redirect_url' => $successUrl
                    ]);
                }

                $encryptedOrderId = Crypt::encryptString($order->id);
                Log::info('Flexy order created — redirecting to success', ['order_id' => $order->id, 'encrypted' => $encryptedOrderId]);
                return redirect(route('seller.payment.success', ['encrypted_order_id' => $encryptedOrderId]));
            }

            // Handle Baridimob payment (original flow)
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'seller_id' => $seller->id,
                'diamond_pack_id' => $pack->id,
                'status' => 'pending',
                // Use pack's game_type (safer) for assigning game-specific identifiers
                'user_id_ml' => $pack->game_type === 'mobilelegends' ? $validated['player_id'] : null,
                'zone_id_ml' => $pack->game_type === 'mobilelegends' ? ($validated['zone_id'] ?? null) : null,
                'player_id_ff' => $pack->game_type === 'freefire' ? $validated['player_id'] : null,
                'player_id_pubg' => $pack->game_type === 'pubgmobile' ? $validated['player_id'] : null,
                'player_id_hok' => $pack->game_type === 'honorofkings' ? $validated['player_id'] : null,
                'user_id_bs' => $pack->game_type === 'bloodstrike' ? $validated['player_id'] : null,
                'server_bs' => $pack->game_type === 'bloodstrike' ? ($validated['zone_id'] ?? null) : null,
                'wallet_deducted' => false,
                'seller_cost' => $baseCost,
                'seller_profit' => $profit,
                'is_direct_topup' => false,
                'original_price' => $sellingPrice,
                'final_price' => $sellingPrice,
                'payment_method' => 'baridimob',
            ]);

            // Create Chargily payment
            $encryptedOrderId = Crypt::encryptString($order->id);
            $successUrl = route('seller.payment.success', ['encrypted_order_id' => $encryptedOrderId]);
            $failureUrl = route('seller.store.game', ['username' => $seller->username, 'gameType' => $pack->game_type]);

            $chargilyService = app(ChargilyPayV2Service::class);
            // Charge the customer the selling price (seller custom price if set)
            $chargilyAmount = $sellingPrice;

            // Build a structured checkout payload aligned with CheckoutController
            $checkoutData = [
                'amount' => (int) round($chargilyAmount),
                'currency' => 'dzd',
                'payment_method' => 'edahabia',
                'success_url' => $successUrl,
                'failure_url' => $failureUrl,
                'description' => "Order {$order->order_number} - {$pack->name}",
                'locale' => 'en',
            ];

            // Add webhook endpoint when not running locally/testing
            $isLocalhost = in_array(config('app.env'), ['local', 'testing']) || 
                          str_contains(request()->getHost(), 'localhost') ||
                          str_contains(request()->getHost(), '127.0.0.1');

            if (!$isLocalhost) {
                $checkoutData['webhook_endpoint'] = route('baridimob.webhook');
            }

            Log::info('Chargily createCheckout request', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'checkout_payload' => $checkoutData,
            ]);

            $chargilyResponse = $chargilyService->createCheckout($checkoutData);

            Log::info('Chargily createCheckout response', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'response' => $chargilyResponse,
            ]);

            if (!$chargilyResponse || !isset($chargilyResponse['checkout_url'])) {
                throw new \Exception('Failed to create payment');
            }

            // Save Chargily status
            $checkoutIdValue = $chargilyResponse['checkout_id'] ?? $chargilyResponse['id'] ?? null;

            if (empty($checkoutIdValue)) {
                Log::warning('Chargily createCheckout returned empty checkout id', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'response' => $chargilyResponse,
                ]);
            }

            $chargilyStatus = \App\Models\ChargilyStatus::create([
                'order_id' => $order->id,
                // Accept either 'checkout_id' or legacy 'id' coming from service
                'checkout_id' => $checkoutIdValue,
                'status' => 'pending',
                // store the actual charged amount
                'amount' => $chargilyAmount,
                'currency' => 'DZD',
                'response_data' => json_encode($chargilyResponse),
            ]);

            $order->update(['chargily_status_id' => $chargilyStatus->id]);

            Log::info('Chargily status saved for order', [
                'order_id' => $order->id,
                'chargily_status_id' => $chargilyStatus->id,
                'checkout_id' => $chargilyStatus->checkout_id,
            ]);

            // Send initial Telegram notification for storefront orders (include seller)
            try {
                $order->load('diamondPack', 'seller');
                $message = \App\Services\TelegramService::formatOrderMessage($order);
                $messageId = \App\Services\TelegramService::sendMessage($message);
                if ($messageId) {
                    $order->tlg_message_id = $messageId;
                    $order->save();
                }
            } catch (\Exception $e) {
                Log::error('Telegram send failed for seller storefront order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            return redirect($chargilyResponse['checkout_url']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Seller payment processing failed', [
                'seller_id' => $seller->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Payment failed. Please try again.'], 400);
            }
            return back()->withErrors(['error' => 'Payment failed. Please try again.']);
        }
    }

    /**
     * Process checkout (OLD - now goes to payment method selection first)
     */
    public function checkout(Request $request, string $username)
    {
        // Redirect to payment method selection
        return $this->showPaymentMethod($request, $username);
    }

    /**
     * Payment success page
     */
    public function paymentSuccess(string $encrypted_order_id)
    {
        try {
            $orderId = Crypt::decryptString($encrypted_order_id);
            $order = Order::with(['seller', 'diamondPack'])->findOrFail($orderId);

            if (!$order->seller) {
                abort(404);
            }

            return view('seller.storefront.payment-success', compact('order'));

        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Process seller order after payment (called from webhook)
     */
    public static function processSellerOrder(Order $order): bool
    {
        $seller = $order->seller;
        if (!$seller) {
            return false;
        }

        $pack = $order->diamondPack;
        $baseCost = $order->seller_cost;

        // Check wallet balance
        if ($seller->wallet_balance < $baseCost) {
            Log::error('Seller insufficient balance for order', [
                'order_id' => $order->id,
                'seller_id' => $seller->id,
                'balance' => $seller->wallet_balance,
                'cost' => $baseCost
            ]);
            return false;
        }

        try {
            DB::beginTransaction();

            // Deduct from seller wallet
            $tx = $seller->deductWallet($baseCost, "Order #{$order->order_number}", $order->id);
            if ($tx) {
                Log::info('Seller wallet deducted for order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'seller_id' => $seller->id,
                    'amount_deducted' => $baseCost,
                    'balance_before' => $tx->balance_before ?? null,
                    'balance_after' => $tx->balance_after ?? null,
                    'transaction_id' => $tx->id ?? null,
                ]);
            } else {
                Log::error('Seller wallet deduction returned no transaction record', [
                    'order_id' => $order->id,
                    'seller_id' => $seller->id,
                    'required' => $baseCost,
                ]);
            }

            $order->update(['wallet_deducted' => true]);

            // Add earnings
            $seller->addEarnings($order->seller_profit, $order->final_price);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process seller order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get game name from type
     */
    protected function getGameName(string $type): string
    {
        $names = [
            'mobilelegends' => 'Mobile Legends',
            'freefire' => 'Free Fire',
            'pubgmobile' => 'PUBG Mobile',
            'honorofkings' => 'Honor of Kings',
            'bloodstrike' => 'Blood Strike',
        ];
        return $names[$type] ?? ucfirst($type);
    }

    /**
     * Get game icon from type
     */
    protected function getGameIcon(string $type): string
    {
        $icons = [
            'mobilelegends' => '/images/games/mlbb.png',
            'freefire' => '/images/games/freefire.png',
            'pubgmobile' => '/images/games/pubg.png',
            'honorofkings' => '/images/games/hok.png',
            'bloodstrike' => '/images/games/bloodstrike.png',
        ];
        return $icons[$type] ?? '/images/games/default.png';
    }
}
