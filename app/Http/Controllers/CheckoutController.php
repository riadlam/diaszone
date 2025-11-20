<?php

namespace App\Http\Controllers;

use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\Flexy;
use App\Services\BinancePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

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
     * Show crypto payment form page (order confirmation before Binance payment)
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
     * Show Binance crypto payment page
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
        
        // Static Binance Pay credentials
        $binanceApiKey = 'sWrdDnMyALYAo03Zz36gG5PkWxOuAw0hxIQ973UVAQBXc8u22244KTccmRExFxwG';
        $binanceSecretKey = 'Wg3kYgj1H9uhiDWGOZ1EEHjOAo83AfXCjHn7cZAATXhYbUQQoZ0bJrbsLnE34O55';
        $binanceEndpoint = 'https://bpay.binanceapi.com/binancepay/openapi/';
        
        // Initialize Binance Pay service with static credentials
        $binancePayService = new BinancePayService($binanceApiKey, $binanceSecretKey, $binanceEndpoint);
        $binanceConfigured = $binancePayService->hasCredentials();
        
        // Prepare order data
        $orderData = [
            'merchant_trade_no' => $order->order_number . '_' . time(), // Unique merchant trade number
            'amount' => number_format($totalAmount, 2, '.', ''),
            'reference_goods_id' => (string) $order->diamondPack->id,
            'goods_name' => $order->diamondPack->diamonds . ' Diamonds + ' . $order->diamondPack->bonus_diamonds . ' Bonus',
            'buyer_id' => $order->user_id ? (string) $order->user_id : $order->user_id_ml,
            'buyer_name' => Auth::check() ? Auth::user()->name : 'Guest',
            'return_url' => route('crypto-payment-success', ['encrypted_order_id' => $encryptedOrderId]),
            'cancel_url' => route('home'),
        ];
        
        // If credentials are not configured, show mock page on localhost
        if (!$binanceConfigured) {
            if ($isLocalhost) {
                $mockBinanceData = [
                    'checkoutUrl' => '#',
                    'qrCodeUrl' => null,
                    'walletAddress' => '0x0000000000000000000000000000000000000000',
                ];
                
                return view('pages.crypto-payment', [
                    'order' => $order,
                    'encrypted_order_id' => $encryptedOrderId,
                    'total_amount' => $totalAmount,
                    'binance_data' => $mockBinanceData,
                    'checkout_url' => null,
                    'qr_code_url' => null,
                    'merchant_trade_no' => $orderData['merchant_trade_no'],
                    'is_localhost' => true,
                    'binance_error' => 'Binance Pay API credentials not configured. Add BINANCE_PAY_API_KEY and BINANCE_PAY_SECRET_KEY to your .env file.',
                ]);
            }
            
            // On production without credentials, redirect with error
            return redirect()->route('select-payment')->with('error', 'Binance Pay is not configured. Please contact support.');
        }
        
        // Credentials exist - try to create Binance Pay order
        $binanceResponse = $binancePayService->createOrder($orderData);
        
        if (!$binanceResponse['success']) {
            // On localhost, show payment page with error details for debugging
            if ($isLocalhost) {
                $mockBinanceData = [
                    'checkoutUrl' => '#',
                    'qrCodeUrl' => null,
                    'walletAddress' => '0x0000000000000000000000000000000000000000',
                ];
                
                // Get detailed error message
                $errorMessage = $binanceResponse['error'] ?? 'Binance Pay API call failed';
                
                // Check if it's a credential issue
                if (str_contains($errorMessage, 'Certificate-SN') || 
                    str_contains($errorMessage, 'Signature') ||
                    str_contains($errorMessage, 'Unauthorized')) {
                    $errorMessage = 'Binance Pay API credentials are invalid or incorrect. Please check your BINANCE_PAY_API_KEY and BINANCE_PAY_SECRET_KEY in .env file.';
                }
                
                return view('pages.crypto-payment', [
                    'order' => $order,
                    'encrypted_order_id' => $encryptedOrderId,
                    'total_amount' => $totalAmount,
                    'binance_data' => $mockBinanceData,
                    'checkout_url' => null,
                    'qr_code_url' => null,
                    'merchant_trade_no' => $orderData['merchant_trade_no'],
                    'is_localhost' => true,
                    'binance_error' => $errorMessage,
                ]);
            }
            
            // On production, redirect with error
            return redirect()->route('select-payment')->with('error', 'Failed to initialize payment: ' . ($binanceResponse['error'] ?? 'Unknown error'));
        }
        
        $binanceData = $binanceResponse['data'];
        
        return view('pages.crypto-payment', [
            'order' => $order,
            'encrypted_order_id' => $encryptedOrderId,
            'total_amount' => $totalAmount,
            'binance_data' => $binanceData,
            'checkout_url' => $binanceData['checkoutUrl'] ?? null,
            'qr_code_url' => $binanceData['qrCodeUrl'] ?? null,
            'merchant_trade_no' => $orderData['merchant_trade_no'],
            'is_localhost' => false,
        ]);
    }
    
    /**
     * Handle Binance Pay success callback
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
            
            // Static Binance Pay credentials
            $binanceApiKey = 'sWrdDnMyALYAo03Zz36gG5PkWxOuAw0hxIQ973UVAQBXc8u22244KTccmRExFxwG';
            $binanceSecretKey = 'Wg3kYgj1H9uhiDWGOZ1EEHjOAo83AfXCjHn7cZAATXhYbUQQoZ0bJrbsLnE34O55';
            $binanceEndpoint = 'https://bpay.binanceapi.com/binancepay/openapi/';
            
            // Query Binance Pay for order status
            $binancePayService = new BinancePayService($binanceApiKey, $binanceSecretKey, $binanceEndpoint);
            // Extract merchant trade no from order (you may need to store this in the order)
            $merchantTradeNo = $order->order_number . '_' . strtotime($order->created_at);
            
            $binanceResponse = $binancePayService->queryOrder($merchantTradeNo);
            
            if ($binanceResponse['success']) {
                $binanceData = $binanceResponse['data'];
                $status = $binanceData['status'] ?? 'UNKNOWN';
                
                if ($status === 'PAID' || $status === 'SUCCESS') {
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
     * Test Binance Pay credentials
     * Route: /test/binance
     */
    public function testBinanceCredentials()
    {
        // Static credentials for testing
        $apiKey = 'sWrdDnMyALYAo03Zz36gG5PkWxOuAw0hxIQ973UVAQBXc8u22244KTccmRExFxwG';
        $secretKey = 'Wg3kYgj1H9uhiDWGOZ1EEHjOAo83AfXCjHn7cZAATXhYbUQQoZ0bJrbsLnE34O55';
        $endpoint = 'https://bpay.binanceapi.com/binancepay/openapi/';
        
        $binancePayService = new BinancePayService($apiKey, $secretKey, $endpoint);
        $testResult = $binancePayService->testConnection();
        
        return response()->json($testResult, $testResult['success'] ? 200 : 400);
    }
}
