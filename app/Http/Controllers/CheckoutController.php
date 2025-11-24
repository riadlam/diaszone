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
use TheHocineSaad\LaravelChargilyEPay\Models\Epay_Invoice;
use TheHocineSaad\LaravelChargilyEPay\Epay_Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

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
                'cart_items' => 'required|array|min:1|max:1', // Single item limit enforced
                'cart_items.*.pack_id' => 'required|exists:diamond_packs,id',
                'cart_items.*.user_id' => 'nullable|string',
                'cart_items.*.zone_id' => 'nullable|string',
                'cart_items.*.player_id' => 'nullable|string',
                'cart_items.*.player_id_ff' => 'nullable|string',
                'cart_items.*.player_id_pubg' => 'nullable|string',
                'cart_items.*.player_id_hok' => 'nullable|string',
                'cart_items.*.user_id_bs' => 'nullable|string',
                'cart_items.*.server_bs' => 'nullable|string',
                'cart_items.*.server' => 'nullable|string',
                'payment_method' => 'nullable|string|in:flexy,bmccp,cryptocurrency',
            ]);
            
            $cartItems = $request->input('cart_items');
            $paymentMethod = $request->input('payment_method'); // flexy, bmccp, or cryptocurrency
            $userId = Auth::check() ? Auth::id() : null;
            $createdOrders = [];
            
            // Determine status based on payment method
            $orderStatus = 'pending'; // Default
            if ($paymentMethod === 'flexy') {
                $orderStatus = 'pending_flexy';
            } elseif ($paymentMethod === 'bmccp') {
                $orderStatus = 'pending_bmccp';
            } elseif ($paymentMethod === 'cryptocurrency') {
                $orderStatus = 'pending_cryptopay';
            }
            
            foreach ($cartItems as $item) {
                // Determine which player_id field to use based on game type
                $pack = \App\Models\DiamondPack::find($item['pack_id']);
                
                if (!$pack) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pack not found'
                    ], 404);
                }
                
                $playerIdFf = null;
                $playerIdPubg = null;
                $playerIdHok = null;
                $userIdBs = null;
                $serverBs = null;
                
                // Validate that required fields are provided based on game type
                if ($pack->game_type === 'freefire') {
                    $playerIdFf = $item['player_id_ff'] ?? $item['player_id'] ?? null;
                    if (empty($playerIdFf)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Player ID is required for Free Fire'
                        ], 422);
                    }
                } elseif ($pack->game_type === 'pubgmobile') {
                    $playerIdPubg = $item['player_id_pubg'] ?? $item['player_id'] ?? null;
                    if (empty($playerIdPubg)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Player ID is required for PUBG Mobile'
                        ], 422);
                    }
                } elseif ($pack->game_type === 'honorofkings') {
                    $playerIdHok = $item['player_id_hok'] ?? $item['player_id'] ?? null;
                    if (empty($playerIdHok)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Player ID is required for Honor of Kings'
                        ], 422);
                    }
                } elseif ($pack->game_type === 'bloodstrike') {
                    $userIdBs = $item['user_id_bs'] ?? $item['user_id'] ?? null;
                    $serverBs = $item['server_bs'] ?? $item['server'] ?? null;
                    if (empty($userIdBs) || empty($serverBs)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'User ID and Server are required for Blood Strike'
                        ], 422);
                    }
                } else {
                    // Mobile Legends - default
                    if (empty($item['user_id']) || empty($item['zone_id'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'User ID and Zone ID are required for Mobile Legends'
                        ], 422);
                    }
                }
                
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'user_id' => $userId,
                    'diamond_pack_id' => $item['pack_id'],
                    'status' => $orderStatus, // Set status based on payment method
                    'user_id_ml' => ($pack->game_type === 'mobilelegends') ? ($item['user_id'] ?? null) : null,
                    'zone_id_ml' => ($pack->game_type === 'mobilelegends') ? ($item['zone_id'] ?? null) : null,
                    'player_id_ff' => $playerIdFf,
                    'player_id_pubg' => $playerIdPubg,
                    'player_id_hok' => $playerIdHok,
                    'user_id_bs' => $userIdBs,
                    'server_bs' => $serverBs,
                ]);
                
                $createdOrders[] = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                ];
            }
        
            // Encrypt order IDs before returning
            $encryptedOrders = array_map(function($order) {
                return [
                    'id' => $order['id'],
                    'order_number' => $order['order_number'],
                    'encrypted_id' => Crypt::encryptString($order['id']),
                ];
            }, $createdOrders);
            
            return response()->json([
                'success' => true,
                'orders' => $encryptedOrders,
            ]);
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
            $vipResellerService = new VipResellerService();
            $result = $vipResellerService->checkNickname($request->user_id, $request->zone_id);

            // Return the API response directly: {"result": true/false, "data": "nickname", "message": "..."}
            return response()->json([
                'result' => $result['result'] ?? false,
                'data' => $result['data'] ?? null,
                'message' => $result['message'] ?? 'Validation failed',
            ], $result['result'] === true ? 200 : 400);
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
        
        $order = Order::with('diamondPack')->find($orderId);
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        // Initialize Chargily Pay v2 service
        $chargilyService = new ChargilyPayV2Service();
        
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
            // Calculate amount in DZD: Use price_dzd directly from the diamond pack
            $priceDzd = (float) ($order->diamondPack->price_dzd ?? 0);
            
            // Fallback: if price_dzd is not set, calculate from price_usd or price
            if (!$priceDzd || $priceDzd <= 0) {
                $priceUsd = (float) ($order->diamondPack->price_usd ?? $order->diamondPack->price ?? 0);
                $priceDzd = $priceUsd * 260; // Convert USD to DZD as fallback
                Log::warning('Chargily payment: price_dzd not found, using fallback calculation', [
                    'order_id' => $order->id,
                    'diamond_pack_id' => $order->diamond_pack_id,
                    'price_usd' => $priceUsd,
                    'calculated_price_dzd' => $priceDzd,
                ]);
            }
            
            // Apply discount
            $discountPercentage = (float) ($order->diamondPack->discount_percentage ?? 0);
            $discountAmount = ($priceDzd * $discountPercentage) / 100;
            $amount = $priceDzd - $discountAmount;
            
            // Log the amount calculation for debugging
            Log::info('Chargily payment amount calculation', [
                'order_id' => $order->id,
                'diamond_pack_id' => $order->diamond_pack_id,
                'price_dzd' => $priceDzd,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
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
            $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
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
            
            $packName = $order->diamondPack->name ?? ($order->diamondPack->diamonds . ' ' . $currencyText);
            $description = "DiasZone - {$gameName} - {$packName}";
            
            // Create invoice in bmccp table first
            $bmccp = \App\Models\Bmccp::create([
                'diamond_pack_id' => $order->diamond_pack_id,
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
                'success_url' => route('baridimob-form', ['encrypted_order_id' => $request->encrypted_order_id]) . '?success=1',
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
            $is401Error = str_contains($errorMessage, '401') || str_contains($errorMessage, 'Unauthorized');
            $configKey = config('laravel-chargily-epay.key');
            $isTestKey = $configKey && str_starts_with($configKey, 'test_');
            
            \Log::error('Baridimob payment error: ' . $errorMessage, [
                'trace' => $e->getTraceAsString(),
                'order_id' => $orderId,
                'is_401_error' => $is401Error,
                'is_test_key' => $isTestKey,
            ]);
            
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
                    'error_details' => '401 Unauthorized - Invalid API credentials'
                ], 401);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $errorMessage
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
            $chargilyService = new ChargilyPayV2Service();
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
                
                if ($chargilyStatus && $chargilyStatus->order_id) {
                    $order = Order::find($chargilyStatus->order_id);
                }
                
                // If not found, try to find by bmccp invoice_number
                if (!$order) {
                    $bmccp = \App\Models\Bmccp::where('invoice_number', $checkoutId)->first();
                    if ($bmccp) {
                        $order = Order::where('bmccp_id', $bmccp->id)->first();
                    }
                }
            }
            
            // STEP 4: Save/Update chargily_status
            if ($chargilyStatus) {
                // Update existing status
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
                Log::warning('Chargily Pay v2 webhook: Order not found', [
                    'checkout_id' => $checkoutId,
                    'event_type' => $eventType,
                ]);
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
            
            // Only process Mobile Legends orders
            $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
            
            if ($gameType !== 'mobilelegends') {
                Log::info('Chargily recharge skipped: Not a Mobile Legends order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'game_type' => $gameType,
                ]);
                return [
                    'success' => false,
                    'message' => 'Recharge only supported for Mobile Legends orders',
                ];
            }

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

            // STEP 1: Validate nickname BEFORE processing recharge
            Log::info('Chargily: Validating nickname before recharge', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id_ml' => $order->user_id_ml,
                'zone_id_ml' => $order->zone_id_ml,
            ]);

            $vipReseller = new VipResellerService();
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

            // Get package code from diamond_packs
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

            // STEP 2: Call VIP Reseller API to recharge
            $result = $vipReseller->placeOrder(
                $packageCode,
                $order->user_id_ml,
                $order->zone_id_ml
            );

            // STEP 3: Save response to vipreseller_status table
            $apiData = $result['data'] ?? [];
            $apiStatus = $apiData['status'] ?? 'error';
            
            // Map API status to our enum
            $status = match(strtolower($apiStatus)) {
                'waiting' => 'waiting',
                'success', 'completed', 'paid' => 'success',
                default => 'error',
            };
            
            if ($result['result'] !== true) {
                $status = 'error';
            }

            // Prepare additional data
            $additionalData = [
                'full_response' => $result,
                'balance' => $apiData['balance'] ?? null,
                'message' => $result['message'] ?? null,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_method' => 'chargily',
            ];

            // Save to vipreseller_status table
            $vipResellerStatus = VipResellerStatus::create([
                'order_id' => $order->id,
                'trxid' => $apiData['trxid'] ?? null,
                'data' => $apiData['data'] ?? $order->user_id_ml,
                'zone' => $apiData['zone'] ?? $order->zone_id_ml,
                'service' => $apiData['service'] ?? $packageCode,
                'status' => $status,
                'note' => $apiData['note'] ?? ($result['message'] ?? null),
                'price' => $apiData['price'] ?? null,
                'additional_data' => $additionalData,
            ]);

            Log::info('Chargily: VIP Reseller status saved', [
                'vipreseller_status_id' => $vipResellerStatus->id,
                'trxid' => $vipResellerStatus->trxid,
                'status' => $status,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            // Update order status based on VIP Reseller response
            $oldOrderStatus = $order->status;
            
            if ($status === 'waiting') {
                // VIP Reseller is processing - ensure order is "sending" (payment done, waiting for topup)
                if ($oldOrderStatus !== 'sending') {
                    $order->status = 'sending';
                    $order->save();
                    Log::info('Chargily: Order status updated to sending (VIP Reseller waiting)', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'old_status' => $oldOrderStatus,
                        'new_status' => 'sending',
                        'vip_status' => $status,
                    ]);
                }
            } elseif ($status === 'success') {
                // VIP Reseller success - set order to completed
                if ($oldOrderStatus !== 'completed') {
                    $order->status = 'completed';
                    $order->save();
                    Log::info('Chargily: Order status updated to completed (VIP Reseller success)', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'old_status' => $oldOrderStatus,
                        'new_status' => 'completed',
                        'vip_status' => $status,
                    ]);
                }
            } elseif ($status === 'error') {
                // VIP Reseller error - ensure order is "sending" (needs attention)
                if ($oldOrderStatus === 'completed') {
                    $order->status = 'sending';
                    $order->save();
                    Log::warning('Chargily: Order status updated to sending (VIP Reseller error - needs attention)', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'old_status' => $oldOrderStatus,
                        'new_status' => 'sending',
                        'vip_status' => $status,
                    ]);
                }
            }

            if ($result['result'] === true) {
                Log::info('Chargily: Recharge successful', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'package_code' => $packageCode,
                    'user_id_ml' => $order->user_id_ml,
                    'zone_id_ml' => $order->zone_id_ml,
                    'trxid' => $vipResellerStatus->trxid,
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Recharge processed successfully',
                    'trxid' => $vipResellerStatus->trxid,
                ];
            } else {
                Log::error('Chargily: Recharge failed', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'package_code' => $packageCode,
                    'user_id_ml' => $order->user_id_ml,
                    'zone_id_ml' => $order->zone_id_ml,
                    'trxid' => $vipResellerStatus->trxid,
                    'api_response' => $result,
                ]);
                
                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Recharge failed',
                    'trxid' => $vipResellerStatus->trxid,
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
                VipResellerStatus::create([
                    'order_id' => $order->id,
                    'trxid' => null,
                    'data' => $order->user_id_ml ?? null,
                    'zone' => $order->zone_id_ml ?? null,
                    'service' => $order->diamondPack->code ?? null,
                    'status' => 'error',
                    'note' => 'Exception: ' . $e->getMessage(),
                    'price' => null,
                    'additional_data' => [
                        'exception' => $e->getMessage(),
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
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
        
        // Create directory if it doesn't exist
        $storagePath = public_path('storage/flexy_receipts');
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
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
        $order->status = 'pending_confirmation';
        $order->notes = $request->input('notes');
        $order->save();
        
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
