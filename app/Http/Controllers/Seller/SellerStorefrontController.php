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
        // If the seller explicitly disabled the website (legacy is_website or website_enabled flag)
        if ((isset($seller->is_website) && !$seller->is_website) || !$seller->website_enabled) {
            // Show the pending storefront landing page instead of a 404 so sellers can disable their storefront
            return view('seller.storefront.pending', compact('seller'));
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

        if ((isset($seller->is_website) && !$seller->is_website) || !$seller->website_enabled) {
            return view('seller.storefront.pending', compact('seller'));
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

        if ((isset($seller->is_website) && !$seller->is_website) || !$seller->website_enabled) {
            return view('seller.storefront.pending', compact('seller'));
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
     * Return seller-specific flexy price for a given pack (AJAX endpoint)
     * This ensures the client cannot set the flexy price and gets server-calculated value.
     */
    public function getFlexyPrice(Request $request, string $username, $pack)
    {
        $seller = Seller::where('username', $username)
            ->where('status', 'active')
            ->firstOrFail();

        if ((isset($seller->is_website) && !$seller->is_website) || !$seller->website_enabled) {
            return response()->json(['success' => false, 'message' => 'Store not available'], 404);
        }

        $packModel = DiamondPack::find($pack);
        if (!$packModel) {
            return response()->json(['success' => false, 'message' => 'Pack not found'], 404);
        }

        // Make sure seller can sell that game
        if (!$seller->canSellGame($packModel->game_type)) {
            return response()->json(['success' => false, 'message' => 'Game not available'], 404);
        }

        // Ensure flexy is enabled for seller
        $flexyAllowed = isset($seller->is_flexy) ? (bool)$seller->is_flexy : $seller->flexy_enabled;
        if (!$flexyAllowed) {
            return response()->json(['success' => false, 'message' => 'Flexy disabled for this seller'], 403);
        }

        $customPrice = $seller->getCustomPrice($packModel->id);

        // If seller has a specific flexy_price configured, prefer it. Otherwise fall back to seller custom price or pack price.
        $flexyPrice = null;
        if ($customPrice && !is_null($customPrice->flexy_price)) {
            $flexyPrice = (float) $customPrice->flexy_price;
        } elseif ($customPrice) {
            $flexyPrice = (float) $customPrice->custom_price_dzd;
        } else {
            $flexyPrice = (float) $packModel->price_dzd;
        }

        return response()->json(['success' => true, 'flexy_price' => $flexyPrice]);
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
            // Zone ID is required for Mobile Legends specifically
            'zone_id' => 'required_if:game_type,mobilelegends|string|nullable',
            // nickname may be provided by client after confirmation (optional)
            'nickname' => 'nullable|string',
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
            'nickname' => $validated['nickname'] ?? null,
        ];

        // For Mobile Legends, server-side validate nickname via VipResellerService
        if ($validated['game_type'] === 'mobilelegends') {
            try {
                $vip = app(VipResellerService::class);
                $check = $vip->checkNickname($validated['player_id'], $validated['zone_id']);

                if (!isset($check['result']) || $check['result'] !== true || empty($check['data'])) {
                    return back()->withErrors(['player_id' => 'Unable to verify nickname for Mobile Legends. Please check your User ID and Zone ID.'])->withInput();
                }

                // Use the verified nickname (override if not provided by client)
                $playerData['nickname'] = $check['data'];
            } catch (\Exception $e) {
                \Log::error('Provider service failure during showPaymentMethod: ' . $e->getMessage(), ['player_id' => $validated['player_id'], 'zone_id' => $validated['zone_id']]);
                return back()->withErrors(['player_id' => 'Unable to verify nickname at this time. Please try again later.'])->withInput();
            }
        }

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
              // zone_id required for Mobile Legends
              'zone_id' => 'required_if:game_type,mobilelegends|string|nullable',
              'nickname' => 'nullable|string',
              'payment_method' => 'required|in:baridimob,flexy',
              // receipt should be required when using flexy
              'receipt' => 'required_if:payment_method,flexy|file|mimes:png,jpg,jpeg,pdf|max:10240',
              'description' => 'nullable|string|max:500',
        ]);

        $pack = DiamondPack::findOrFail($validated['pack_id']);

        // Validate product availability with Digiflazz
        if (!$pack->code) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Product code is missing. Please contact support.'], 400);
            }
            return back()->withErrors(['error' => 'Product code is missing. Please contact support.']);
        }

        $digiflazzService = app(\App\Services\DigiflazzService::class);
        $productData = $digiflazzService->checkProductAvailability($pack->code);

        if (!$productData) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unable to verify product availability. Please try again in a moment.'], 400);
            }
            return back()->withErrors(['error' => 'Unable to verify product availability. Please try again in a moment.']);
        }

        // Check if product is available
        if (!$productData['available']) {
            $reason = 'This product is not currently available';
            if (!$productData['buyer_product_status']) {
                $reason = 'This product has been disabled and is no longer available';
            } elseif (!$productData['seller_product_status']) {
                $reason = 'This product is temporarily unavailable from the seller';
            }
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $reason], 400);
            }
            return back()->withErrors(['error' => $reason]);
        }

        // Check quantity support (if quantity > 1, multi must be true)
        $quantity = (int)($request->input('quantity', 1));
        if ($quantity > 1 && !$productData['multi']) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => "This product doesn't support multiple quantities. Maximum quantity: 1"], 400);
            }
            return back()->withErrors(['error' => "This product doesn't support multiple quantities. Maximum quantity: 1"]);
        }

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

        // Ensure seller allows Flexy when chosen (respect legacy is_flexy flag if present)
        $flexyAllowed = isset($seller->is_flexy) ? (bool)$seller->is_flexy : $seller->flexy_enabled;
        if (($validated['payment_method'] ?? '') === 'flexy' && !$flexyAllowed) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Flexy payment is disabled for this seller'], 400);
            }
            return back()->withErrors(['error' => 'Flexy payment is disabled for this seller']);
        }

        // Re-validate selling price against base cost to prevent tampering (server-side)
        if ($sellingPrice < $baseCost) {
            Log::warning('Seller selling price is below base cost', ['seller_id' => $seller->id, 'pack_id' => $pack->id, 'selling_price' => $sellingPrice, 'base_cost' => $baseCost]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid price configuration detected. Please contact the seller.'], 400);
            }
            return back()->withErrors(['error' => 'Invalid price configuration detected. Please contact the seller.']);
        }

        // For Mobile Legends, ensure nickname is valid server-side (defense in depth)
        if ($pack->game_type === 'mobilelegends') {
            try {
                $vipSvc = app(VipResellerService::class);
                // Basic numeric check to avoid non-numeric input reaching external service
                if (!preg_match('/^\d+$/', $validated['player_id'] ?? '') || !preg_match('/^\d+$/', $validated['zone_id'] ?? '')) {
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => 'User ID and Zone ID must be numeric for Mobile Legends'], 422);
                    }
                    return back()->withErrors(['player_id' => 'User ID and Zone ID must be numeric for Mobile Legends'])->withInput();
                }

                $check = $vipSvc->checkNickname($validated['player_id'], $validated['zone_id']);
                if (!isset($check['result']) || $check['result'] !== true || empty($check['data'])) {
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => 'Unable to verify Mobile Legends nickname. Please check your User ID / Zone ID.'], 422);
                    }
                    return back()->withErrors(['player_id' => 'Unable to verify Mobile Legends nickname. Please check your User ID / Zone ID.'])->withInput();
                }

                // If server provided nickname exists, ensure consistency (optional)
                if (!empty($validated['nickname']) && $validated['nickname'] !== $check['data']) {
                    // If mismatch, prefer verified nickname but warn the user
                    $validated['nickname'] = $check['data'];
                }
            } catch (\Exception $e) {
                Log::error('Provider check failed during processPayment: ' . $e->getMessage(), ['player_id' => $validated['player_id'], 'zone_id' => $validated['zone_id']]);
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Nickname validation service unavailable. Please try again later.'], 500);
                }
                return back()->withErrors(['player_id' => 'Nickname validation service unavailable. Please try again later.'])->withInput();
            }
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

                $file = $request->file('receipt');
                if (!$file || !$file->isValid()) {
                    throw new \Exception('Uploaded receipt file is not valid');
                }

                // Defense-in-depth: strict server-side MIME and size validation
                $allowedMimes = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];
                $mime = $file->getClientMimeType();
                $size = $file->getSize();
                if (!in_array($mime, $allowedMimes, true)) {
                    throw new \Exception('Invalid receipt file type');
                }
                if ($size > 10 * 1024 * 1024) {
                    throw new \Exception('Receipt file too large');
                }

                // Virus scan the temporary upload path before storing
                $scanner = app(\App\Services\VirusScannerService::class);
                $scan = $scanner->scanFile($file->getPathname());
                if (!$scan['clean']) {
                    // Ensure any started transaction is rolled back before returning
                    DB::rollBack();
                    Log::warning('Virus scanner rejected uploaded receipt', ['message' => $scan['message'] ?? null]);
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Receipt flagged by virus scanner: ' . ($scan['message'] ?? 'infected')
                        ], 422);
                    }
                    return back()->withErrors(['receipt' => 'Receipt flagged by virus scanner: ' . ($scan['message'] ?? 'infected')]);
                }

                // Store receipt file to public disk after scanning
                $receiptPath = $file->store('flexy-receipts', 'public');
                if (!$receiptPath) {
                    throw new \Exception('Failed to store receipt file');
                }

                // We intentionally do NOT call Chargily here — Flexy is manual transfer waiting verification.
                // However, the price charged to the client for Flexy must be server-calculated and stored.
                // Determine the Flexy final price server-side (use seller flexy_price if configured)
                $customFlex = $seller->getCustomPrice($pack->id);
                if ($customFlex && !is_null($customFlex->flexy_price)) {
                    $sellingPrice = (float) $customFlex->flexy_price;
                } elseif ($customFlex) {
                    $sellingPrice = (float) $customFlex->custom_price_dzd;
                } else {
                    $sellingPrice = (float) $pack->price_dzd;
                }

                // Recalculate profit relative to seller internal cost
                $profit = $sellingPrice - $baseCost;
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
                    // sanitize description to prevent any HTML/JS injection
                    'flexy_description' => !empty($validated['description']) ? strip_tags($validated['description']) : null,
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

            // If the client requested JSON (AJAX), return checkout_url in JSON so client JS can redirect
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'checkout_url' => $chargilyResponse['checkout_url'] ?? null,
                    'checkout_id' => $chargilyResponse['checkout_id'] ?? $chargilyResponse['id'] ?? null,
                ], 200);
            }

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

        // Support both single-pack and multi-item orders
        $hasOrderItems = $order->orderItems && $order->orderItems->count() > 0;
        
        if ($hasOrderItems) {
            // Multi-item order: calculate expected base cost from order items
            $order->load('orderItems.diamondPack');
            $expectedBaseCost = 0;
            foreach ($order->orderItems as $orderItem) {
                $pack = $orderItem->diamondPack;
                if (!$pack) {
                    Log::error('Order item missing diamond pack when processing seller order', [
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                    ]);
                    return false;
                }
                $packBaseCost = $pack->base_price_dzd ?? $pack->price_dzd;
                $expectedBaseCost += $packBaseCost * $orderItem->quantity;
            }
        } else {
            // Legacy single-pack order
        $pack = $order->diamondPack;
            if (!$pack) {
                Log::error('Order missing diamond pack when processing seller order', [
                    'order_id' => $order->id,
                ]);
                return false;
            }
        // Recalculate expected base cost from the pack to protect against tampering
        $expectedBaseCost = $pack->base_price_dzd ?? $pack->price_dzd;
            // Apply quantity if set
            $quantity = (int)($order->quantity ?? 1);
            $expectedBaseCost *= $quantity;
        }

        // If the stored order seller_cost doesn't match the expected base cost, reject processing
        if (abs((float)$order->seller_cost - (float)$expectedBaseCost) > 0.01) {
            Log::error('Order seller_cost mismatch when processing seller order', [
                'order_id' => $order->id,
                'order_seller_cost' => $order->seller_cost,
                'expected_base' => $expectedBaseCost,
                'is_multi_item' => $hasOrderItems,
            ]);
            return false;
        }

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
