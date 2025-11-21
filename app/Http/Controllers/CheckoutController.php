<?php

namespace App\Http\Controllers;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\Flexy;
use App\Services\NowPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    /**
     * API endpoint to fetch diamond packs by IDs
     */
    public function getPacks(Request $request)
    {
        $packIds = $request->input('ids', []);
        
        if (empty($packIds)) {
            return response()->json(['packs' => []]);
        }
        
        // Ensure packIds is an array
        if (!is_array($packIds)) {
            $packIds = [$packIds];
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
                    'discount' => (float) $pack->discount_percentage,
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
                'description' => 'Mobile payment method'
            ],
            [
                'id' => 'cryptocurrency',
                'name' => 'Cryptocurrency',
                'icon' => 'cryptocurrency.webp',
                'description' => 'Pay with crypto (USD)'
            ],
            [
                'id' => 'flexy',
                'name' => 'Flexy',
                'icon' => 'flexy.webp',
                'description' => 'Flexible payment option'
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
        $request->validate([
            'cart_items' => 'required|array|min:1',
            'cart_items.*.pack_id' => 'required|exists:diamond_packs,id',
            'cart_items.*.user_id' => 'required|string',
            'cart_items.*.zone_id' => 'required|string',
        ]);
        
        $cartItems = $request->input('cart_items');
        $userId = Auth::check() ? Auth::id() : null;
        $createdOrders = [];
        
        foreach ($cartItems as $item) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $userId,
                'diamond_pack_id' => $item['pack_id'],
                'status' => 'pending',
                'user_id_ml' => $item['user_id'],
                'zone_id_ml' => $item['zone_id'],
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
                'notes' => $order->notes,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
                'diamond_pack' => [
                    'id' => $order->diamondPack->id,
                    'diamonds' => $order->diamondPack->diamonds,
                    'bonus_diamonds' => $order->diamondPack->bonus_diamonds,
                    'price' => (float) $order->diamondPack->price,
                    'discount_percentage' => (float) $order->diamondPack->discount_percentage,
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
            // Decrypt the order ID
            $orderId = Crypt::decryptString($request->input('encrypted_order_id'));
        } catch (\Exception $e) {
            return back()->withErrors(['encrypted_order_id' => 'Invalid order ID'])->withInput();
        }
        
        $order = Order::findOrFail($orderId);
        
        // Store the receipt image
        $imagePath = $request->file('receipt_image')->store('flexy_receipts', 'public');
        
        // Create or update Flexy record
        $flexy = Flexy::create([
            'receipt_image' => $imagePath,
            'diamond_pack_id' => $order->diamond_pack_id,
            'status' => 'pending',
        ]);
        
        // Link order to flexy
        $order->flexy_id = $flexy->id;
        $order->notes = $request->input('notes');
        $order->save();
        
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
     * Show NOWPayments crypto payment page
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
        
        // Check if we're on localhost/testing
        $isLocalhost = in_array(config('app.env'), ['local', 'testing']) || 
                       str_contains(request()->getHost(), 'localhost') ||
                       str_contains(request()->getHost(), '127.0.0.1');
        
        // NOWPayments API key (will be set from env or static later)
        $nowPaymentsApiKey = null; // Will be set from env or static later
        $nowPaymentsEndpoint = 'https://api.nowpayments.io/v1/';
        
        // Initialize NOWPayments service
        $nowPaymentsService = new NowPaymentsService($nowPaymentsApiKey, $nowPaymentsEndpoint);
        $nowPaymentsConfigured = $nowPaymentsService->hasCredentials();
        
        // Prepare order data
        // Use usdttrc20 (USDT on TRC20) as default - it's usually the cheapest option
        $orderData = [
            'order_id' => $order->order_number . '_' . time(), // Unique order number
            'amount' => number_format($totalAmount, 2, '.', ''),
            'price_currency' => 'usd',
            'goods_name' => $order->diamondPack->diamonds . ' Diamonds + ' . $order->diamondPack->bonus_diamonds . ' Bonus',
            'pay_currency' => 'usdttrc20', // USDT on TRC20 network (low fees)
            'return_url' => route('crypto-payment-success', ['encrypted_order_id' => $encryptedOrderId]),
            'cancel_url' => route('home'),
            'ipn_callback_url' => route('nowpayments.webhook'),
        ];
        
        // If credentials are not configured, show mock page on localhost
        if (!$nowPaymentsConfigured) {
            if ($isLocalhost) {
                $mockPaymentData = [
                    'payment_id' => 'test_' . time(),
                    'payment_status' => 'waiting',
                    'pay_address' => '0x0000000000000000000000000000000000000000',
                    'pay_amount' => $totalAmount,
                    'pay_currency' => 'usdt',
                    'invoice_url' => '#',
                ];
                
                return view('pages.crypto-payment', [
                    'order' => $order,
                    'encrypted_order_id' => $encryptedOrderId,
                    'total_amount' => $totalAmount,
                    'payment_data' => $mockPaymentData,
                    'payment_url' => null,
                    'pay_address' => null,
                    'payment_id' => $orderData['order_id'],
                    'is_localhost' => true,
                    'payment_error' => 'NOWPayments API key is not configured. Add NOWPAYMENTS_API_KEY to your .env file.',
                ]);
            }
            
            // On production without credentials, redirect with error
            return redirect()->route('select-payment')->with('error', 'Cryptocurrency payment is not configured. Please contact support.');
        }
        
        // Credentials exist - try to create NOWPayments invoice (returns invoice_url)
        $paymentResponse = $nowPaymentsService->createInvoice($orderData);
        
        // If invoice creation fails due to currency issue, try alternative currencies
        if (!$paymentResponse['success'] && 
            (str_contains($paymentResponse['error'], 'estimate') || 
             str_contains($paymentResponse['error'], 'currency'))) {
            // Get available currencies and try alternatives
            $currenciesResponse = $nowPaymentsService->getAvailableCurrencies();
            $availableCurrencies = [];
            if ($currenciesResponse['success']) {
                $availableCurrencies = $currenciesResponse['data']['currencies'] ?? [];
            }
            
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
        
        if (!$paymentResponse['success']) {
            // On localhost, show payment page with error details for debugging
            if ($isLocalhost) {
                $mockPaymentData = [
                    'payment_id' => 'test_' . time(),
                    'payment_status' => 'waiting',
                    'pay_address' => '0x0000000000000000000000000000000000000000',
                    'pay_amount' => $totalAmount,
                    'pay_currency' => 'usdt',
                    'invoice_url' => '#',
                ];
                
                // Get detailed error message
                $errorMessage = $paymentResponse['error'] ?? 'NOWPayments API call failed';
                
                return view('pages.crypto-payment', [
                    'order' => $order,
                    'encrypted_order_id' => $encryptedOrderId,
                    'total_amount' => $totalAmount,
                    'payment_data' => $mockPaymentData,
                    'payment_url' => null,
                    'pay_address' => null,
                    'payment_id' => $orderData['order_id'],
                    'is_localhost' => true,
                    'payment_error' => $errorMessage,
                ]);
            }
            
            // On production, redirect with error
            return redirect()->route('select-payment')->with('error', 'Failed to initialize payment: ' . ($paymentResponse['error'] ?? 'Unknown error'));
        }
        
        $paymentData = $paymentResponse['data'];
        
        // Store the NOWPayments invoice_id or payment_id in the order for future status checks
        if (isset($paymentData['invoice_id'])) {
            $order->nowpayments_payment_id = $paymentData['invoice_id'];
            $order->save();
        } elseif (isset($paymentData['payment_id'])) {
            $order->nowpayments_payment_id = $paymentData['payment_id'];
            $order->save();
        }
        
        // Invoice endpoint returns invoice_url directly
        $paymentUrl = $paymentData['invoice_url'] ?? null;
        
        return view('pages.crypto-payment', [
            'order' => $order,
            'encrypted_order_id' => $encryptedOrderId,
            'total_amount' => $totalAmount,
            'payment_data' => $paymentData,
            'payment_url' => $paymentUrl,
            'pay_address' => $paymentData['pay_address'] ?? null,
            'payment_id' => $paymentData['payment_id'] ?? $orderData['order_id'],
            'is_localhost' => false,
        ]);
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
}
