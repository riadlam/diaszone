<?php

namespace App\Http\Controllers;

    
    
use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\Flexy;
use App\Models\ChargilyStatus;
use App\Models\VipResellerStatus;
use App\Services\NowPaymentsService;
use App\Services\MixPayService;
use App\Services\ChargilyPayV2Service;
use App\Services\VipResellerService;
use App\Services\TelegramService;
use TheHocineSaad\LaravelChargilyEPay\Models\Epay_Invoice;
use TheHocineSaad\LaravelChargilyEPay\Epay_Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * API endpoint to fetch diamond packs by IDs
     */
    public function getPacks(Request $request)
    {
        // Validate and sanitize input to prevent SQL injection
        $request->validate([
            'ids' => 'nullable|array',
            'ids.*' => 'integer|min:1',
        ]);
        
        $packIds = $request->input('ids', []);
        
        if (empty($packIds)) {
            return response()->json(['packs' => []]);
        }
        
        // Ensure packIds is an array
        if (!is_array($packIds)) {
            $packIds = [$packIds];
        }
        
        // Sanitize: ensure all IDs are integers (extra safety)
        $packIds = array_filter(array_map('intval', $packIds), function($id) {
            return $id > 0;
        });
        
        if (empty($packIds)) {
            return response()->json(['packs' => []]);
        }
        
        // Fetch packs from database
        $packs = DiamondPack::whereIn('id', $packIds)
            ->where('is_active', true)
            ->get()
            ->map(function ($pack) {
                return [
                    'id' => $pack->id,
                    'diamonds' => $pack->diamonds,
                    'bonus' => $pack->bonus_diamonds,
                    'price' => (float) $pack->price,
                    'price_usd' => (float) ($pack->price_usd ?? $pack->price),
                    'price_dzd' => (float) ($pack->price_dzd ?? ($pack->price * 260)),
                    'discount' => (float) $pack->discount_percentage,
                    'game_type' => $pack->game_type ?? 'mobilelegends',
                    'name' => $pack->name ?? null,
                    'sort_order' => $pack->sort_order ?? 0,
                ];
            })
            ->keyBy('id');
        
        return response()->json(['packs' => $packs]);
    }

    /**
     * Return order status for authenticated customer
     */
    public function getOrderStatus(Order $order)
    {
        $user = auth()->user();
        if (!$user || (! $user->isAdmin() && $order->user_id !== $user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $digiflazz = $order->digiflazzStatuses()->latest()->first();

        return response()->json([
            'order_id' => $order->id,
            'status' => $order->status,
            'notes' => $order->notes,
            'digiflazz' => $digiflazz ? $digiflazz->toArray() : null,
        ], 200);
    }
    
    public function cart()
    {
        return view('pages.cart');
    }
    
    public function orderCheckout(Request $request)
    {
        // Get order ID from query parameter
        $orderId = $request->query('buy_now_orders');
        
        // For now, we'll use dummy data or fetch from session/database
        // In a real app, you'd fetch the actual order data
        
        // Example order data structure
        $orderData = [
            'order_id' => $orderId ?? '87306940',
            'pack_id' => 1, // This would come from the order
            'quantity' => 1,
            'user_id' => '205762973',
            'zone_id' => '4048',
        ];
        
        // Fetch pack details (in real app, this would come from the order)
        $pack = DiamondPack::find($orderData['pack_id']) ?? DiamondPack::first();
        
        if (!$pack) {
            abort(404, 'Pack not found');
        }
        
        // Calculate prices
        $unitPrice = $pack->price;
        $discountPercentage = $pack->discount_percentage ?? 0;
        $discountAmount = ($unitPrice * $discountPercentage) / 100;
        $priceAfterDiscount = $unitPrice - $discountAmount;
        $totalBeforeDiscount = $unitPrice * $orderData['quantity'];
        $totalDiscount = $discountAmount * $orderData['quantity'];
        $total = $priceAfterDiscount * $orderData['quantity'];
        
        // Calculate SEAGM Credits (example: 1 USD = ~416 credits)
        $seagmCredits = round($total * 416);
        
        return view('pages.checkout', [
            'order' => $orderData,
            'pack' => $pack,
            'unitPrice' => $unitPrice,
            'discountPercentage' => $discountPercentage,
            'discountAmount' => $discountAmount,
            'priceAfterDiscount' => $priceAfterDiscount,
            'totalBeforeDiscount' => $totalBeforeDiscount,
            'totalDiscount' => $totalDiscount,
            'total' => $total,
            'seagmCredits' => $seagmCredits,
        ]);
    }
    
    public function selectPayment(Request $request)
    {
        // Note: Client-side validation already checks for stored encrypted_order_id
        // and redirects to home if order is already created.
        // This prevents users from accessing /select after order creation.
        
        // Payment method data
        $paymentMethods = [
            [
                'id' => 'baridimob',
                'name' => 'Baridimob',
                'icon' => 'baridimob.png',
                'description' => 'Mobile payment method',
                'coming_soon' => false
            ],
            [
                'id' => 'cryptocurrency',
                'name' => 'Cryptocurrency',
                'icon' => 'cryptocurrency.webp',
                'description' => 'Pay with crypto (USD)',
                'coming_soon' => true
            ],
            [
                'id' => 'flexy',
                'name' => 'Flexy',
                'icon' => 'flexy.webp',
                'description' => 'Flexible payment option',
                'coming_soon' => false
            ]
        ];
        
        return view('pages.select-payment', [
            'paymentMethods' => $paymentMethods,
        ]);
    }
    
    /**
     * API endpoint to create order from cart data
     */
    public function createOrder(Request $request)
    {
        Log::info('=== ORDER CREATION STARTED ===', [
            'payment_method' => $request->input('payment_method'),
            'cart_items_count' => count($request->input('cart_items', [])),
            'user_id' => Auth::id(),
            'ip' => $request->ip(),
        ]);

        // Additional rate limiting check for authenticated users (per user, not per IP)
        if (Auth::check()) {
            $key = 'order_creation_user_' . Auth::id();
            $maxAttempts = 20; // Increased to match IP limit
            $decayMinutes = 1;
            
            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($key);
                return response()->json([
                    'success' => false,
                    'message' => 'Too many order creation attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).',
                ], 429);
            }
            
            RateLimiter::hit($key, $decayMinutes * 60);
        }
        
        // Prevent duplicate requests within 2 seconds (same cart + same payment method)
        $requestHash = md5(json_encode([
            'cart_items' => $request->input('cart_items'),
            'payment_method' => $request->input('payment_method'),
            'ip' => $request->ip(),
        ]));
        
        $duplicateKey = 'order_creation_duplicate_' . $requestHash;
        if (RateLimiter::tooManyAttempts($duplicateKey, 1)) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait a moment before trying again.',
            ], 429);
        }
        RateLimiter::hit($duplicateKey, 2); // 2 second window
        
        try {
            $request->validate([
                'cart_items' => 'required|array|min:1|max:10', // Allow up to 10 different packs
                'cart_items.*.pack_id' => 'required|exists:diamond_packs,id',
                'cart_items.*.quantity' => 'nullable|integer|min:1|max:20', // Quantity per pack
                'cart_items.*.user_id' => 'nullable|string',
                'cart_items.*.zone_id' => 'nullable|string',
                'cart_items.*.player_id' => 'nullable|string',
                'cart_items.*.player_id_ff' => 'nullable|string',
                'cart_items.*.player_id_pubg' => 'nullable|string',
                'cart_items.*.player_id_hok' => 'nullable|string',
                'cart_items.*.user_id_bs' => 'nullable|string',
                'cart_items.*.server_bs' => 'nullable|string',
                'cart_items.*.server' => 'nullable|string',
                'payment_method' => 'nullable|string|in:flexy,bmccp,cryptocurrency,coupon_free',
            ]);
            
            $cartItems = $request->input('cart_items');
            $paymentMethod = $request->input('payment_method'); // flexy, bmccp, cryptocurrency, or coupon_free
            $userId = Auth::check() ? Auth::id() : null;
            
            // Validate all packs exist and are from same game
            $packs = [];
            $gameType = null;
            $gameTypeMap = [];
            
            foreach ($cartItems as $item) {
                $pack = \App\Models\DiamondPack::find($item['pack_id']);
                if (!$pack) {
                    return response()->json([
                        'success' => false,
                        'message' => "Pack ID {$item['pack_id']} not found"
                    ], 404);
                }
                
                // Ensure all packs are from the same game
                if ($gameType === null) {
                    $gameType = $pack->game_type;
                } elseif ($gameType !== $pack->game_type) {
                    return response()->json([
                        'success' => false,
                        'message' => 'All packs must be from the same game'
                    ], 422);
                }
                
                $packs[$item['pack_id']] = $pack;
                $gameTypeMap[$item['pack_id']] = $pack->game_type;
            }
            
            // Validate user/player IDs based on game type
            $user_id_ml = null;
            $zone_id_ml = null;
            $player_id_ff = null;
            $player_id_pubg = null;
            $player_id_hok = null;
            $user_id_bs = null;
            $server_bs = null;
            
            // Get IDs from first item (all items should have same game type)
            $firstItem = $cartItems[0];
            if ($gameType === 'mobilelegends') {
                $user_id_ml = $firstItem['user_id'] ?? null;
                $zone_id_ml = $firstItem['zone_id'] ?? null;
                if (empty($user_id_ml) || empty($zone_id_ml)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User ID and Zone ID are required for Mobile Legends'
                    ], 422);
                }
            } elseif ($gameType === 'freefire') {
                $player_id_ff = $firstItem['player_id_ff'] ?? $firstItem['player_id'] ?? null;
                if (empty($player_id_ff)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Player ID is required for Free Fire'
                    ], 422);
                }
            } elseif ($gameType === 'pubgmobile') {
                $player_id_pubg = $firstItem['player_id_pubg'] ?? $firstItem['player_id'] ?? null;
                if (empty($player_id_pubg)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Player ID is required for PUBG Mobile'
                    ], 422);
                }
            } elseif ($gameType === 'honorofkings') {
                $player_id_hok = $firstItem['player_id_hok'] ?? $firstItem['player_id'] ?? null;
                if (empty($player_id_hok)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Player ID is required for Honor of Kings'
                    ], 422);
                }
            } elseif ($gameType === 'bloodstrike') {
                $user_id_bs = $firstItem['user_id_bs'] ?? $firstItem['user_id'] ?? null;
                $server_bs = $firstItem['server_bs'] ?? $firstItem['server'] ?? null;
                if (empty($user_id_bs) || empty($server_bs)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User ID and Server are required for Blood Strike'
                    ], 422);
                }
            }
            
            // Determine status based on payment method
            $orderStatus = 'pending'; // Default
            if ($paymentMethod === 'flexy') {
                $orderStatus = 'pending_flexy';
            } elseif ($paymentMethod === 'bmccp') {
                $orderStatus = 'pending_bmccp';
            } elseif ($paymentMethod === 'cryptocurrency') {
                $orderStatus = 'pending_cryptopay';
            } elseif ($paymentMethod === 'coupon_free') {
                $orderStatus = 'pending'; // Will be processed immediately by CouponController
            }
            
            // Create single order with multiple order_items
            return DB::transaction(function () use (
                $userId, $gameType, $user_id_ml, $zone_id_ml, $player_id_ff, $player_id_pubg,
                $player_id_hok, $user_id_bs, $server_bs, $cartItems, $packs, $orderStatus, $paymentMethod
            ) {
                // Determine which pack to use as primary (first pack)
                $primaryPack = reset($packs);
                
                // Create the order (using primary pack for backward compatibility)
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'user_id' => $userId,
                    'diamond_pack_id' => $primaryPack->id, // Keep for backward compatibility
                    'status' => $orderStatus,
                    'user_id_ml' => $user_id_ml,
                    'zone_id_ml' => $zone_id_ml,
                    'player_id_ff' => $player_id_ff,
                    'player_id_pubg' => $player_id_pubg,
                    'player_id_hok' => $player_id_hok,
                    'user_id_bs' => $user_id_bs,
                    'server_bs' => $server_bs,
                ]);
                
                // Create order_items and calculate totals
                // SECURITY: Always use current pack prices from database (prevents client-side manipulation)
                $totalOriginalPrice = 0;
                $totalDiscount = 0;
                $totalFinalPrice = 0;
                
                foreach ($cartItems as $item) {
                    // Re-fetch pack to ensure we have latest prices
                    $pack = \App\Models\DiamondPack::find($item['pack_id']);
                    if (!$pack || !$pack->is_active) {
                        throw new \Exception("Pack ID {$item['pack_id']} not found or inactive");
                    }
                    
                    // Validate quantity (max 20 per pack, min 1)
                    $quantity = max(1, min(20, (int)($item['quantity'] ?? ($pack->special_quantity ?? 1))));
                    
                    // Calculate prices from current pack data (not from client input)
                    $unitPriceDzd = $pack->price_dzd ?? ($pack->price * 260);
                    $unitPriceUsd = $pack->price_usd ?? $pack->price;
                    $discountPercentage = $pack->discount_percentage ?? 0;
                    $subtotalDzd = $unitPriceDzd * $quantity;
                    $discountAmount = ($unitPriceDzd * $discountPercentage / 100) * $quantity;
                    $totalDzd = $subtotalDzd - $discountAmount;
                    
                    // Create order item with validated prices
                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'diamond_pack_id' => $pack->id,
                        'quantity' => $quantity,
                        'unit_price_dzd' => $unitPriceDzd,
                        'unit_price_usd' => $unitPriceUsd,
                        'discount_percentage' => $discountPercentage,
                        'subtotal_dzd' => $subtotalDzd,
                        'discount_amount_dzd' => $discountAmount,
                        'total_dzd' => $totalDzd,
                    ]);
                    
                    $totalOriginalPrice += $subtotalDzd;
                    $totalDiscount += $discountAmount;
                    $totalFinalPrice += $totalDzd;
                }
                
                // Update order with total prices
                $order->original_price = $totalOriginalPrice;
                $order->discount_amount = $totalDiscount;
                $order->final_price = $totalFinalPrice;
                $order->save();
                
                // Send Telegram notification (skip pending_flexy status)
                if ($order->status !== 'pending_flexy') {
                    try {
                        $order->load(['orderItems.diamondPack', 'user']);
                        $message = TelegramService::formatOrderMessage($order);
                        $messageId = TelegramService::sendMessage($message);
                        if ($messageId) {
                            $order->tlg_message_id = $messageId;
                            $order->save();
                        }
                    } catch (\Exception $e) {
                        Log::error('Telegram notification failed for new order', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'final_price' => $order->final_price,
                    ],
                    'items_count' => count($cartItems),
                ], 201);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Order creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getTraceAsString() : 'Internal server error',
            ], 500);
        }
    }
    
    /**
     * Order success page for free coupon orders
     */
    public function orderSuccess(Order $order)
    {
        // Verify order belongs to current user (use int cast for type-safe comparison)
        if ((int) $order->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized');
        }
        
        // Load relationships
        $order->load(['diamondPack', 'coupon']);
        
        return view('pages.order-success', [
            'order' => $order,
        ]);
    }
    
    /**
     * API endpoint to get order by encrypted order_id
     * 
     * This endpoint:
     * 1. Receives encrypted_order_id from client (stored in localStorage as 'diaszone_encrypted_order_id')
     * 2. Decrypts the order_id on the backend
     * 3. Queries the order from database
     * 4. Returns order details
     */
    public function getOrderByEncryptedId(Request $request)
    {
        $request->validate([
            'encrypted_order_id' => 'required|string',
        ]);
        
        try {
            $orderId = Crypt::decryptString($request->input('encrypted_order_id'));
            $order = Order::with('diamondPack', 'flexy')->findOrFail($orderId);
            
            // Format order data for frontend
            $orderData = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'flexy_id' => $order->flexy_id, // Include flexy_id to check if payment receipt is uploaded
                'user_id_ml' => $order->user_id_ml,
                'zone_id_ml' => $order->zone_id_ml,
                'player_id_ff' => $order->player_id_ff,
                'player_id_pubg' => $order->player_id_pubg,
                'player_id_hok' => $order->player_id_hok,
                'user_id_bs' => $order->user_id_bs,
                'server_bs' => $order->server_bs,
                'notes' => $order->notes,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
                'diamond_pack' => [
                    'id' => $order->diamondPack->id,
                    'diamonds' => $order->diamondPack->diamonds,
                    'bonus_diamonds' => $order->diamondPack->bonus_diamonds,
                    'price' => (float) $order->diamondPack->price,
                    'price_usd' => (float) ($order->diamondPack->price_usd ?? $order->diamondPack->price),
                    'price_dzd' => (float) ($order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260)),
                    'discount_percentage' => (float) $order->diamondPack->discount_percentage,
                    'game_type' => $order->diamondPack->game_type ?? 'mobilelegends',
                    'name' => $order->diamondPack->name ?? null,
                ],
            ];
            
            // Calculate amount
            $unitPrice = $orderData['diamond_pack']['price'];
            $discountPercentage = $orderData['diamond_pack']['discount_percentage'] ?? 0;
            $discountAmount = ($unitPrice * $discountPercentage) / 100;
            $orderData['amount'] = $unitPrice - $discountAmount;
            
            return response()->json([
                'success' => true,
                'order' => $orderData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired order ID',
            ], 400);
        }
    }
    
    /**
     * API endpoint to validate nickname for Mobile Legends
     */
    public function validateNickname(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'zone_id' => 'required|string',
        ]);

        try {
            $vipResellerService = app(VipResellerService::class);
            $result = $vipResellerService->checkNickname($request->user_id, $request->zone_id);

            // Return the API response directly: {"result": true/false, "data": "nickname", "message": "..."}
            // Build a friendly message when the external service returned no message
            $message = $result['message'] ?? null;
            if (empty($message) && ($result['result'] ?? false) === false) {
                $message = 'Nickname not found or invalid for the provided User ID / Zone ID.';
            }

            return response()->json([
                'result' => $result['result'] ?? false,
                'data' => $result['data'] ?? null,
                'message' => $message,
            ], ($result['result'] === true) ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Nickname validation error: ' . $e->getMessage(), [
                'user_id' => $request->user_id,
                'zone_id' => $request->zone_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error validating nickname. Please try again.',
            ], 500);
        }
    }
    
    /**
     * Show Flexy form page
     */
    public function flexyForm(Request $request)
    {
        // Get encrypted order ID from query
        $encryptedOrderId = $request->query('order_id');
        
        if (!$encryptedOrderId) {
            return redirect()->route('select-payment')->with('error', 'Order ID is required');
        }
        
        try {
            // Decrypt the order ID
            $orderId = Crypt::decryptString($encryptedOrderId);
        } catch (\Exception $e) {
            return redirect()->route('select-payment')->with('error', 'Invalid order ID');
        }
        
        $order = Order::with('diamondPack')->find($orderId);
        
        if (!$order) {
            return redirect()->route('select-payment')->with('error', 'Order not found');
        }
        
        return view('pages.flexy-form', [
            'order' => $order,
        ]);
    }
    
    /**
     * Show Baridimob form page
     */
    public function baridimobForm($encryptedOrderId)
    {
        if (!$encryptedOrderId) {
            return redirect()->route('select-payment')->with('error', 'Order ID is required');
        }
        
        try {
            // Decrypt the order ID
            $orderId = Crypt::decryptString($encryptedOrderId);
        } catch (\Exception $e) {
            return redirect()->route('select-payment')->with('error', 'Invalid order ID');
        }
        
        $order = Order::with('diamondPack')->find($orderId);
        
        if (!$order) {
            return redirect()->route('select-payment')->with('error', 'Order not found');
        }
        
        return view('pages.baridimob-form', [
            'order' => $order,
            'encrypted_order_id' => $encryptedOrderId,
        ]);
    }
    
    /**
     * Payment success page - shows success message and redirects to orders
     */
    public function paymentSuccess($encryptedOrderId)
    {
        try {
            $orderId = Crypt::decryptString($encryptedOrderId);
            $order = Order::with('diamondPack')->find($orderId);
            
            if (!$order) {
                return redirect()->route('home')->with('error', 'Order not found');
            }
            
            return view('pages.payment-success', [
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Invalid order');
        }
    }
    
    /**
     * Handle Baridimob payment (Chargily Pay)
     */
    public function processBaridimobPayment(Request $request)
    {
        $request->validate([
            'encrypted_order_id' => 'required|string',
        ]);
        
        try {
            $orderId = Crypt::decryptString($request->encrypted_order_id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid order ID'
            ], 400);
        }
        
        // Load order with relationships - support both single-pack and multi-item orders
        $order = Order::with(['diamondPack', 'orderItems.diamondPack'])->find($orderId);
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        // Check if this is a multi-item order
        $hasOrderItems = $order->orderItems && $order->orderItems->count() > 0;
        
        // Initialize Chargily Pay v2 service
        $chargilyService = app(ChargilyPayV2Service::class);
        
        if (!$chargilyService->hasCredentials()) {
            // Check what's actually in the environment
            $v2Secret = env('CHARGILY_PAY_V2_SECRET');
            $epaySecret = env('CHARGILY_EPAY_SECRET');
            
            Log::error('Chargily Pay v2 credentials not found', [
                'CHARGILY_PAY_V2_SECRET_exists' => !empty($v2Secret),
                'CHARGILY_EPAY_SECRET_exists' => !empty($epaySecret),
                'v2_secret_length' => $v2Secret ? strlen($v2Secret) : 0,
                'epay_secret_length' => $epaySecret ? strlen($epaySecret) : 0,
                'v2_secret_preview' => $v2Secret ? (substr($v2Secret, 0, 10) . '...') : 'NOT SET',
                'epay_secret_preview' => $epaySecret ? (substr($epaySecret, 0, 10) . '...') : 'NOT SET',
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Baridimob payment is not configured. Please check your .env file and ensure CHARGILY_PAY_V2_SECRET (or CHARGILY_EPAY_SECRET) is set. After updating .env, run: php artisan config:clear'
            ], 500);
        }
        
        try {
            // Calculate amount - use stored final_price if available (most reliable)
            $amount = (float) ($order->final_price ?? 0);
            
            if ($hasOrderItems) {
                // Multi-item order: sum from order items if final_price not set
                if (empty($amount)) {
                    $amount = $order->orderItems->sum('total_dzd');
                    Log::info('Chargily payment: Multi-item order amount calculated from items', [
                        'order_id' => $order->id,
                        'items_count' => $order->orderItems->count(),
                        'calculated_amount' => $amount,
                    ]);
                }
            } else {
                // Legacy single-pack order: calculate from pack if final_price not set
                if (empty($amount) && $order->diamondPack) {
                    $unitPriceDzd = (float) ($order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260));
                    
                    // Fallback: if price_dzd is not set, calculate from price_usd or price
                    if (!$unitPriceDzd || $unitPriceDzd <= 0) {
                        $priceUsd = (float) ($order->diamondPack->price_usd ?? $order->diamondPack->price ?? 0);
                        $unitPriceDzd = $priceUsd * 260;
                        Log::warning('Chargily payment: price_dzd not found, using fallback calculation', [
                            'order_id' => $order->id,
                            'diamond_pack_id' => $order->diamond_pack_id,
                            'price_usd' => $priceUsd,
                            'calculated_price_dzd' => $unitPriceDzd,
                        ]);
                    }
                    
                    $discountPercentage = (float) ($order->diamondPack->discount_percentage ?? 0);
                    $quantity = (int) ($order->quantity ?? 1);
                    
                    $computedTotalBeforeDiscount = $unitPriceDzd * $quantity;
                    $computedDiscount = ($unitPriceDzd * $discountPercentage / 100) * $quantity;
                    $amount = $computedTotalBeforeDiscount - $computedDiscount;
                    
                    Log::info('Chargily payment: Legacy order amount calculated', [
                        'order_id' => $order->id,
                        'diamond_pack_id' => $order->diamond_pack_id,
                        'price_dzd' => $unitPriceDzd,
                        'discount_percentage' => $discountPercentage,
                        'discount_amount' => $computedDiscount,
                        'final_amount_dzd' => $amount,
                    ]);
                }
            }
            
            // Log the amount calculation for debugging
            Log::info('Chargily payment amount calculation', [
                'order_id' => $order->id,
                'is_multi_item' => $hasOrderItems,
                'final_amount_dzd' => $amount,
            ]);
            
            // Minimum amount is 75 DZD
            if ($amount < 75) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum payment amount is 75 DZD'
                ], 400);
            }
            
            // Get client info (use order data or default)
            $clientName = 'Customer';
            $clientEmail = 'customer@example.com';
            
            if (Auth::check()) {
                $user = Auth::user();
                $clientName = $user->name ?? 'Customer';
                $clientEmail = $user->email ?? 'customer@example.com';
            }
            
            // Determine game type for description
            // For multi-item orders, get game type from first order item
            // For legacy orders, use diamondPack
            if ($hasOrderItems && $order->orderItems->first()) {
                $gameType = $order->orderItems->first()->diamondPack->game_type ?? 'mobilelegends';
            } elseif ($order->diamondPack) {
                $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
            } else {
                $gameType = 'mobilelegends';
            }
            
            $gameName = 'Mobile Legends';
            $currencyText = 'Diamonds';
            
            if ($gameType === 'freefire') {
                $gameName = 'Free Fire';
                $currencyText = 'Diamonds';
            } elseif ($gameType === 'pubgmobile') {
                $gameName = 'PUBG Mobile';
                $currencyText = 'UC';
            } elseif ($gameType === 'honorofkings') {
                $gameName = 'Honor of Kings';
                $currencyText = 'Tokens';
            } elseif ($gameType === 'bloodstrike') {
                $gameName = 'Blood Strike';
                $currencyText = 'Golds';
            }
            
            // Build description based on order type
            if ($hasOrderItems) {
                $itemsCount = $order->orderItems->count();
                $description = "DiasZone - {$gameName} - {$itemsCount} pack(s)";
            } elseif ($order->diamondPack) {
                $packName = $order->diamondPack->name ?? ($order->diamondPack->diamonds . ' ' . $currencyText);
                $description = "DiasZone - {$gameName} - {$packName}";
            } else {
                $description = "DiasZone - {$gameName} - Order #{$order->order_number}";
            }
            
            // Create invoice in bmccp table first
            // For multi-item orders, use first pack ID (legacy compatibility) or null
            $firstPackId = null;
            if ($hasOrderItems && $order->orderItems->first()) {
                $firstPackId = $order->orderItems->first()->diamond_pack_id;
            } elseif ($order->diamond_pack_id) {
                $firstPackId = $order->diamond_pack_id;
            }
            
            $bmccp = \App\Models\Bmccp::create([
                'diamond_pack_id' => $firstPackId,
                'status' => 'pending',
                'notes' => $description,
            ]);
            
            // Update order with bmccp_id and status before creating checkout
            $order->bmccp_id = $bmccp->id;
            $order->status = 'pending_bmccp';
            $order->save();
            
            // Prepare checkout data for Chargily Pay v2
            // Note: Chargily Pay v2 expects amount in DZD (not centimes)
            $checkoutData = [
                'amount' => (int) round($amount), // Amount in DZD
                'currency' => 'dzd',
                'payment_method' => 'edahabia', // Baridimob uses EDAHABIA
                'success_url' => route('payment.success', ['encrypted_order_id' => $request->encrypted_order_id]),
                'failure_url' => route('baridimob-form', ['encrypted_order_id' => $request->encrypted_order_id]) . '?failed=1',
                'description' => $description,
                'locale' => 'en', // ar, en, or fr
            ];
            
            // Add webhook endpoint if not on localhost
            $isLocalhost = in_array(config('app.env'), ['local', 'testing']) || 
                          str_contains(request()->getHost(), 'localhost') ||
                          str_contains(request()->getHost(), '127.0.0.1');
            
            if (!$isLocalhost) {
                $checkoutData['webhook_endpoint'] = route('baridimob.webhook');
            }
            
            // Log configuration for debugging
            $apiSecret = env('CHARGILY_PAY_V2_SECRET') ?? env('CHARGILY_EPAY_SECRET');
            Log::info('Creating Chargily Pay v2 checkout', [
                'secret_exists' => !empty($apiSecret),
                'secret_length' => $apiSecret ? strlen($apiSecret) : 0,
                'amount' => $amount,
                'amount_dzd' => (int) round($amount),
                'payment_method' => 'edahabia',
                'checkout_data' => $checkoutData,
            ]);
            
            // Create checkout using Chargily Pay v2 API
            $checkoutResponse = $chargilyService->createCheckout($checkoutData);
            
            if (!$checkoutResponse['success']) {
                Log::error('Chargily Pay v2 checkout creation failed', [
                    'error' => $checkoutResponse['error'] ?? 'Unknown error',
                    'response_data' => $checkoutResponse['response_data'] ?? [],
                    'http_status' => $checkoutResponse['http_status'] ?? null,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment: ' . ($checkoutResponse['error'] ?? 'Unknown error')
                ], 500);
            }
            
            // Store checkout ID in bmccp record
            $checkoutId = $checkoutResponse['checkout_id'] ?? null;
            if ($checkoutId) {
                $bmccp->invoice_number = $checkoutId; // Store checkout ID as invoice_number for reference
                $bmccp->save();
                
                // Create initial chargily_status record
                $checkoutResponseData = $checkoutResponse['data'] ?? [];
                $chargilyStatus = ChargilyStatus::create([
                    'order_id' => $order->id,
                    'checkout_id' => $checkoutId,
                    'event_type' => 'checkout.created',
                    'status' => $checkoutResponseData['status'] ?? 'pending',
                    'amount' => $amount,
                    'fees' => $checkoutResponseData['fees'] ?? null,
                    'payment_method' => $checkoutResponseData['payment_method'] ?? 'edahabia',
                    'metadata' => $checkoutResponseData['metadata'] ?? null,
                    'webhook_data' => $checkoutResponseData,
                ]);
                
                // Link to order
                $order->chargily_status_id = $chargilyStatus->id;
                $order->save();
                
                Log::info('Chargily status created on checkout', [
                    'chargily_status_id' => $chargilyStatus->id,
                    'checkout_id' => $checkoutId,
                    'order_id' => $order->id,
                ]);
            }
            
            $checkoutUrl = $checkoutResponse['checkout_url'] ?? null;
            
            // If checkout URL is valid, return it
            if ($checkoutUrl && filter_var($checkoutUrl, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'success' => true,
                    'checkout_url' => $checkoutUrl,
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment. Please try again.'
            ], 500);
            
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Detect timeout errors with multiple patterns
            $isTimeoutError = str_contains($errorMessage, 'cURL error 28') 
                || str_contains($errorMessage, 'Connection timed out')
                || str_contains($errorMessage, 'timeout')
                || str_contains($errorMessage, 'CURLE_OPERATION_TIMEDOUT')
                || str_contains($errorMessage, '10001 milliseconds')
                || str_contains($errorMessage, '10002 milliseconds');
            
            $is401Error = str_contains($errorMessage, '401') || str_contains($errorMessage, 'Unauthorized');
            $configKey = config('laravel-chargily-epay.key');
            $isTestKey = $configKey && str_starts_with($configKey, 'test_');
            
            // Extract error code - simple numeric format for documentation
            // ERR-028 = cURL timeout, ERR-401 = Auth failed, ERR-500 = Server error, ERR-503 = Service unavailable
            $errorCode = 'ERR-500';  // Default generic error
            if (preg_match('/cURL error (\d+)/', $errorMessage, $matches)) {
                $errorCode = 'ERR-0' . $matches[1];  // e.g., ERR-028 for cURL error 28
            } elseif (str_contains($errorMessage, 'Connection timed out') || str_contains($errorMessage, 'timeout')) {
                $errorCode = 'ERR-028';  // Timeout error code
            } elseif (str_contains($errorMessage, '401') || str_contains($errorMessage, 'Unauthorized')) {
                $errorCode = 'ERR-401';
            } elseif (str_contains($errorMessage, '503')) {
                $errorCode = 'ERR-503';
            }
            
            \Log::error('Baridimob payment error: ' . $errorMessage, [
                'trace' => $e->getTraceAsString(),
                'order_id' => $orderId ?? 'unknown',
                'is_401_error' => $is401Error,
                'is_timeout_error' => $isTimeoutError,
                'is_test_key' => $isTestKey,
                'error_message' => $errorMessage,
                'error_code' => $errorCode,
            ]);
            
            // Provide helpful error message for timeout errors (Algerie Poste temporary outage)
            if ($isTimeoutError) {
                $locale = app()->getLocale();
                
                if ($locale === 'ar') {
                    $userMessage = 'عذراً، خدمة البريد الجزائري مغلقة مؤقتاً. يرجى محاولة الدفع مرة أخرى خلال 10 دقائق. شكراً لفهمك وصبرك.';
                    $shortMessage = 'خدمة البريد الجزائري مغلقة مؤقتاً';
                } elseif ($locale === 'fr') {
                    $userMessage = 'Désolé, le service de la Poste Algérienne est temporairement fermé. Veuillez réessayer d\'ici 10 minutes. Merci pour votre compréhension et votre patience.';
                    $shortMessage = 'Service La Poste fermé temporairement';
                } else {
                    $userMessage = 'Sorry, Algerie Poste service is temporarily unavailable. Please try again in 10 minutes. Thank you for your understanding and patience.';
                    $shortMessage = 'Algerie Poste temporarily unavailable';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $userMessage,  // Friendly message only, no technical details
                    'short_message' => $shortMessage,
                    'error_code' => $errorCode,  // Simple code like ERR-028 for documentation
                    'retry_after' => 600,
                    'is_timeout' => true
                ], 503);
            }
            
            // Provide helpful error message for 401 errors
            if ($is401Error) {
                $userMessage = 'Payment authentication failed. ';
                if ($isTestKey) {
                    $userMessage .= 'Test API keys may not work for API calls. Please verify you are using production credentials from your Chargily dashboard at https://epay.chargily.com.dz';
                } else {
                    $userMessage .= 'Please verify your Chargily API credentials are correct and active in your dashboard.';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $userMessage,
                    'error_details' => '401 Unauthorized - Invalid API credentials',
                    'technical_error_code' => $errorCode,
                ], 401);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $errorMessage,
                'technical_error_code' => $errorCode,
                'raw_error' => $errorMessage
            ], 500);
        }
    }
    
    /**
     * Handle Baridimob webhook from Chargily Pay v2
     * According to: https://dev.chargily.com/pay-v2/webhooks
     * 
     * Webhook structure:
     * {
     *   "id": "event_id",
     *   "entity": "event",
     *   "type": "checkout.paid" | "checkout.failed" | "checkout.canceled",
     *   "data": { checkout object }
     * }
     */
    public function baridimobWebhook(Request $request)
    {
        try {
            // Get raw payload for signature verification
            $rawPayload = $request->getContent();
            
            // Log incoming webhook for debugging
            Log::info('Chargily Pay v2 webhook received', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
                'raw_payload' => $rawPayload,
            ]);
            
            // STEP 1: Verify signature (HMAC SHA256)
            $signature = $request->header('signature');
            
            if (empty($signature)) {
                Log::warning('Chargily Pay v2 webhook: Missing signature header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json(['error' => 'Missing signature'], 400);
            }
            
            // Get API secret key
            $chargilyService = app(ChargilyPayV2Service::class);
            $apiSecret = config('services.chargily_pay_v2.secret') ?? config('laravel-chargily-epay.secret');
            
            if (empty($apiSecret)) {
                Log::error('Chargily Pay v2 webhook: API secret not configured');
                return response()->json(['error' => 'Server configuration error'], 500);
            }
            
            // Calculate expected signature (HMAC SHA256)
            $expectedSignature = hash_hmac('sha256', $rawPayload, $apiSecret);
            
            // Verify signature using hash_equals to prevent timing attacks
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Chargily Pay v2 webhook: Invalid signature', [
                    'received' => substr($signature, 0, 20) . '...',
                    'expected' => substr($expectedSignature, 0, 20) . '...',
                    'ip' => $request->ip(),
                ]);
                return response()->json(['error' => 'Invalid signature'], 403);
            }
            
            Log::info('Chargily Pay v2 webhook: Signature verified successfully');
            
            // STEP 2: Parse webhook payload
            $webhookData = json_decode($rawPayload, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Chargily Pay v2 webhook: Invalid JSON payload', [
                    'json_error' => json_last_error_msg(),
                ]);
                return response()->json(['error' => 'Invalid JSON'], 400);
            }
            
            // Extract event type and checkout data
            $eventType = $webhookData['type'] ?? null;
            $checkoutData = $webhookData['data'] ?? null;
            
            if (empty($eventType) || empty($checkoutData)) {
                Log::warning('Chargily Pay v2 webhook: Missing event type or data', [
                    'event_type' => $eventType,
                    'has_data' => !empty($checkoutData),
                ]);
                return response()->json(['error' => 'Invalid payload structure'], 400);
            }
            
            $checkoutId = $checkoutData['id'] ?? null;
            $checkoutStatus = $checkoutData['status'] ?? null;
            $amount = $checkoutData['amount'] ?? null;
            $fees = $checkoutData['fees'] ?? null;
            $paymentMethod = $checkoutData['payment_method'] ?? null;
            $metadata = $checkoutData['metadata'] ?? null;
            
            Log::info('Chargily Pay v2 webhook: Processing event', [
                'event_type' => $eventType,
                'checkout_id' => $checkoutId,
                'checkout_status' => $checkoutStatus,
            ]);
            
            // STEP 3: Find order by checkout_id
            $order = null;
            $chargilyStatus = null;
            
            if ($checkoutId) {
                // Try to find by chargily_status first
                $chargilyStatus = ChargilyStatus::where('checkout_id', $checkoutId)->first();
                
                if ($chargilyStatus) {
                    if ($chargilyStatus->order_id) {
                        $order = Order::find($chargilyStatus->order_id);
                    } else {
                        // Detailed log when chargily_status exists but not linked to an order
                        Log::warning('Chargily Pay v2 webhook: Found ChargilyStatus without order_id', [
                            'chargily_status_id' => $chargilyStatus->id,
                            'checkout_id' => $chargilyStatus->checkout_id,
                            'chargily_response_snippet' => substr(json_encode($chargilyStatus->webhook_data ?? $chargilyStatus->response_data ?? []), 0, 400),
                        ]);
                    }
                }
                
                // If not found, try to find by bmccp invoice_number
                if (!$order) {
                    $bmccp = \App\Models\Bmccp::where('invoice_number', $checkoutId)->first();
                    if ($bmccp) {
                        $order = Order::where('bmccp_id', $bmccp->id)->first();
                        if (!$order) {
                            Log::warning('Chargily Pay v2 webhook: bmccp matched but no order found for bmccp', [
                                'bmccp_id' => $bmccp->id,
                                'invoice_number' => $bmccp->invoice_number,
                            ]);
                        }
                    }
                }
            }
            
            // STEP 4: Save/Update chargily_status
            if ($chargilyStatus) {
                // Update existing status
                Log::info('Chargily Pay v2: Updating existing ChargilyStatus from webhook', [
                    'chargily_status_id' => $chargilyStatus->id,
                    'checkout_id' => $chargilyStatus->checkout_id,
                    'event' => $eventType,
                ]);
                $chargilyStatus->update([
                    'event_type' => $eventType,
                    'status' => $checkoutStatus ?? 'pending',
                    'amount' => $amount,
                    'fees' => $fees,
                    'payment_method' => $paymentMethod,
                    'metadata' => $metadata,
                    'webhook_data' => $webhookData,
                ]);
            } else {
                Log::info('Chargily Pay v2: Creating new ChargilyStatus from webhook', [
                    'checkout_id' => $checkoutId,
                    'order_id_candidate' => $order ? $order->id : null,
                    'event' => $eventType,
                ]);
                // Create new status record
                $chargilyStatus = ChargilyStatus::create([
                    'order_id' => $order ? $order->id : null,
                    'checkout_id' => $checkoutId,
                    'event_type' => $eventType,
                    'status' => $checkoutStatus ?? 'pending',
                    'amount' => $amount,
                    'fees' => $fees,
                    'payment_method' => $paymentMethod,
                    'metadata' => $metadata,
                    'webhook_data' => $webhookData,
                ]);
                
                // Link to order if found
                if ($order) {
                    $order->chargily_status_id = $chargilyStatus->id;
                    $order->save();
                    Log::info('Chargily Pay v2: Linked new ChargilyStatus to order', [
                        'chargily_status_id' => $chargilyStatus->id,
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                    ]);
                }
            }
            
            // STEP 5: Handle event and update order status
            if ($order) {
                switch ($eventType) {
                    case 'checkout.paid':
                        // Payment successful - Set status to "sending" (payment done, waiting for topup)
                        $oldOrderStatus = $order->status;
                        if ($oldOrderStatus !== 'sending' && $oldOrderStatus !== 'completed') {
                            $order->status = 'sending';
                            $order->save();
                            
                            Log::info('Chargily Pay v2: Payment successful - Order status set to sending (waiting for topup)', [
                                'checkout_id' => $checkoutId,
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'old_status' => $oldOrderStatus,
                                'new_status' => 'sending',
                            ]);
                            
                            // Update Telegram message if exists, otherwise send new one
                            try {
                                $order->load('diamondPack', 'user', 'vipResellerStatuses', 'seller');
                                $updatedMessage = TelegramService::formatOrderMessage($order);
                                $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '⏳ <b>Order Confirmed - Processing Recharge</b>', $updatedMessage);
                                
                                if ($order->tlg_message_id) {
                                    // Update existing message
                                    TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                                } else {
                                    // Send new message if no existing message
                                    $messageId = TelegramService::sendMessage($updatedMessage);
                                    if ($messageId) {
                                        $order->tlg_message_id = $messageId;
                                        $order->save();
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::error('Telegram notification failed for payment success', [
                                    'order_id' => $order->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                            
                            // Update bmccp if exists
                            if ($order->bmccp_id) {
                                $bmccp = \App\Models\Bmccp::find($order->bmccp_id);
                                if ($bmccp) {
                                    $bmccp->status = 'approved';
                                    $bmccp->save();
                                }
                            }
                            
                            // Trigger automatic recharge for Mobile Legends orders
                            // processChargilyRecharge will update order status based on VIP Reseller response
                            $rechargeResult = $this->processChargilyRecharge($order);
                            
                            if ($rechargeResult['success']) {
                                Log::info('Chargily Pay v2: Payment successful and recharge processed', [
                                    'checkout_id' => $checkoutId,
                                    'order_id' => $order->id,
                                    'order_number' => $order->order_number,
                                    'trxid' => $rechargeResult['trxid'] ?? null,
                                    'order_status' => $order->fresh()->status, // Get updated status
                                ]);
                                
                                // Update Telegram message with balance after VIP Reseller order is created
                                if ($order->tlg_message_id) {
                                    try {
                                        $order->refresh();
                                        $order->load('diamondPack', 'user', 'vipResellerStatuses', 'seller');
                                        $updatedMessage = TelegramService::formatOrderMessage($order);
                                        // Update header if still showing old status
                                        if (strpos($updatedMessage, '🆕 <b>New Order Created</b>') !== false) {
                                            $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '⏳ <b>Order Confirmed - Processing Recharge</b>', $updatedMessage);
                                        }
                                        TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                                    } catch (\Exception $e) {
                                        Log::error('Failed to update Telegram message with balance after recharge', [
                                            'order_id' => $order->id,
                                            'error' => $e->getMessage(),
                                        ]);
                                    }
                                }
                            } else {
                                Log::warning('Chargily Pay v2: Payment successful but recharge failed', [
                                    'checkout_id' => $checkoutId,
                                    'order_id' => $order->id,
                                    'order_number' => $order->order_number,
                                    'recharge_message' => $rechargeResult['message'] ?? 'Unknown error',
                                    'order_status' => $order->fresh()->status, // Get current status
                                ]);
                            }
                        } else {
                            Log::info('Chargily Pay v2: Payment successful (order already completed)', [
                                'checkout_id' => $checkoutId,
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                            ]);
                        }
                        break;
                        
                    case 'checkout.failed':
                    case 'checkout.canceled':
                        // Payment failed or canceled
                        if (!in_array($order->status, ['cancelled', 'refunded'])) {
                            $order->status = 'cancelled';
                            $order->save();
                            
                            // Update bmccp if exists
                            if ($order->bmccp_id) {
                                $bmccp = \App\Models\Bmccp::find($order->bmccp_id);
                                if ($bmccp) {
                                    $bmccp->status = 'rejected';
                                    $bmccp->save();
                                }
                            }
                        }
                        
                        Log::info('Chargily Pay v2: Payment failed/canceled', [
                            'checkout_id' => $checkoutId,
                            'event_type' => $eventType,
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                        ]);
                        break;
                }
            } else {
                // Provide detailed logging to help troubleshooting missing order links
                $details = [
                    'checkout_id' => $checkoutId,
                    'event_type' => $eventType,
                ];
                if ($chargilyStatus) {
                    $details['chargily_status_id'] = $chargilyStatus->id;
                    $details['chargily_status_order_id'] = $chargilyStatus->order_id;
                    $details['chargily_status_webhook_data_snippet'] = substr(json_encode($chargilyStatus->webhook_data ?? $chargilyStatus->response_data ?? []), 0, 400);
                }
                if (isset($bmccp) && $bmccp) {
                    $details['bmccp_id'] = $bmccp->id;
                    $details['bmccp_invoice_number'] = $bmccp->invoice_number;
                }

                Log::warning('Chargily Pay v2 webhook: Order not found', $details);
            }
            
            // STEP 6: Return 200 OK response
            return response()->json(['success' => true], 200);
            
        } catch (\Exception $e) {
            Log::error('Chargily Pay v2 webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Process recharge for Chargily completed orders
     * This method is called when Chargily payment is successful (checkout.paid)
     * Similar to processRecharge in AdminController but for Chargily payments
     */
    private function processChargilyRecharge(Order $order)
    {
        try {
            // Load order with diamond pack relationship
            $order->load('diamondPack');
            
            // Get game type
            $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
            
            // Only process Mobile Legends and Free Fire orders
            if (!in_array($gameType, ['mobilelegends', 'freefire'])) {
                Log::info('Chargily recharge skipped: Unsupported game type', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'game_type' => $gameType,
                ]);
                return [
                    'success' => false,
                    'message' => 'Recharge only supported for Mobile Legends and Free Fire orders',
                ];
            }

            $vipReseller = app(VipResellerService::class);
            $packageCode = $order->diamondPack->code ?? null;
            
            if (empty($packageCode)) {
                Log::warning('Chargily recharge skipped: Missing package code', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'diamond_pack_id' => $order->diamond_pack_id,
                ]);
                return [
                    'success' => false,
                    'message' => 'Package code not found',
                ];
            }
            
            // Handle based on game type
            if ($gameType === 'mobilelegends') {
                // Check if user_id_ml and zone_id_ml are set
                if (empty($order->user_id_ml) || empty($order->zone_id_ml)) {
                    Log::warning('Chargily recharge skipped: Missing user_id_ml or zone_id_ml', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'user_id_ml' => $order->user_id_ml,
                        'zone_id_ml' => $order->zone_id_ml,
                    ]);
                    return [
                        'success' => false,
                        'message' => 'Missing User ID or Zone ID for Mobile Legends',
                    ];
                }

                // STEP 1: Validate nickname BEFORE processing recharge (Mobile Legends only)
                Log::info('Chargily: Validating nickname before recharge', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id_ml' => $order->user_id_ml,
                    'zone_id_ml' => $order->zone_id_ml,
                ]);

                $nicknameValidation = $vipReseller->checkNickname($order->user_id_ml, $order->zone_id_ml);

                if ($nicknameValidation['result'] !== true) {
                    Log::error('Chargily recharge aborted: Nickname validation failed', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'user_id_ml' => $order->user_id_ml,
                        'zone_id_ml' => $order->zone_id_ml,
                        'validation_message' => $nicknameValidation['message'] ?? 'Unknown error',
                    ]);
                    return [
                        'success' => false,
                        'message' => 'Nickname validation failed: ' . ($nicknameValidation['message'] ?? 'Invalid User ID or Zone ID'),
                    ];
                }

                // Nickname validation successful
                $nickname = $nicknameValidation['data'] ?? 'Unknown';
                Log::info('Chargily: Nickname validation successful - Proceeding with recharge', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id_ml' => $order->user_id_ml,
                    'zone_id_ml' => $order->zone_id_ml,
                    'nickname' => $nickname,
                ]);

                // For seller storefront orders: deduct seller wallet (base cost) before placing the VIP order
                if ($order->seller_id && !$order->wallet_deducted) {
                    // Attempt to deduct from seller wallet. If this fails, stop and record error status.
                    $sellerChargeResult = \App\Http\Controllers\Seller\SellerStorefrontController::processSellerOrder($order);
                    if ($sellerChargeResult !== true) {
                        Log::error('Chargily recharge aborted: seller insufficient balance', [
                            'order_id' => $order->id,
                            'seller_id' => $order->seller_id,
                            'seller_balance' => $order->seller ? $order->seller->wallet_balance : null,
                            'required' => $order->seller_cost,
                        ]);

                        // Save an error vipreseller status so admin can review
                        // Include legacy service name inside additional_data for compatibility
                        $vipResellerStatus = VipResellerStatus::create([
                            'order_id' => $order->id,
                            'trxid' => null,
                            'data' => null,
                            'zone' => null,
                            'status' => 'error',
                            'note' => 'Insufficient seller wallet balance',
                            'price' => null,
                            'additional_data' => [
                                'required' => $order->seller_cost,
                                'wallet_balance' => $order->seller ? $order->seller->wallet_balance : null,
                                'service' => $packageCode,
                            ],
                        ]);

                        return [
                            'success' => false,
                            'message' => 'Seller has insufficient wallet balance to process this order.',
                        ];
                    }
                }

                // STEP 2: Call provider API to recharge (Mobile Legends)
                Log::info('Chargily: Calling provider API for Mobile Legends', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'package_code' => $packageCode,
                    'player_id' => $order->user_id_ml,
                    'zone_id' => $order->zone_id_ml,
                    'seller_id' => $order->seller_id,
                    'wallet_deducted' => $order->wallet_deducted,
                    'seller_cost' => $order->seller_cost,
                    'quantity' => $order->quantity ?? 1,
                ]);

                // Multi-item order support: Submit top-ups for each order_item
                if (config('services.digiflazz.username') || env('DIGIFLAZZ_USERNAME')) {
                    // Atomic multi-quantity submission with proper locking
                    DB::transaction(function () use (&$result, &$order) {
                        // Lock the order to prevent concurrent modifications
                        $orderLocked = Order::where('id', $order->id)->lockForUpdate()->first();
                        if (!$orderLocked) {
                            Log::error('Chargily: Failed to lock order for Digiflazz submission', ['order_id' => $order->id]);
                            return;
                        }

                        $orderLocked->load('orderItems.diamondPack');
                        
                        // SECURITY: Re-calculate and validate prices before top-up to prevent manipulation
                        $totalOriginalPrice = 0;
                        $totalDiscount = 0;
                        $totalFinalPrice = 0;
                        $priceValidationErrors = [];
                        
                        foreach ($orderLocked->orderItems as $orderItem) {
                            $pack = $orderItem->diamondPack;
                            
                            // Re-calculate prices from current pack data (prevents price manipulation)
                            $unitPriceDzd = $pack->price_dzd ?? ($pack->price * 260);
                            $unitPriceUsd = $pack->price_usd ?? $pack->price;
                            $discountPercentage = $pack->discount_percentage ?? 0;
                            $quantity = max(1, (int)$orderItem->quantity);
                            
                            $subtotalDzd = $unitPriceDzd * $quantity;
                            $discountAmount = ($unitPriceDzd * $discountPercentage / 100) * $quantity;
                            $totalDzd = $subtotalDzd - $discountAmount;
                            
                            // Validate: stored prices should match calculated prices (within 1 DZD tolerance)
                            $storedTotal = (float)$orderItem->total_dzd;
                            $calculatedTotal = (float)$totalDzd;
                            $priceDiff = abs($storedTotal - $calculatedTotal);
                            
                            if ($priceDiff > 1.0) {
                                $priceValidationErrors[] = [
                                    'order_item_id' => $orderItem->id,
                                    'pack_id' => $pack->id,
                                    'pack_name' => $pack->name,
                                    'stored_price' => $storedTotal,
                                    'calculated_price' => $calculatedTotal,
                                    'difference' => $priceDiff
                                ];
                            }
                            
                            // Update order_item with recalculated prices (use current pack prices)
                            $orderItem->unit_price_dzd = $unitPriceDzd;
                            $orderItem->unit_price_usd = $unitPriceUsd;
                            $orderItem->discount_percentage = $discountPercentage;
                            $orderItem->subtotal_dzd = $subtotalDzd;
                            $orderItem->discount_amount_dzd = $discountAmount;
                            $orderItem->total_dzd = $totalDzd;
                            $orderItem->quantity = $quantity; // Ensure quantity is valid
                            $orderItem->save();
                            
                            $totalOriginalPrice += $subtotalDzd;
                            $totalDiscount += $discountAmount;
                            // Note: totalDzd here is per-item total (after pack discount, before coupon)
                            // We'll calculate final order total after applying coupon discount below
                        }
                        
                        // If individual item price validation fails, abort and log
                        if (!empty($priceValidationErrors)) {
                            Log::error('Chargily: Price validation failed - potential manipulation detected', [
                                'order_id' => $orderLocked->id,
                                'order_number' => $orderLocked->order_number,
                                'errors' => $priceValidationErrors,
                                'stored_final_price' => $orderLocked->final_price,
                                'calculated_original_price' => $totalOriginalPrice
                            ]);
                            $result = [
                                'result' => false,
                                'message' => 'Price validation failed. Order prices do not match current pack prices.',
                                'errors' => $priceValidationErrors
                            ];
                            return;
                        }
                        
                        // Apply coupon discount if order has a coupon
                        $orderDiscountAmount = 0;
                        $calculatedFinalPrice = $totalOriginalPrice - $totalDiscount; // After pack discounts
                        
                        if ($orderLocked->coupon_id) {
                            $orderLocked->load('coupon');
                            if ($orderLocked->coupon) {
                                // Re-calculate coupon discount on the original total (before pack discounts)
                                // This matches how coupons are typically applied in the order creation flow
                                $couponDiscountInfo = $orderLocked->coupon->calculateDiscount($totalOriginalPrice);
                                $orderDiscountAmount = $couponDiscountInfo['discount_amount'];
                                $calculatedFinalPrice = $couponDiscountInfo['final_amount'] - $totalDiscount;
                                // Ensure final price doesn't go below 0
                                $calculatedFinalPrice = max(0, $calculatedFinalPrice);
                                
                                Log::info('Chargily: Coupon discount recalculated', [
                                    'order_id' => $orderLocked->id,
                                    'coupon_id' => $orderLocked->coupon_id,
                                    'original_total' => $totalOriginalPrice,
                                    'coupon_discount' => $orderDiscountAmount,
                                    'pack_discounts' => $totalDiscount,
                                    'calculated_final_price' => $calculatedFinalPrice
                                ]);
                            } else {
                                Log::warning('Chargily: Order has coupon_id but coupon not found', [
                                    'order_id' => $orderLocked->id,
                                    'coupon_id' => $orderLocked->coupon_id
                                ]);
                            }
                        }
                        
                        // Validate final order price (accounting for coupon discount)
                        $storedFinalPrice = (float)($orderLocked->final_price ?? 0);
                        $finalPriceDiff = abs($storedFinalPrice - $calculatedFinalPrice);
                        
                        if ($finalPriceDiff > 1.0) {
                            Log::error('Chargily: Final price validation failed - potential manipulation detected', [
                                'order_id' => $orderLocked->id,
                                'order_number' => $orderLocked->order_number,
                                'stored_final_price' => $storedFinalPrice,
                                'calculated_final_price' => $calculatedFinalPrice,
                                'difference' => $finalPriceDiff,
                                'has_coupon' => !empty($orderLocked->coupon_id),
                                'coupon_discount' => $orderDiscountAmount,
                                'pack_discounts' => $totalDiscount,
                                'original_price' => $totalOriginalPrice
                            ]);
                            $result = [
                                'result' => false,
                                'message' => 'Price validation failed. Final order price does not match calculated price.',
                                'stored_price' => $storedFinalPrice,
                                'calculated_price' => $calculatedFinalPrice
                            ];
                            return;
                        }
                        
                        // Update order with recalculated total prices
                        $orderLocked->original_price = $totalOriginalPrice;
                        $orderLocked->discount_amount = $totalDiscount + $orderDiscountAmount; // Total discount (pack + coupon)
                        $orderLocked->final_price = $calculatedFinalPrice;
                        $orderLocked->save();
                        
                        Log::info('Chargily: Price validation passed, proceeding with top-up', [
                            'order_id' => $orderLocked->id,
                            'final_price' => $totalFinalPrice,
                            'items_count' => $orderLocked->orderItems->count()
                        ]);
                        
                        $lastResult = ['result' => false, 'message' => 'No provider calls made'];

                        // Submit top-ups for each order_item
                        foreach ($orderLocked->orderItems as $orderItem) {
                            // Check how many DigiflazzStatus records already exist for this item
                            $submitted = $orderItem->digiflazzStatuses()
                                ->where(function ($q) {
                                    $q->whereIn('status', ['Sukses', 'sukses', 'SUCCESS', 'success', 'waiting', 'pending'])
                                      ->orWhere('event', 'create');
                                })->count();

                            $remaining = max(0, $orderItem->quantity - $submitted);

                            // Submit remaining top-ups for this item
                            for ($i = 0; $i < $remaining; $i++) {
                                $refId = 'order-' . $orderLocked->id . '-item-' . $orderItem->id . '-' . Str::random(8);
                                
                                $lastResult = app(\App\Services\DigiflazzService::class)->placeOrderWithRefId(
                                    $orderItem->diamondPack,
                                    $orderLocked,
                                    $refId,
                                    $orderItem->id
                                );
                                
                                Log::info('Chargily: Digiflazz placeOrder attempt', [
                                    'order_id' => $orderLocked->id,
                                    'order_item_id' => $orderItem->id,
                                    'pack_id' => $orderItem->diamond_pack_id,
                                    'quantity' => $orderItem->quantity,
                                    'remaining' => $remaining - ($i + 1),
                                    'ref_id' => $refId,
                                    'result' => $lastResult
                                ]);
                                
                                // Small delay to ensure DigiflazzStatus record is committed
                                usleep(100000); // 0.1 second
                            }
                        }

                        $result = $lastResult;
                        $order = $orderLocked; // Update order reference
                    });
                } else {
                    // Legacy vipReseller flow also might need multiple calls when quantity>1
                    $existing = $order->vipResellerStatuses()->count();
                    $remaining = max(0, $required - $existing);
                    $lastResult = ['result' => false, 'message' => 'No provider calls made'];

                    for ($i = 0; $i < $remaining; $i++) {
                        $attempt = $i + 1;
                        $lastResult = $vipReseller->placeOrder(
                            $packageCode,
                            $order->user_id_ml,
                            $order->zone_id_ml
                        );

                        Log::info('Chargily: vipReseller placeOrder attempt', ['order_id' => $order->id, 'order_number' => $order->order_number, 'attempt' => $attempt, 'remaining_after' => $remaining - $attempt, 'result' => $lastResult]);
                    }

                    $result = $lastResult;
                }

                // If Digiflazz is used, create a lightweight VipResellerStatus mirror so admin/telegram can show provider info immediately
                if (config('services.digiflazz.username') || env('DIGIFLAZZ_USERNAME')) {
                    try {
                        $apiData = $result['data'] ?? [];
                        $balance = $apiData['buyer_last_saldo'] ?? $apiData['balance'] ?? null;
                        $vipData = [
                            'order_id' => $order->id,
                            'trxid' => $apiData['trxid'] ?? null,
                            'data' => $apiData['customer_no'] ?? $order->user_id_ml,
                            'zone' => $apiData['zone'] ?? $order->zone_id_ml,
                            // do not set `service` column on `digiflazz_statuses` (legacy field)
                            'status' => strtolower(($apiData['status'] ?? $apiData['rc'] ?? 'waiting')) === 'sukses' || ($apiData['rc'] ?? null) === '00' ? 'success' : 'waiting',
                            'note' => $apiData['message'] ?? null,
                            'price' => $apiData['price'] ?? null,
                            'balance' => $balance,
                            'additional_data' => array_merge($apiData, ['balance' => $balance, 'service' => 'digiflazz']),
                        ];

                        if (!empty($vipData['trxid'])) {
                            \App\Models\VipResellerStatus::updateOrCreate(['trxid' => $vipData['trxid']], $vipData);
                        } else {
                            \App\Models\VipResellerStatus::create($vipData);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Chargily: Failed to create VipResellerStatus mirror after Digiflazz placeOrder', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                    }
                }

                Log::info('Chargily: provider API response', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'api_result' => $result,
                ]);
                
                $playerId = $order->user_id_ml;
                $zoneId = $order->zone_id_ml;
                
            } elseif ($gameType === 'freefire') {
                // Check if player_id_ff is set
                if (empty($order->player_id_ff)) {
                    Log::warning('Chargily recharge skipped: Missing player_id_ff', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'player_id_ff' => $order->player_id_ff,
                    ]);
                    return [
                        'success' => false,
                        'message' => 'Missing Player ID for Free Fire',
                    ];
                }

                // Free Fire doesn't support nickname check, proceed directly
                Log::info('Chargily: Processing Free Fire recharge (no nickname check)', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'player_id_ff' => $order->player_id_ff,
                    'package_code' => $packageCode,
                ]);

                // For seller storefront orders: deduct seller wallet (base cost) before placing the VIP order
                if ($order->seller_id && !$order->wallet_deducted) {
                    $sellerChargeResult = \App\Http\Controllers\Seller\SellerStorefrontController::processSellerOrder($order);
                    if ($sellerChargeResult !== true) {
                        Log::error('Chargily recharge aborted: seller insufficient balance (Free Fire)', [
                            'order_id' => $order->id,
                            'seller_id' => $order->seller_id,
                            'seller_balance' => $order->seller ? $order->seller->wallet_balance : null,
                            'required' => $order->seller_cost,
                        ]);

                        // Include legacy service name in additional_data for compatibility
                        $vipResellerStatus = VipResellerStatus::create([
                            'order_id' => $order->id,
                            'trxid' => null,
                            'data' => null,
                            'zone' => null,
                            'status' => 'error',
                            'note' => 'Insufficient seller wallet balance',
                            'price' => null,
                            'additional_data' => [
                                'required' => $order->seller_cost,
                                'wallet_balance' => $order->seller ? $order->seller->wallet_balance : null,
                                'service' => $packageCode,
                            ],
                        ]);

                        return [
                            'success' => false,
                            'message' => 'Seller has insufficient wallet balance to process this order.',
                        ];
                    }
                }

                // STEP 2: Call provider API to recharge (Free Fire)
                Log::info('Chargily: Calling provider API for Free Fire', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'package_code' => $packageCode,
                    'player_id' => $order->player_id_ff,
                    'seller_id' => $order->seller_id,
                    'wallet_deducted' => $order->wallet_deducted,
                    'seller_cost' => $order->seller_cost,
                    'quantity' => $order->quantity ?? 1,
                ]);

                // Multi-item order support: Submit top-ups for each order_item
                if (config('services.digiflazz.username') || env('DIGIFLAZZ_USERNAME')) {
                    // Atomic multi-quantity submission with proper locking
                    DB::transaction(function () use (&$result, &$order) {
                        // Lock the order to prevent concurrent modifications
                        $orderLocked = Order::where('id', $order->id)->lockForUpdate()->first();
                        if (!$orderLocked) {
                            Log::error('Chargily: Failed to lock order for Digiflazz submission (Free Fire)', ['order_id' => $order->id]);
                            return;
                        }

                        $orderLocked->load('orderItems.diamondPack');
                        
                        // SECURITY: Re-calculate and validate prices before top-up to prevent manipulation
                        $totalOriginalPrice = 0;
                        $totalDiscount = 0;
                        $totalFinalPrice = 0;
                        $priceValidationErrors = [];
                        
                        foreach ($orderLocked->orderItems as $orderItem) {
                            $pack = $orderItem->diamondPack;
                            
                            // Re-calculate prices from current pack data (prevents price manipulation)
                            $unitPriceDzd = $pack->price_dzd ?? ($pack->price * 260);
                            $unitPriceUsd = $pack->price_usd ?? $pack->price;
                            $discountPercentage = $pack->discount_percentage ?? 0;
                            $quantity = max(1, (int)$orderItem->quantity);
                            
                            $subtotalDzd = $unitPriceDzd * $quantity;
                            $discountAmount = ($unitPriceDzd * $discountPercentage / 100) * $quantity;
                            $totalDzd = $subtotalDzd - $discountAmount;
                            
                            // Validate: stored prices should match calculated prices (within 1 DZD tolerance)
                            $storedTotal = (float)$orderItem->total_dzd;
                            $calculatedTotal = (float)$totalDzd;
                            $priceDiff = abs($storedTotal - $calculatedTotal);
                            
                            if ($priceDiff > 1.0) {
                                $priceValidationErrors[] = [
                                    'order_item_id' => $orderItem->id,
                                    'pack_id' => $pack->id,
                                    'pack_name' => $pack->name,
                                    'stored_price' => $storedTotal,
                                    'calculated_price' => $calculatedTotal,
                                    'difference' => $priceDiff
                                ];
                            }
                            
                            // Update order_item with recalculated prices (use current pack prices)
                            $orderItem->unit_price_dzd = $unitPriceDzd;
                            $orderItem->unit_price_usd = $unitPriceUsd;
                            $orderItem->discount_percentage = $discountPercentage;
                            $orderItem->subtotal_dzd = $subtotalDzd;
                            $orderItem->discount_amount_dzd = $discountAmount;
                            $orderItem->total_dzd = $totalDzd;
                            $orderItem->quantity = $quantity; // Ensure quantity is valid
                            $orderItem->save();
                            
                            $totalOriginalPrice += $subtotalDzd;
                            $totalDiscount += $discountAmount;
                            // Note: totalDzd here is per-item total (after pack discount, before coupon)
                        }
                        
                        // If individual item price validation fails, abort and log
                        if (!empty($priceValidationErrors)) {
                            Log::error('Chargily: Price validation failed - potential manipulation detected (Free Fire)', [
                                'order_id' => $orderLocked->id,
                                'order_number' => $orderLocked->order_number,
                                'errors' => $priceValidationErrors,
                                'stored_final_price' => $orderLocked->final_price,
                                'calculated_original_price' => $totalOriginalPrice
                            ]);
                            $result = [
                                'result' => false,
                                'message' => 'Price validation failed. Order prices do not match current pack prices.',
                                'errors' => $priceValidationErrors
                            ];
                            return;
                        }
                        
                        // Apply coupon discount if order has a coupon
                        $orderDiscountAmount = 0;
                        $calculatedFinalPrice = $totalOriginalPrice - $totalDiscount; // After pack discounts
                        
                        if ($orderLocked->coupon_id) {
                            $orderLocked->load('coupon');
                            if ($orderLocked->coupon) {
                                // Re-calculate coupon discount on the original total (before pack discounts)
                                $couponDiscountInfo = $orderLocked->coupon->calculateDiscount($totalOriginalPrice);
                                $orderDiscountAmount = $couponDiscountInfo['discount_amount'];
                                $calculatedFinalPrice = $couponDiscountInfo['final_amount'] - $totalDiscount;
                                $calculatedFinalPrice = max(0, $calculatedFinalPrice);
                                
                                Log::info('Chargily: Coupon discount recalculated (Free Fire)', [
                                    'order_id' => $orderLocked->id,
                                    'coupon_id' => $orderLocked->coupon_id,
                                    'original_total' => $totalOriginalPrice,
                                    'coupon_discount' => $orderDiscountAmount,
                                    'pack_discounts' => $totalDiscount,
                                    'calculated_final_price' => $calculatedFinalPrice
                                ]);
                            }
                        }
                        
                        // Validate final order price (accounting for coupon discount)
                        $storedFinalPrice = (float)($orderLocked->final_price ?? 0);
                        $finalPriceDiff = abs($storedFinalPrice - $calculatedFinalPrice);
                        
                        if ($finalPriceDiff > 1.0) {
                            Log::error('Chargily: Final price validation failed - potential manipulation detected (Free Fire)', [
                                'order_id' => $orderLocked->id,
                                'order_number' => $orderLocked->order_number,
                                'stored_final_price' => $storedFinalPrice,
                                'calculated_final_price' => $calculatedFinalPrice,
                                'difference' => $finalPriceDiff,
                                'has_coupon' => !empty($orderLocked->coupon_id),
                                'coupon_discount' => $orderDiscountAmount
                            ]);
                            $result = [
                                'result' => false,
                                'message' => 'Price validation failed. Final order price does not match calculated price.',
                                'stored_price' => $storedFinalPrice,
                                'calculated_price' => $calculatedFinalPrice
                            ];
                            return;
                        }
                        
                        // Update order with recalculated total prices
                        $orderLocked->original_price = $totalOriginalPrice;
                        $orderLocked->discount_amount = $totalDiscount + $orderDiscountAmount; // Total discount (pack + coupon)
                        $orderLocked->final_price = $calculatedFinalPrice;
                        $orderLocked->save();
                        
                        Log::info('Chargily: Price validation passed, proceeding with top-up (Free Fire)', [
                            'order_id' => $orderLocked->id,
                            'final_price' => $totalFinalPrice,
                            'items_count' => $orderLocked->orderItems->count()
                        ]);
                        
                        $lastResult = ['result' => false, 'message' => 'No provider calls made'];

                        // Submit top-ups for each order_item
                        foreach ($orderLocked->orderItems as $orderItem) {
                            // Check how many DigiflazzStatus records already exist for this item
                            $submitted = $orderItem->digiflazzStatuses()
                                ->where(function ($q) {
                                    $q->whereIn('status', ['Sukses', 'sukses', 'SUCCESS', 'success', 'waiting', 'pending'])
                                      ->orWhere('event', 'create');
                                })->count();

                            $remaining = max(0, $orderItem->quantity - $submitted);

                            // Submit remaining top-ups for this item
                            for ($i = 0; $i < $remaining; $i++) {
                                $refId = 'order-' . $orderLocked->id . '-item-' . $orderItem->id . '-' . Str::random(8);
                                
                                $lastResult = app(\App\Services\DigiflazzService::class)->placeOrderWithRefId(
                                    $orderItem->diamondPack,
                                    $orderLocked,
                                    $refId,
                                    $orderItem->id
                                );
                                
                                Log::info('Chargily: Digiflazz placeOrder attempt (Free Fire)', [
                                    'order_id' => $orderLocked->id,
                                    'order_item_id' => $orderItem->id,
                                    'pack_id' => $orderItem->diamond_pack_id,
                                    'quantity' => $orderItem->quantity,
                                    'remaining' => $remaining - ($i + 1),
                                    'ref_id' => $refId,
                                    'result' => $lastResult
                                ]);
                                
                                // Small delay to ensure DigiflazzStatus record is committed
                                usleep(100000); // 0.1 second
                            }
                        }

                        $result = $lastResult;
                        $order = $orderLocked; // Update order reference
                    });
                } else {
                    $existing = $order->vipResellerStatuses()->count();
                    $remaining = max(0, $required - $existing);
                    $lastResult = ['result' => false, 'message' => 'No provider calls made'];

                    for ($i = 0; $i < $remaining; $i++) {
                        $attempt = $i + 1;
                        $lastResult = $vipReseller->placeFreefireOrder(
                            $packageCode,
                            $order->player_id_ff
                        );

                        Log::info('Chargily: vipReseller placeFreefireOrder attempt', ['order_id' => $order->id, 'order_number' => $order->order_number, 'attempt' => $attempt, 'remaining_after' => $remaining - $attempt, 'result' => $lastResult]);
                    }

                    $result = $lastResult;
                }

                Log::info('Chargily: provider API response (Free Fire)', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'api_result' => $result,
                ]);
                
                $playerId = $order->player_id_ff;
                $zoneId = null; // Free Fire doesn't use zone_id
            }

            // STEP 3: Save response to provider status table
            $apiData = $result['data'] ?? [];
            $apiStatus = $apiData['status'] ?? 'error';

            // Map API status to our enum (note: when using Digiflazz, final status must come from Digiflazz webhook)
            $status = match(strtolower($apiStatus)) {
                'waiting' => 'waiting',
                'success', 'completed', 'paid' => 'success',
                default => 'error',
            };

            if ($result['result'] !== true) {
                $status = 'error';
            }

            $usingDigiflazz = (bool)(config('services.digiflazz.username') || env('DIGIFLAZZ_USERNAME'));

            // Prepare additional data
            $additionalData = [
                'full_response' => $result,
                'balance' => $apiData['balance'] ?? null,
                'message' => $result['message'] ?? null,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_method' => 'chargily',
            ];

            if ($usingDigiflazz) {
                // When using Digiflazz we rely on Digiflazz webhook to drive final order status.
                // Ensure order is set to 'sending' and let Digiflazz webhook update to 'completed' when confirmed.
                if ($order->status !== 'sending') {
                    $order->status = 'sending';
                    $order->save();
                    Log::info('Chargily: Digiflazz used - order set to sending and awaiting Digiflazz webhook', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'provider_status' => $status,
                    ]);
                }

                // Update Telegram to indicate we are waiting for provider confirmation
                if ($order->tlg_message_id) {
                    try {
                        $order->load('diamondPack', 'user', 'seller');
                        $updatedMessage = TelegramService::formatOrderMessage($order);
                        $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '⏳ <b>Order Confirmed - Waiting for provider</b>', $updatedMessage);
                        TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                    } catch (\Exception $e) {
                        Log::warning('Failed to update Telegram message when setting sending (Digiflazz flow)', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                    }
                }

                // DigiflazzService->placeOrder should have already created a DigiflazzStatus record; log if missing
                $digStatus = $order->digiflazzStatuses()->latest()->first();
                if (!$digStatus) {
                    Log::warning('Chargily: Digiflazz used but no DigiflazzStatus found for order', ['order_id' => $order->id, 'order_number' => $order->order_number, 'api_result' => $result]);
                }

            } else {
                // Save to vipreseller_status table (legacy provider behavior)
                $vipData = [
                    'order_id' => $order->id,
                    'trxid' => $apiData['trxid'] ?? null,
                    'data' => $apiData['data'] ?? $playerId,
                    'zone' => $apiData['zone'] ?? $zoneId,
                    // store legacy service name in additional_data instead of a dedicated column
                    'status' => $status,
                    'note' => $apiData['note'] ?? ($result['message'] ?? null),
                    'price' => $apiData['price'] ?? null,
                    'additional_data' => array_merge($additionalData, ['service' => $apiData['service'] ?? $packageCode]),
                ];

                if (!empty($vipData['trxid'])) {
                    $vipResellerStatus = VipResellerStatus::updateOrCreate(['trxid' => $vipData['trxid']], $vipData);
                } else {
                    $vipResellerStatus = VipResellerStatus::create($vipData);
                }

                Log::info('Chargily: provider status saved', [
                    'vipreseller_status_id' => $vipResellerStatus->id,
                    'trxid' => $vipResellerStatus->trxid,
                    'status' => $status,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
            }

            // Update order status based on provider response
            $oldOrderStatus = $order->status;
            
            if ($status === 'waiting') {
                // Provider is processing - ensure order is "sending" (payment done, waiting for topup)
                if ($oldOrderStatus !== 'sending') {
                    $order->status = 'sending';
                    $order->save();
                    Log::info('Chargily: Order status updated to sending (provider waiting)', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'old_status' => $oldOrderStatus,
                        'new_status' => 'sending',
                        'provider_status' => $status,
                    ]);
                }
                
                // Update Telegram message if exists (always update when VIP Reseller status changes)
                if ($order->tlg_message_id) {
                    try {
                        $order->load('diamondPack', 'user', 'vipResellerStatuses', 'seller');
                        $updatedMessage = TelegramService::formatOrderMessage($order);
                        $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '⏳ <b>Order Confirmed - Waiting for provider</b>', $updatedMessage);
                        TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                    } catch (\Exception $e) {
                        Log::error('Failed to update Telegram message', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } elseif ($status === 'success') {
                if ($usingDigiflazz) {
                    // When using Digiflazz, do NOT mark completed here; wait for Digiflazz webhook
                    Log::info('Chargily: Digiflazz reported success but final confirmation must come via webhook; leaving order in sending', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'provider_status' => $status,
                    ]);

                    // Update Telegram message to indicate awaiting final confirmation
                    if ($order->tlg_message_id) {
                        try {
                            $order->load('diamondPack', 'user', 'seller');
                            $updatedMessage = TelegramService::formatOrderMessage($order);
                            $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '⏳ <b>Order Confirmed - Waiting for provider</b>', $updatedMessage);
                            TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                        } catch (\Exception $e) {
                            Log::warning('Failed to update Telegram message (Digiflazz success awaiting webhook)', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                        }
                    }
                } else {
                    // Legacy (non-Digiflazz) provider success - fetch balance and complete order
                    try {
                        $vipReseller = app(VipResellerService::class);
                        $profileResult = $vipReseller->getProfile();

                        if ($profileResult['result'] === true && isset($profileResult['data']['balance'])) {
                            $balance = (string) $profileResult['data']['balance'];
                            $vipResellerStatus->balance = $balance;
                            $vipResellerStatus->save();

                            Log::info('Chargily: Balance fetched and saved for successful order', [
                                'provider_status_id' => $vipResellerStatus->id,
                                'order_id' => $order->id,
                                'balance' => $balance,
                            ]);
                        } else {
                            Log::warning('Chargily: Failed to fetch balance from provider API', [
                                'provider_status_id' => $vipResellerStatus->id,
                                'order_id' => $order->id,
                                'message' => $profileResult['message'] ?? 'Unknown error',
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Chargily: Error fetching balance from provider API', [
                            'vipreseller_status_id' => $vipResellerStatus->id ?? null,
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // Provider success - set order to completed
                    if ($oldOrderStatus !== 'completed') {
                        $order->status = 'completed';
                        $order->save();
                        Log::info('Chargily: Order status updated to completed (provider success)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'old_status' => $oldOrderStatus,
                            'new_status' => 'completed',
                            'provider_status' => $status,
                        ]);
                        // Credit seller profit if applicable and not already paid
                        try {
                            if ($order->seller_id && !$order->seller_profit_paid) {
                                $order->creditSellerProfit();
                            }
                        } catch (\Exception $e) {
                            Log::warning('Chargily: Failed to credit seller profit', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                        }
                    }

                    // Update Telegram message if exists (always update when VIP Reseller status changes)
                    if ($order->tlg_message_id) {
                        try {
                            $order->load('diamondPack', 'user', 'vipResellerStatuses', 'seller');
                            $updatedMessage = TelegramService::formatOrderMessage($order);
                            $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '✅ <b>Order Confirmed & Completed</b>', $updatedMessage);
                            TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                        } catch (\Exception $e) {
                            Log::error('Failed to update Telegram message', [
                                'order_id' => $order->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            } elseif ($status === 'error') {
                // Provider error - ensure order is "sending" (needs attention)
                if ($oldOrderStatus === 'completed') {
                    $order->status = 'sending';
                    $order->save();
                    Log::warning('Chargily: Order status updated to sending (provider error - needs attention)', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'old_status' => $oldOrderStatus,
                        'new_status' => 'sending',
                        'vip_status' => $status,
                    ]);
                }
                
                // Update Telegram message if exists
                if ($order->tlg_message_id) {
                    try {
                        $order->load('diamondPack', 'user', 'vipResellerStatuses', 'seller');
                        $updatedMessage = TelegramService::formatOrderMessage($order);
                        $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '❌ <b>Order Error - provider Failed</b>', $updatedMessage);
                        TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                    } catch (\Exception $e) {
                        Log::error('Failed to update Telegram message', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Resolve a trxid value regardless of provider (Digiflazz or legacy) so logging/returns don't assume $vipResellerStatus exists
            $trxid = $apiData['trxid'] ?? $result['ref_id'] ?? null;

            if ($result['result'] === true) {
                Log::info('Chargily: Recharge successful', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'game_type' => $gameType,
                    'package_code' => $packageCode,
                    'player_id' => $playerId,
                    'zone_id' => $zoneId,
                    'trxid' => $trxid,
                ]);

                return [
                    'success' => true,
                    'message' => 'Recharge processed successfully',
                    'trxid' => $trxid,
                ];
            } else {
                Log::error('Chargily: Recharge failed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'game_type' => $gameType,
                    'package_code' => $packageCode,
                    'player_id' => $playerId,
                    'zone_id' => $zoneId,
                    'trxid' => $trxid,
                    'api_response' => $result,
                ]);

                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Recharge failed',
                    'trxid' => $trxid,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Chargily recharge exception: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Try to save error status
            try {
                $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
                $playerId = $gameType === 'freefire' ? $order->player_id_ff : $order->user_id_ml;
                $zoneId = $gameType === 'freefire' ? null : $order->zone_id_ml;
                
                VipResellerStatus::create([
                    'order_id' => $order->id,
                    'trxid' => null,
                    'data' => $playerId ?? null,
                    'zone' => $zoneId,
                    'service' => $order->diamondPack->code ?? null,
                    'status' => 'error',
                    'note' => 'Exception: ' . $e->getMessage(),
                    'price' => null,
                    'additional_data' => [
                        'exception' => $e->getMessage(),
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'game_type' => $gameType,
                        'payment_method' => 'chargily',
                    ],
                ]);
            } catch (\Exception $saveException) {
                Log::error('Failed to save Chargily error status: ' . $saveException->getMessage());
            }
            
            return [
                'success' => false,
                'message' => 'Error processing recharge: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Handle Flexy form submission
     */
    public function submitFlexy(Request $request)
    {
        // Validate reCAPTCHA
        $recaptchaResponse = $request->input('g-recaptcha-response');
        if (!$recaptchaResponse) {
            return back()->withErrors(['recaptcha' => 'Please complete the reCAPTCHA verification'])->withInput();
        }
        
        // Verify reCAPTCHA with Google (server-side only)
        $secretKey = config('recaptcha.secret_key');
        $verifyUrl = config('recaptcha.verify_url');
        
        try {
            $response = Http::asForm()->post($verifyUrl, [
                'secret' => $secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => $request->ip(),
            ]);
            
            $responseData = $response->json();
            
            if (!isset($responseData['success']) || !$responseData['success']) {
                Log::warning('Flexy submission: reCAPTCHA verification failed', [
                    'ip' => $request->ip(),
                    'recaptcha_errors' => $responseData['error-codes'] ?? [],
                ]);
                return back()->withErrors(['recaptcha' => 'reCAPTCHA verification failed. Please try again.'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Flexy submission: reCAPTCHA verification error', [
                'ip' => $request->ip(),
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['recaptcha' => 'reCAPTCHA verification error. Please try again.'])->withInput();
        }
        
        $request->validate([
            'encrypted_order_id' => 'required|string',
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'notes' => 'nullable|string|max:1000',
        ]);
        
        try {
            // Decrypt the order ID - use generic error message for security
            $orderId = Crypt::decryptString($request->input('encrypted_order_id'));
        } catch (\Exception $e) {
            // Generic error message to prevent information leakage
            Log::warning('Flexy submission: Invalid encrypted order ID', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return back()->withErrors(['encrypted_order_id' => 'Invalid order information'])->withInput();
        }
        
        // Verify order exists
        $order = Order::find($orderId);
        if (!$order) {
            Log::warning('Flexy submission: Order not found', [
                'order_id' => $orderId,
                'ip' => $request->ip(),
            ]);
            return back()->withErrors(['encrypted_order_id' => 'Order not found'])->withInput();
        }
        
        // Verify order belongs to authenticated user (if logged in)
        if (auth()->check() && $order->user_id !== auth()->id()) {
            Log::warning('Flexy submission: Unauthorized order access attempt', [
                'order_id' => $order->id,
                'order_user_id' => $order->user_id,
                'auth_user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);
            return back()->withErrors(['encrypted_order_id' => 'Unauthorized access'])->withInput();
        }
        
        // Verify order is in correct status (should be pending_flexy)
        if ($order->status !== 'pending_flexy') {
            Log::warning('Flexy submission: Order in wrong status', [
                'order_id' => $order->id,
                'order_status' => $order->status,
                'ip' => $request->ip(),
            ]);
            return back()->withErrors(['encrypted_order_id' => 'Order cannot be processed'])->withInput();
        }
        
        // Enhanced file upload validation
        $file = $request->file('receipt_image');
        
        // Verify file was actually uploaded
        if (!$file || !$file->isValid()) {
            return back()->withErrors(['receipt_image' => 'Invalid file upload'])->withInput();
        }
        
        // Check MIME type (additional security layer)
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimes)) {
            Log::warning('Flexy submission: Invalid MIME type', [
                'order_id' => $order->id,
                'mime_type' => $mimeType,
                'ip' => $request->ip(),
            ]);
            return back()->withErrors(['receipt_image' => 'Invalid file type'])->withInput();
        }
        
        // Check file extension matches MIME type
        $extension = strtolower($file->getClientOriginalExtension());
        $extensionMimeMap = [
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];
        if (!isset($extensionMimeMap[$extension]) || $extensionMimeMap[$extension] !== $mimeType) {
            Log::warning('Flexy submission: File extension mismatch', [
                'order_id' => $order->id,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'ip' => $request->ip(),
            ]);
            return back()->withErrors(['receipt_image' => 'File type mismatch'])->withInput();
        }
        
        // Check file size (in bytes)
        $maxSize = 5120 * 1024; // 5MB in bytes
        if ($file->getSize() > $maxSize) {
            return back()->withErrors(['receipt_image' => 'File size exceeds 5MB limit'])->withInput();
        }
        
        // Sanitize filename to prevent directory traversal
        $originalName = $file->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $sanitizedName = substr($sanitizedName, 0, 255); // Limit filename length
        
        // Create directory if it doesn't exist (more restrictive permissions)
        $storagePath = public_path('storage/flexy_receipts');
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0750, true);
        }
        
        // Generate unique filename
        $filename = $order->id . '_' . time() . '_' . $sanitizedName;
        
        // Move file to public/storage/flexy_receipts/
        $file->move($storagePath, $filename);
        
        // Store relative path for database (storage/flexy_receipts/filename)
        $imagePath = 'storage/flexy_receipts/' . $filename;
        
        // Create or update Flexy record
        $flexy = Flexy::create([
            'receipt_image' => $imagePath,
            'diamond_pack_id' => $order->diamond_pack_id,
            'status' => 'pending',
        ]);
        
        // Link order to flexy and update status to pending_confirmation
        $order->flexy_id = $flexy->id;
        $oldStatus = $order->status;
        $order->status = 'pending_confirmation';
        // Sanitize notes to prevent XSS (strip HTML tags, limit length)
        $notes = $request->input('notes');
        if ($notes) {
            $notes = strip_tags($notes); // Remove HTML tags
            $notes = substr($notes, 0, 1000); // Enforce max length
        }
        $order->notes = $notes;
        $order->save();
        
        // Send Telegram notification for status change to pending_confirmation
        if ($oldStatus === 'pending_flexy' && $order->status === 'pending_confirmation') {
            try {
                $order->load('diamondPack', 'user');
                $message = TelegramService::formatOrderMessage($order);
                // Add confirm button for pending_confirmation orders
                $messageId = TelegramService::sendMessage($message, true);
                if ($messageId) {
                    $order->tlg_message_id = $messageId;
                    $order->save();
                }
            } catch (\Exception $e) {
                Log::error('Telegram notification failed for status change', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Log successful submission
        Log::info('Flexy receipt submitted successfully', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'flexy_id' => $flexy->id,
            'file_path' => $imagePath,
            'ip' => $request->ip(),
            'user_id' => auth()->id() ?? 'guest',
        ]);
        
        // Encrypt order ID for redirect
        $encryptedOrderId = Crypt::encryptString($order->id);
        
        // Clear cart
        // Redirect to success page with encrypted order_id
        return redirect()->route('flexy-success', ['order_id' => $encryptedOrderId])
            ->with('clear_cart', true);
    }
    
    /**
     * Show Flexy success page
     */
    public function flexySuccess(Request $request)
    {
        $encryptedOrderId = $request->query('order_id');
        
        if (!$encryptedOrderId) {
            return redirect()->route('dashboard.orders');
        }
        
        try {
            // Decrypt the order ID (just for validation, we don't need to show it)
            $orderId = Crypt::decryptString($encryptedOrderId);
            $order = Order::find($orderId);
            
            if (!$order) {
                return redirect()->route('dashboard.orders');
            }
        } catch (\Exception $e) {
            return redirect()->route('dashboard.orders');
        }
        
        return view('pages.flexy-success', [
            'encrypted_order_id' => $encryptedOrderId,
        ]);
    }
    
    /**
     * Delete an order by encrypted ID
     */
    public function deleteOrder(Request $request)
    {
        $request->validate([
            'encrypted_order_id' => 'required|string',
        ]);
        
        try {
            // Decrypt the order ID
            $orderId = Crypt::decryptString($request->encrypted_order_id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid order ID'
            ], 400);
        }
        
        $order = Order::with('diamondPack')->find($orderId);
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        // Get order data before deleting (to restore to cart)
        $orderData = [
            'pack_id' => $order->diamond_pack_id,
            'user_id' => $order->user_id_ml,
            'zone_id' => $order->zone_id_ml,
            'player_id_ff' => $order->player_id_ff,
            'player_id_pubg' => $order->player_id_pubg,
            'player_id_hok' => $order->player_id_hok,
            'user_id_bs' => $order->user_id_bs,
            'server_bs' => $order->server_bs,
        ];
        
        // Log before deletion
        Log::info('Deleting order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
        ]);
        
        // Delete the order from database
        $order->delete();
        
        // Log after deletion
        Log::info('Order deleted successfully', [
            'order_id' => $orderId,
            'encrypted_order_id_received' => $request->encrypted_order_id,
        ]);
        
        // Return multiple formats of the encrypted ID to help frontend match
        $encryptedId = $request->encrypted_order_id;
        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully',
            'encrypted_order_id' => $encryptedId, // Return the exact encrypted ID that was deleted
            'encrypted_order_id_variations' => [
                'original' => $encryptedId,
                'url_encoded' => urlencode($encryptedId),
                'url_decoded' => urldecode($encryptedId),
            ],
            'order_id' => $orderId, // Also return the decrypted order ID for reference
            'cart_item' => $orderData // Return order data to restore to cart
        ]);
    }
    
    /**
     * Show crypto payment form page (order confirmation before cryptocurrency payment)
     */
    public function cryptoForm($encryptedOrderId)
    {
        try {
            // Decrypt the order ID
            $orderId = Crypt::decryptString($encryptedOrderId);
        } catch (\Exception $e) {
            return redirect()->route('select-payment')->with('error', 'Invalid order ID');
        }
        
        $order = Order::with('diamondPack')->find($orderId);
        
        if (!$order) {
            return redirect()->route('select-payment')->with('error', 'Order not found');
        }
        
        // Calculate order amount
        $unitPrice = $order->diamondPack->price;
        $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
        $discountAmount = ($unitPrice * $discountPercentage) / 100;
        $totalAmount = $unitPrice - $discountAmount;
        
        return view('pages.crypto-form', [
            'order' => $order,
            'encrypted_order_id' => $encryptedOrderId,
            'total_amount' => $totalAmount,
            'unit_price' => $unitPrice,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
        ]);
    }
    
    /**
     * Show MixPay crypto payment page
     */
    public function cryptoPayment($encryptedOrderId)
    {
        try {
            // Decrypt the order ID
            $orderId = Crypt::decryptString($encryptedOrderId);
        } catch (\Exception $e) {
            return redirect()->route('select-payment')->with('error', 'Invalid order ID');
        }
        
        $order = Order::with('diamondPack')->find($orderId);
        
        if (!$order) {
            return redirect()->route('select-payment')->with('error', 'Order not found');
        }
        
        // Calculate order amount
        $unitPrice = $order->diamondPack->price;
        $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
        $discountAmount = ($unitPrice * $discountPercentage) / 100;
        $totalAmount = $unitPrice - $discountAmount;
        
        // Initialize MixPay service
        $mixPayService = new MixPayService();
        $mixPayConfigured = $mixPayService->hasCredentials();
        
        if (!$mixPayConfigured) {
            return redirect()->route('select-payment')->with('error', 'Cryptocurrency payment is not configured. Please contact support.');
        }
        
        // Prepare order data for MixPay
        // orderId must be 6-36 chars, unique, containing only letters, numbers, dashes and underscores
        $orderIdForMixPay = preg_replace('/[^a-zA-Z0-9_-]/', '_', $order->order_number) . '_' . time();
        // Ensure it's between 6-36 characters
        if (strlen($orderIdForMixPay) > 36) {
            $orderIdForMixPay = substr($orderIdForMixPay, 0, 36);
        }
        if (strlen($orderIdForMixPay) < 6) {
            $orderIdForMixPay = str_pad($orderIdForMixPay, 6, '0', STR_PAD_RIGHT);
        }
        
        // Check if we're on localhost/testing (MixPay requires HTTPS for callback)
        $isLocalhost = in_array(config('app.env'), ['local', 'testing']) || 
                       str_contains(request()->getHost(), 'localhost') ||
                       str_contains(request()->getHost(), '127.0.0.1');
        
        $orderData = [
            'order_id' => $orderIdForMixPay,
            'quote_amount' => number_format($totalAmount, 2, '.', ''), // Amount in USD
            'quote_asset_id' => 'usd', // Quote currency is USD
            'settlement_asset_id' => '4d8c508b-91c5-375b-92b0-ee702ed2dac5', // USDT ERC20
            'payment_asset_id' => '4d8c508b-91c5-375b-92b0-ee702ed2dac5', // USDT ERC20 - restrict to only this
            'return_to' => route('crypto-payment-success', ['encrypted_order_id' => $encryptedOrderId]),
            'failed_return_to' => route('select-payment'),
            'remark' => 'DiasZone - Payment Crypto: ' . $order->diamondPack->diamonds . ' Diamonds + ' . $order->diamondPack->bonus_diamonds . ' Bonus',
        ];
        
        // Only set callback URL if not on localhost (MixPay requires HTTPS)
        if (!$isLocalhost) {
            $orderData['callback_url'] = route('mixpay.webhook');
        }
        
        // Create MixPay payment
        $paymentResponse = $mixPayService->createOneTimePayment($orderData);
        
        if (!$paymentResponse['success']) {
            Log::error('MixPay Payment Creation Failed', [
                'order_id' => $orderId,
                'error' => $paymentResponse['error'] ?? 'Unknown error',
                'response' => $paymentResponse['response_data'] ?? [],
            ]);
            
            return redirect()->route('select-payment')->with('error', 'Failed to create payment. Please try again or contact support.');
        }
        
        // Store payment code in order for reference
        $order->nowpayments_payment_id = $paymentResponse['data']['code'] ?? null;
        $order->status = 'pending_cryptopay';
        $order->save();
        
        // Redirect to MixPay payment URL
        $paymentUrl = $paymentResponse['data']['payment_url'] ?? null;
        
        if ($paymentUrl) {
            return redirect($paymentUrl);
        }
        
        return redirect()->route('select-payment')->with('error', 'Payment URL not generated. Please try again.');
    }
    
    /**
     * Handle cryptocurrency payment success callback
     */
    public function cryptoPaymentSuccess($encryptedOrderId)
    {
        try {
            $orderId = Crypt::decryptString($encryptedOrderId);
            $order = Order::find($orderId);
            
            if (!$order) {
                return redirect()->route('dashboard.orders')->with('error', 'Order not found');
            }
            
            // Update order status to completed
            $order->status = 'completed';
            $order->save();
            
            return redirect()->route('dashboard.orders')->with('success', 'Payment successful! Your order has been processed.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard.orders')->with('error', 'Invalid order ID');
        }
    }
    
    /**
     * API endpoint to check crypto payment status
     */
    public function checkCryptoPayment(Request $request)
    {
        $request->validate([
            'encrypted_order_id' => 'required|string',
        ]);
        
        try {
            $orderId = Crypt::decryptString($request->input('encrypted_order_id'));
            $order = Order::find($orderId);
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'error' => 'Order not found',
                ], 404);
            }
            
            // Check if order has a NOWPayments payment_id stored
            if (!$order->nowpayments_payment_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Payment ID not found for this order',
                    'paid' => false,
                    'status' => 'PENDING',
                ]);
            }
            
            // NOWPayments API key (will be set from env or static later)
            $nowPaymentsApiKey = null; // Will be set from env or static later
            $nowPaymentsEndpoint = 'https://api.nowpayments.io/v1/';
            
            // Query NOWPayments for payment status using the stored payment_id
            $nowPaymentsService = new NowPaymentsService($nowPaymentsApiKey, $nowPaymentsEndpoint);
            $paymentResponse = $nowPaymentsService->getPaymentStatus($order->nowpayments_payment_id);
            
            if ($paymentResponse['success']) {
                $paymentData = $paymentResponse['data'];
                $status = $paymentData['payment_status'] ?? 'waiting';
                
                // NOWPayments statuses: waiting, confirming, confirmed, sending, partially_paid, finished, failed, refunded, expired
                if ($status === 'finished' || $status === 'confirmed') {
                    // Update order status
                    $order->status = 'completed';
                    $order->save();
                    
                    return response()->json([
                        'success' => true,
                        'paid' => true,
                        'status' => $status,
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'paid' => false,
                'status' => 'PENDING',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test NOWPayments credentials
     * Route: /test/nowpayments
     */
    public function testNowPaymentsCredentials()
    {
        // API key will be set from env or static later
        $apiKey = null; // Will be set from env or static later
        $endpoint = 'https://api.nowpayments.io/v1/';
        
        $nowPaymentsService = new NowPaymentsService($apiKey, $endpoint);
        $testResult = $nowPaymentsService->testConnection();
        
        return response()->json($testResult, $testResult['success'] ? 200 : 400);
    }
    
    /**
     * Test NOWPayments payment creation with $10
     * Route: /test/nowpayments/payment
     */
    public function testNowPaymentsPayment(Request $request)
    {
        // Check if this is an AJAX request for JSON, or regular request for HTML dialog
        if ($request->wantsJson() || $request->ajax()) {
            // Return JSON for AJAX requests
            return $this->testNowPaymentsPaymentJson();
        }
        
        // Return HTML page with dialog
        return view('pages.crypto-payment-dialog');
    }
    
    /**
     * Test NOWPayments payment creation (JSON response)
     */
    private function testNowPaymentsPaymentJson()
    {
        // API key will be set from env or static later
        $apiKey = null; // Will be set from env or static later
        $endpoint = 'https://api.nowpayments.io/v1/';
        
        $nowPaymentsService = new NowPaymentsService($apiKey, $endpoint);
        
        // Check if credentials are configured
        if (!$nowPaymentsService->hasCredentials()) {
            return response()->json([
                'success' => false,
                'error' => 'NOWPayments API key is not configured',
                'message' => 'Add NOWPAYMENTS_API_KEY to your .env file',
                'test_payment_data' => [
                    'price_amount' => '10.00',
                    'price_currency' => 'usd',
                    'pay_currency' => 'usdt',
                    'order_id' => 'TEST_' . time(),
                    'order_description' => 'Test Payment - $10',
                ],
            ], 400);
        }
        
        // First, get available currencies to see what's supported
        $currenciesResponse = $nowPaymentsService->getAvailableCurrencies();
        $availableCurrencies = [];
        if ($currenciesResponse['success']) {
            $availableCurrencies = $currenciesResponse['data']['currencies'] ?? [];
        }
        
        // Create a test payment for $10
        // Use usdttrc20 (USDT on TRC20) as default - it's usually the cheapest option
        // You can change this to any currency from the available_currencies list
        $orderData = [
            'order_id' => 'TEST_' . time(),
            'amount' => '10.00',
            'price_currency' => 'usd',
            'goods_name' => 'Test Payment - $10',
            'pay_currency' => 'usdttrc20', // USDT on TRC20 network (low fees)
            'return_url' => route('home'),
            'cancel_url' => route('home'),
            'ipn_callback_url' => route('nowpayments.webhook'),
        ];
        
        $paymentResponse = $nowPaymentsService->createInvoice($orderData);
        
        // If invoice creation fails due to currency issue, try alternative currencies
        if (!$paymentResponse['success'] && 
            (str_contains($paymentResponse['error'], 'estimate') || 
             str_contains($paymentResponse['error'], 'currency'))) {
            // Try alternative USDT options
            $alternativeCurrencies = ['usdterc20', 'usdtbsc', 'usdtmatic', 'usdtsol'];
            foreach ($alternativeCurrencies as $altCurrency) {
                if (in_array($altCurrency, $availableCurrencies)) {
                    $orderData['pay_currency'] = $altCurrency;
                    $paymentResponse = $nowPaymentsService->createInvoice($orderData);
                    if ($paymentResponse['success']) {
                        break;
                    }
                }
            }
        }
        
        if ($paymentResponse['success']) {
            $paymentData = $paymentResponse['data'];
            
            $response = [
                'success' => true,
                'message' => 'Test payment created successfully!',
                'payment' => [
                    'payment_id' => $paymentData['payment_id'] ?? null,
                    'payment_status' => $paymentData['payment_status'] ?? null,
                    'pay_address' => $paymentData['pay_address'] ?? null,
                    'pay_amount' => $paymentData['pay_amount'] ?? null,
                    'pay_currency' => $paymentData['pay_currency'] ?? null,
                    'price_amount' => $paymentData['price_amount'] ?? null,
                    'price_currency' => $paymentData['price_currency'] ?? null,
                    'amount_received' => $paymentData['amount_received'] ?? 0,
                    'invoice_url' => $paymentData['invoice_url'] ?? null,
                    'payment_url' => $paymentData['invoice_url'] ?? $paymentData['payment_url'] ?? null,
                ],
                'available_currencies' => $availableCurrencies,
                'full_response' => $paymentData,
            ];
            
            // Add direct properties for easier access in dialog
            $response['pay_address'] = $paymentData['pay_address'] ?? null;
            $response['pay_amount'] = $paymentData['pay_amount'] ?? null;
            $response['pay_currency'] = $paymentData['pay_currency'] ?? null;
            $response['price_amount'] = $paymentData['price_amount'] ?? null;
            $response['price_currency'] = $paymentData['price_currency'] ?? null;
            $response['payment_id'] = $paymentData['invoice_id'] ?? $paymentData['payment_id'] ?? null;
            // Invoice endpoint returns invoice_url directly - use it as payment_url
            $response['invoice_url'] = $paymentData['invoice_url'] ?? null;
            $response['payment_url'] = $paymentData['invoice_url'] ?? $paymentData['payment_url'] ?? null;
            
            return response()->json($response, 200);
        } else {
            return response()->json([
                'success' => false,
                'error' => $paymentResponse['error'] ?? 'Failed to create payment',
                'response_data' => $paymentResponse['response_data'] ?? null,
                'test_request' => $orderData,
                'available_currencies' => $availableCurrencies,
                'suggestion' => 'Try removing pay_currency from the request to let NOWPayments suggest available payment options, or check available currencies first.',
            ], 400);
        }
    }
    
    /**
     * Test NOWPayments payment status check
     * Route: /test/nowpayments/status/{payment_id}
     */
    public function testNowPaymentsStatus($paymentId)
    {
        // API key will be set from env or static later
        $apiKey = null; // Will be set from env or static later
        $endpoint = 'https://api.nowpayments.io/v1/';
        
        $nowPaymentsService = new NowPaymentsService($apiKey, $endpoint);
        
        // Check if credentials are configured
        if (!$nowPaymentsService->hasCredentials()) {
            return response()->json([
                'success' => false,
                'error' => 'NOWPayments API key is not configured',
                'message' => 'Add NOWPAYMENTS_API_KEY to your .env file',
            ], 400);
        }
        
        // Get payment status
        $statusResponse = $nowPaymentsService->getPaymentStatus($paymentId);
        
        if ($statusResponse['success']) {
            $paymentData = $statusResponse['data'];
            
            return response()->json([
                'success' => true,
                'payment_id' => $paymentId,
                'payment_status' => $paymentData['payment_status'] ?? 'unknown',
                'payment_data' => $paymentData,
                'status_meaning' => [
                    'waiting' => 'Waiting for payment',
                    'confirming' => 'Payment is being confirmed',
                    'confirmed' => 'Payment confirmed',
                    'sending' => 'Sending payment',
                    'partially_paid' => 'Partially paid',
                    'finished' => 'Payment completed',
                    'failed' => 'Payment failed',
                    'refunded' => 'Payment refunded',
                    'expired' => 'Payment expired',
                ],
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'error' => $statusResponse['error'] ?? 'Failed to get payment status',
                'payment_id' => $paymentId,
            ], 400);
        }
    }
    
    /**
     * Handle NOWPayments IPN (Instant Payment Notification)
     * This is called by NOWPayments when payment status changes
     * Note: IPN is optional - we primarily use API polling for status checks
     */
    public function nowPaymentsWebhook(Request $request)
    {
        // Handle NOWPayments IPN (Instant Payment Notification)
        // This will be called by NOWPayments when payment status changes
        
        $paymentData = $request->all();
        
        // Log IPN for debugging
        Log::info('NOWPayments IPN Received', $paymentData);
        
        // Extract payment_id from IPN data
        $paymentId = $paymentData['payment_id'] ?? null;
        
        if ($paymentId) {
            // Find order by payment_id
            $order = Order::where('nowpayments_payment_id', $paymentId)->first();
            
            if ($order) {
                // Update order status based on payment status
                $paymentStatus = $paymentData['payment_status'] ?? 'waiting';
                
                // NOWPayments statuses: waiting, confirming, confirmed, sending, partially_paid, finished, failed, refunded, expired
                if ($paymentStatus === 'finished' || $paymentStatus === 'confirmed') {
                    $order->status = 'completed';
                    $order->save();
                } elseif ($paymentStatus === 'failed' || $paymentStatus === 'expired') {
                    // Keep as pending or mark as failed based on your business logic
                    // $order->status = 'failed';
                }
            }
        }
        
        return response()->json(['status' => 'ok'], 200);
    }
    
    /**
     * Handle MixPay payment callback
     * This is called by MixPay when payment status changes
     * See: https://mixpay.me/developers/api/payments/payment-callback
     */
    public function mixPayWebhook(Request $request)
    {
        // Handle MixPay payment callback
        $paymentData = $request->all();
        
        // Log callback for debugging
        Log::info('MixPay Webhook Received', $paymentData);
        
        // Extract orderId from callback data
        $orderId = $paymentData['orderId'] ?? null;
        
        if ($orderId) {
            // Find order by orderId (MixPay uses orderId, not payment_id)
            // We stored the MixPay code in nowpayments_payment_id field
            // But we need to match by orderId which is stored in order_number
            // MixPay orderId format: order_number_timestamp
            $order = Order::where('order_number', 'like', explode('_', $orderId)[0] . '%')
                          ->orWhere('nowpayments_payment_id', $paymentData['code'] ?? null)
                          ->first();
            
            if ($order) {
                // Update order status based on payment status
                $paymentStatus = $paymentData['status'] ?? 'pending';
                
                // MixPay statuses: pending, paid, success, failed
                // See: https://mixpay.me/developers/api/payments/payment-callback
                if ($paymentStatus === 'success' || $paymentStatus === 'paid') {
                    $order->status = 'completed';
                    $order->save();
                } elseif ($paymentStatus === 'failed') {
                    // Keep as pending or mark as failed based on your business logic
                    // $order->status = 'failed';
                }
            }
        }
        
        return response()->json(['status' => 'ok'], 200);
    }
    
    /**
     * Test Chargily Pay credentials
     */
    public function testChargilyCredentials()
    {
        $key = env('CHARGILY_EPAY_KEY') ?? config('laravel-chargily-epay.key');
        $secret = env('CHARGILY_EPAY_SECRET') ?? config('laravel-chargily-epay.secret');
        $backUrl = env('CHARGILY_EPAY_BACK_URL') ?? config('laravel-chargily-epay.back_url');
        $webhookUrl = env('CHARGILY_EPAY_WEBHOOK_URL') ?? config('laravel-chargily-epay.webhook_url');
        
        return response()->json([
            'credentials_configured' => !empty($key) && !empty($secret),
            'key_exists' => !empty($key),
            'secret_exists' => !empty($secret),
            'back_url' => $backUrl,
            'webhook_url' => $webhookUrl,
            'key_preview' => $key ? (substr($key, 0, 10) . '...' . substr($key, -5)) : 'NOT SET',
            'secret_preview' => $secret ? (substr($secret, 0, 10) . '...' . substr($secret, -5)) : 'NOT SET',
            'note' => 'If credentials are set but you get 401, the API key/secret might be incorrect. Verify them in your Chargily Pay dashboard.',
        ]);
    }
}
