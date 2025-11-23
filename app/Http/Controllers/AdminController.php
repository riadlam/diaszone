<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\VipResellerStatus;
use App\Services\VipResellerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        // Get statistics from database
        // Calculate revenue from diamond_pack price_dzd
        $totalRevenue = Order::where('status', 'completed')
            ->with('diamondPack')
            ->get()
            ->sum(function ($order) {
                $priceDzd = $order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260);
                $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
                $discountAmount = ($priceDzd * $discountPercentage) / 100;
                return $priceDzd - $discountAmount;
            });
        
        $todayRevenue = Order::where('status', 'completed')
            ->whereDate('created_at', today())
            ->with('diamondPack')
            ->get()
            ->sum(function ($order) {
                $priceDzd = $order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260);
                $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
                $discountAmount = ($priceDzd * $discountPercentage) / 100;
                return $priceDzd - $discountAmount;
            });
        
        $monthlyRevenue = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->with('diamondPack')
            ->get()
            ->sum(function ($order) {
                $priceDzd = $order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260);
                $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
                $discountAmount = ($priceDzd * $discountPercentage) / 100;
                return $priceDzd - $discountAmount;
            });
        
        $stats = [
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_revenue' => $totalRevenue,
            'pending_orders' => Order::whereIn('status', ['pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay', 'pending_confirmation'])->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'refunded_orders' => Order::where('status', 'refunded')->count(),
            'today_revenue' => $todayRevenue,
            'monthly_revenue' => $monthlyRevenue,
        ];

        // Recent users
        $recentUsers = User::latest()->take(5)->get();

        // Recent orders from database
        $recentOrders = Order::with(['user', 'diamondPack'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
                $currencyText = 'Diamonds';
                $gameName = 'Mobile Legends';
                
                if ($gameType === 'freefire') {
                    $currencyText = 'Diamonds';
                    $gameName = 'Free Fire';
                } elseif ($gameType === 'pubgmobile') {
                    $currencyText = 'UC';
                    $gameName = 'PUBG Mobile';
                } elseif ($gameType === 'honorofkings') {
                    $currencyText = 'Tokens';
                    $gameName = 'Honor of Kings';
                } elseif ($gameType === 'bloodstrike') {
                    $currencyText = 'Golds';
                    $gameName = 'Blood Strike';
                }
                
                $packName = $order->diamondPack->name ?? ($order->diamondPack->diamonds . ' ' . $currencyText);
                if ($order->diamondPack->bonus_diamonds > 0) {
                    $packName .= ' + ' . $order->diamondPack->bonus_diamonds . ' Bonus';
                }
                
                // Calculate amount from price_dzd with discount
                $priceDzd = $order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260);
                $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
                $discountAmount = ($priceDzd * $discountPercentage) / 100;
                $amount = $priceDzd - $discountAmount;
                
                return [
                    'id' => $order->order_number,
                    'user' => $order->user->name ?? 'Guest',
                    'product' => $gameName,
                    'amount' => $amount,
                    'status' => $order->status,
                    'date' => $order->created_at,
                    'pack_name' => $packName,
                ];
            });

        // Revenue chart data (last 7 days)
        $revenueChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayName = $date->format('D');
            $revenue = Order::where('status', 'completed')
                ->whereDate('created_at', $date->toDateString())
                ->with('diamondPack')
                ->get()
                ->sum(function ($order) {
                    $priceDzd = $order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260);
                    $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
                    $discountAmount = ($priceDzd * $discountPercentage) / 100;
                    return $priceDzd - $discountAmount;
                });
            
            $revenueChart[] = [
                'day' => $dayName,
                'revenue' => $revenue,
            ];
        }

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentOrders', 'revenueChart'));
    }

    /**
     * Show users management
     */
    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    /**
     * Show orders management
     */
    public function orders(Request $request)
    {
        // Get orders from database with relationships
        $ordersQuery = Order::with(['user', 'diamondPack'])
            ->latest();
        
        // Apply filters if provided
        if ($request->has('status') && $request->status) {
            $ordersQuery->where('status', $request->status);
        }
        
        if ($request->has('search') && $request->search) {
            $ordersQuery->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }
        
        $orders = $ordersQuery->paginate(20);
        
        // Transform orders for display
        $orders->getCollection()->transform(function ($order) {
            $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
            $currencyText = 'Diamonds';
            $gameName = 'Mobile Legends';
            
            if ($gameType === 'freefire') {
                $currencyText = 'Diamonds';
                $gameName = 'Free Fire';
            } elseif ($gameType === 'pubgmobile') {
                $currencyText = 'UC';
                $gameName = 'PUBG Mobile';
            } elseif ($gameType === 'honorofkings') {
                $currencyText = 'Tokens';
                $gameName = 'Honor of Kings';
            } elseif ($gameType === 'bloodstrike') {
                $currencyText = 'Golds';
                $gameName = 'Blood Strike';
            }
            
            $packName = $order->diamondPack->name ?? ($order->diamondPack->diamonds . ' ' . $currencyText);
            if ($order->diamondPack->bonus_diamonds > 0) {
                $packName .= ' + ' . $order->diamondPack->bonus_diamonds . ' Bonus';
            }
            
            // Calculate amount from price_dzd with discount
            $priceDzd = $order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260);
            $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
            $discountAmount = ($priceDzd * $discountPercentage) / 100;
            $amount = $priceDzd - $discountAmount;
            
            return [
                'id' => $order->order_number,
                'user' => $order->user->name ?? 'Guest',
                'email' => $order->user->email ?? 'N/A',
                'product' => $gameName,
                'pack' => $packName,
                'amount' => $amount,
                'status' => $order->status,
                'date' => $order->created_at,
            ];
        });

        return view('admin.orders', compact('orders'));
    }

    /**
     * Show settings page
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Toggle user status
     */
    public function toggleUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent admin from deactivating themselves
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return back()->with('success', "User status updated to {$user->status}.");
    }

    /**
     * Get order details by order number
     */
    public function getOrderDetails($orderNumber)
    {
        $order = Order::with(['user', 'diamondPack', 'flexy', 'bmccp', 'cryptopay'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
        $currencyText = 'Diamonds';
        $gameName = 'Mobile Legends';
        
        if ($gameType === 'freefire') {
            $currencyText = 'Diamonds';
            $gameName = 'Free Fire';
        } elseif ($gameType === 'pubgmobile') {
            $currencyText = 'UC';
            $gameName = 'PUBG Mobile';
        } elseif ($gameType === 'honorofkings') {
            $currencyText = 'Tokens';
            $gameName = 'Honor of Kings';
        } elseif ($gameType === 'bloodstrike') {
            $currencyText = 'Golds';
            $gameName = 'Blood Strike';
        }
        
        $packName = $order->diamondPack->name ?? ($order->diamondPack->diamonds . ' ' . $currencyText);
        if ($order->diamondPack->bonus_diamonds > 0) {
            $packName .= ' + ' . $order->diamondPack->bonus_diamonds . ' Bonus';
        }
        
        // Calculate amount from price_dzd with discount
        $priceDzd = $order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260);
        $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
        $discountAmount = ($priceDzd * $discountPercentage) / 100;
        $amount = $priceDzd - $discountAmount;

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'user_name' => $order->user->name ?? 'Guest',
                'user_email' => $order->user->email ?? 'N/A',
                'game_name' => $gameName,
                'pack_name' => $packName,
                'amount' => $amount,
                'status' => $order->status,
                'created_at' => $order->created_at->format('M d, Y H:i'),
                'updated_at' => $order->updated_at->format('M d, Y H:i'),
                'user_id_ml' => $order->user_id_ml,
                'zone_id_ml' => $order->zone_id_ml,
                'player_id_ff' => $order->player_id_ff,
                'player_id_pubg' => $order->player_id_pubg,
                'player_id_hok' => $order->player_id_hok,
                'user_id_bs' => $order->user_id_bs,
                'server_bs' => $order->server_bs,
                'notes' => $order->notes,
                'flexy_id' => $order->flexy_id,
                'bmccp_id' => $order->bmccp_id,
                'cryptopay_id' => $order->cryptopay_id,
            ]
        ]);
    }

    /**
     * Update order status
     * Only processes recharge if:
     * 1. Old status was 'pending_confirmation'
     * 2. New status is 'completed'
     * 3. Order has flexy_id (not null) - meaning it's a Flexy payment
     * 4. Update is from admin dashboard UI
     */
    public function updateOrderStatus(Request $request, $orderNumber)
    {
        $request->validate([
            'status' => 'required|in:pending,pending_flexy,pending_bmccp,pending_cryptopay,pending_confirmation,sending,completed,cancelled,refunded',
            'from_admin' => 'sometimes|boolean', // Flag to ensure this is from admin dashboard
        ]);

        // Load order with relationships
        $order = Order::with('diamondPack')->where('order_number', $orderNumber)->firstOrFail();
        $oldStatus = $order->status;
        $newStatus = $request->status;
        
        // Update order status
        $order->status = $newStatus;
        $order->save();

        // Reload order to get fresh data including flexy_id
        $order->refresh();

        $message = "Order status updated from " . ucfirst(str_replace('_', ' ', $oldStatus)) . " to " . ucfirst(str_replace('_', ' ', $newStatus));
        
        // CRITICAL: Only process recharge if ALL conditions are met:
        // 1. Old status was 'pending_confirmation'
        // 2. New status is 'completed'
        // 3. Order has flexy_id (not null) - confirms it's a Flexy payment
        // 4. Update is from admin dashboard (from_admin flag or route check)
        $isFromAdmin = $request->has('from_admin') ? (bool)$request->from_admin : true; // Default to true for admin routes
        $hasFlexyId = !is_null($order->flexy_id);
        
        if ($oldStatus === 'pending_confirmation' && $newStatus === 'completed' && $hasFlexyId && $isFromAdmin) {
            Log::info('Processing recharge - All conditions met', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'flexy_id' => $order->flexy_id,
                'from_admin' => $isFromAdmin,
            ]);
            
            $rechargeResult = $this->processRecharge($order);
            
            if ($rechargeResult['success']) {
                $message .= ". Recharge processed successfully.";
            } else {
                $message .= ". Warning: " . $rechargeResult['message'];
            }
        } else {
            // Log why recharge was skipped
            if ($oldStatus === 'pending_confirmation' && $newStatus === 'completed') {
                Log::info('Recharge skipped - Conditions not met', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'has_flexy_id' => $hasFlexyId,
                    'flexy_id' => $order->flexy_id,
                    'from_admin' => $isFromAdmin,
                    'reason' => !$hasFlexyId ? 'No flexy_id' : (!$isFromAdmin ? 'Not from admin' : 'Unknown'),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
            ]
        ]);
    }

    /**
     * Process recharge for completed orders
     * This method is ONLY called when:
     * - Old status was 'pending_confirmation'
     * - New status is 'completed'
     * - Order has flexy_id (not null)
     * - Update is from admin dashboard
     */
    private function processRecharge(Order $order)
    {
        try {
            // Double-check flexy_id exists (safety check)
            if (is_null($order->flexy_id)) {
                Log::error('Recharge aborted: flexy_id is null', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                return [
                    'success' => false,
                    'message' => 'Order does not have flexy_id. Recharge aborted.',
                ];
            }

            // Only process Mobile Legends orders for now
            $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
            
            if ($gameType !== 'mobilelegends') {
                Log::info('Recharge skipped: Not a Mobile Legends order', [
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
                Log::warning('Recharge skipped: Missing user_id_ml or zone_id_ml', [
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
            // This ensures the User ID and Zone ID are valid before attempting recharge
            Log::info('Validating nickname before recharge', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id_ml' => $order->user_id_ml,
                'zone_id_ml' => $order->zone_id_ml,
            ]);

            $vipReseller = new VipResellerService();
            $nicknameValidation = $vipReseller->checkNickname($order->user_id_ml, $order->zone_id_ml);

            if ($nicknameValidation['result'] !== true) {
                Log::error('Recharge aborted: Nickname validation failed', [
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

            // Nickname validation successful - log the nickname
            $nickname = $nicknameValidation['data'] ?? 'Unknown';
            Log::info('Nickname validation successful - Proceeding with recharge', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id_ml' => $order->user_id_ml,
                'zone_id_ml' => $order->zone_id_ml,
                'nickname' => $nickname,
            ]);

            // Get package code from diamond_packs
            $packageCode = $order->diamondPack->code ?? null;
            
            if (empty($packageCode)) {
                Log::warning('Recharge skipped: Missing package code', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'diamond_pack_id' => $order->diamond_pack_id,
                ]);
                return [
                    'success' => false,
                    'message' => 'Package code not found',
                ];
            }

            // STEP 2: Call VIP Reseller API to recharge (nickname already validated)
            $result = $vipReseller->placeOrder(
                $packageCode,
                $order->user_id_ml,
                $order->zone_id_ml
            );

            // STEP 3: Save response to vipreseller_status table
            $apiData = $result['data'] ?? [];
            $apiStatus = $apiData['status'] ?? 'error';
            
            // Map API status to our enum (waiting, success, error)
            $status = match(strtolower($apiStatus)) {
                'waiting' => 'waiting',
                'success', 'completed', 'paid' => 'success',
                default => 'error',
            };
            
            // If result is false, set status to error
            if ($result['result'] !== true) {
                $status = 'error';
            }

            // Prepare additional data (store full response and other fields)
            $additionalData = [
                'full_response' => $result,
                'balance' => $apiData['balance'] ?? null,
                'message' => $result['message'] ?? null,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
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

            Log::info('VIP Reseller status saved', [
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
                    Log::info('Order status updated to sending (VIP Reseller waiting)', [
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
                    Log::info('Order status updated to completed (VIP Reseller success)', [
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
                    Log::warning('Order status updated to sending (VIP Reseller error - needs attention)', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'old_status' => $oldOrderStatus,
                        'new_status' => 'sending',
                        'vip_status' => $status,
                    ]);
                }
            }

            if ($result['result'] === true) {
                Log::info('Recharge successful', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'package_code' => $packageCode,
                    'user_id_ml' => $order->user_id_ml,
                    'zone_id_ml' => $order->zone_id_ml,
                    'trxid' => $vipResellerStatus->trxid,
                    'api_response' => $result,
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Recharge processed successfully',
                    'data' => $result['data'] ?? null,
                    'trxid' => $vipResellerStatus->trxid,
                ];
            } else {
                Log::error('Recharge failed', [
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
            Log::error('Recharge exception: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Try to save error status even if exception occurred
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
                    ],
                ]);
            } catch (\Exception $saveException) {
                Log::error('Failed to save error status: ' . $saveException->getMessage());
            }
            
            return [
                'success' => false,
                'message' => 'Error processing recharge: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle VIP Reseller webhook for order status updates
     * 
     * Webhook receives status updates: waiting → processing → success/error
     * Signature verification: X-Client-Signature = md5(API_ID + API_KEY)
     */
    public function vipResellerWebhook(Request $request)
    {
        try {
            // Log incoming webhook request
            Log::info('VIP Reseller webhook received', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'raw_body' => $request->getContent(),
            ]);

            // Verify signature
            $receivedSignature = $request->header('X-Client-Signature');
            $apiId = env('VIP_RESELLER_API_ID');
            $apiKey = env('VIP_RESELLER_API_KEY');
            $expectedSignature = md5($apiId . $apiKey);

            if (empty($receivedSignature) || $receivedSignature !== $expectedSignature) {
                Log::warning('VIP Reseller webhook signature verification failed', [
                    'received_signature' => $receivedSignature,
                    'expected_signature' => $expectedSignature,
                    'ip' => $request->ip(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 401);
            }

            Log::info('VIP Reseller webhook signature verified successfully');

            // Get webhook data
            $webhookData = $request->input('data', []);
            
            if (empty($webhookData)) {
                Log::warning('VIP Reseller webhook: Empty data received', [
                    'request_data' => $request->all(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Empty data',
                ], 400);
            }

            $trxid = $webhookData['trxid'] ?? null;
            $status = $webhookData['status'] ?? null;
            $data = $webhookData['data'] ?? null;
            $zone = $webhookData['zone'] ?? null;
            $service = $webhookData['service'] ?? null;
            $note = $webhookData['note'] ?? null;
            $price = $webhookData['price'] ?? null;

            if (empty($trxid)) {
                Log::warning('VIP Reseller webhook: Missing trxid', [
                    'webhook_data' => $webhookData,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Missing trxid',
                ], 400);
            }

            // Map API status to our enum
            $mappedStatus = match(strtolower($status ?? '')) {
                'waiting', 'processing' => 'waiting',
                'success', 'completed', 'paid' => 'success',
                'error', 'failed', 'canceled' => 'error',
                default => 'waiting',
            };

            Log::info('VIP Reseller webhook processing', [
                'trxid' => $trxid,
                'api_status' => $status,
                'mapped_status' => $mappedStatus,
                'data' => $data,
                'zone' => $zone,
            ]);

            // Find or create vipreseller_status record
            $vipResellerStatus = VipResellerStatus::where('trxid', $trxid)->first();

            if ($vipResellerStatus) {
                // Update existing record
                $oldStatus = $vipResellerStatus->status;
                
                $vipResellerStatus->update([
                    'status' => $mappedStatus,
                    'note' => $note ?? $vipResellerStatus->note,
                    'price' => $price ?? $vipResellerStatus->price,
                    'additional_data' => array_merge(
                        $vipResellerStatus->additional_data ?? [],
                        [
                            'webhook_update' => now()->toDateTimeString(),
                            'api_status' => $status,
                            'webhook_data' => $webhookData,
                        ]
                    ),
                ]);

                Log::info('VIP Reseller status updated', [
                    'vipreseller_status_id' => $vipResellerStatus->id,
                    'trxid' => $trxid,
                    'old_status' => $oldStatus,
                    'new_status' => $mappedStatus,
                ]);

                // Update order status based on VIP Reseller status (waiting, success, error)
                if ($oldStatus !== $mappedStatus) {
                    $this->updateOrderFromWebhook($vipResellerStatus, $webhookData);
                }
            } else {
                // Create new record if not found
                // Try to find order from additional_data if available
                $orderId = null;
                if (isset($webhookData['additional_data']['order_id'])) {
                    $orderId = $webhookData['additional_data']['order_id'];
                } elseif (isset($webhookData['order_id'])) {
                    $orderId = $webhookData['order_id'];
                } else {
                    // Try to find order by user_id_ml and zone_id_ml
                    $order = Order::where('user_id_ml', $data)
                        ->where('zone_id_ml', $zone)
                        ->where('status', 'completed')
                        ->latest()
                        ->first();
                    if ($order) {
                        $orderId = $order->id;
                    }
                }
                
                $vipResellerStatus = VipResellerStatus::create([
                    'order_id' => $orderId,
                    'trxid' => $trxid,
                    'data' => $data,
                    'zone' => $zone,
                    'service' => $service,
                    'status' => $mappedStatus,
                    'note' => $note,
                    'price' => $price,
                    'additional_data' => [
                        'webhook_created' => now()->toDateTimeString(),
                        'api_status' => $status,
                        'webhook_data' => $webhookData,
                    ],
                ]);

                Log::info('VIP Reseller status created from webhook', [
                    'vipreseller_status_id' => $vipResellerStatus->id,
                    'trxid' => $trxid,
                    'status' => $mappedStatus,
                ]);

                // Update order status based on VIP Reseller status
                $this->updateOrderFromWebhook($vipResellerStatus, $webhookData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'trxid' => $trxid,
                'status' => $mappedStatus,
            ], 200);

        } catch (\Exception $e) {
            Log::error('VIP Reseller webhook exception: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update order status from VIP Reseller webhook based on status
     * Handles: waiting, success, error
     */
    private function updateOrderFromWebhook(VipResellerStatus $vipResellerStatus, array $webhookData)
    {
        try {
            // Get order using relationship
            $order = $vipResellerStatus->order;
            
            if (!$order) {
                // Try to find order by user_id_ml and zone_id_ml as fallback
                $order = Order::where('user_id_ml', $vipResellerStatus->data)
                    ->where('zone_id_ml', $vipResellerStatus->zone)
                    ->where('status', 'completed')
                    ->latest()
                    ->first();
                
                // If found, update the vipreseller_status with order_id
                if ($order) {
                    $vipResellerStatus->order_id = $order->id;
                    $vipResellerStatus->save();
                }
            }

            if ($order) {
                $oldOrderStatus = $order->status;
                $vipStatus = $vipResellerStatus->status;
                
                Log::info('Order found for VIP Reseller webhook update', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'trxid' => $vipResellerStatus->trxid,
                    'vip_status' => $vipStatus,
                    'current_order_status' => $oldOrderStatus,
                ]);

                // Update order status based on VIP Reseller webhook status
                // Flow: Chargily/Flexy paid → "sending" → VIP Reseller webhook updates → final status
                
                if ($vipStatus === 'waiting') {
                    // VIP Reseller is processing the topup
                    // Set order status to "sending" (payment done, waiting for diamonds topup)
                    if ($oldOrderStatus !== 'sending') {
                        $order->status = 'sending';
                        $order->save();
                        
                        Log::info('Order status updated to sending (VIP Reseller waiting - payment done, waiting for topup)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'old_status' => $oldOrderStatus,
                            'new_status' => 'sending',
                            'vip_status' => $vipStatus,
                        ]);
                    } else {
                        Log::info('Order status already sending (VIP Reseller waiting)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'order_status' => $oldOrderStatus,
                            'vip_status' => $vipStatus,
                        ]);
                    }
                }
                elseif ($vipStatus === 'success') {
                    // VIP Reseller successfully delivered the topup
                    // Change order status to "completed" (everything done)
                    if ($oldOrderStatus !== 'completed') {
                        $order->status = 'completed';
                        $order->save();
                        
                        Log::info('Order status updated to completed (VIP Reseller success - topup delivered)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'old_status' => $oldOrderStatus,
                            'new_status' => 'completed',
                            'vip_status' => $vipStatus,
                        ]);
                    } else {
                        Log::info('Order status already completed (VIP Reseller success)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'order_status' => $oldOrderStatus,
                            'vip_status' => $vipStatus,
                        ]);
                    }
                }
                elseif ($vipStatus === 'error') {
                    // VIP Reseller failed to deliver the topup
                    // Keep order as "sending" to indicate it needs attention (payment done, but topup failed)
                    if ($oldOrderStatus === 'completed') {
                        // Change from completed to sending (needs attention)
                        $order->status = 'sending';
                        $order->save();
                        
                        // Add error note to order
                        $errorNote = 'VIP Reseller topup error: ' . ($vipResellerStatus->note ?? 'Unknown error');
                        if (!empty($order->notes)) {
                            $order->notes = $order->notes . "\n" . $errorNote;
                        } else {
                            $order->notes = $errorNote;
                        }
                        $order->save();
                        
                        Log::warning('Order status updated to sending (VIP Reseller error - payment done but topup failed, needs attention)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'old_status' => $oldOrderStatus,
                            'new_status' => 'sending',
                            'vip_status' => $vipStatus,
                            'vip_note' => $vipResellerStatus->note,
                        ]);
                    } else {
                        Log::warning('VIP Reseller topup error (order already in sending status)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'order_status' => $oldOrderStatus,
                            'vip_status' => $vipStatus,
                            'vip_note' => $vipResellerStatus->note,
                        ]);
                    }
                }
            } else {
                Log::info('No matching order found for VIP Reseller webhook', [
                    'vipreseller_status_id' => $vipResellerStatus->id,
                    'data' => $vipResellerStatus->data,
                    'zone' => $vipResellerStatus->zone,
                    'trxid' => $vipResellerStatus->trxid,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating order from VIP Reseller webhook: ' . $e->getMessage(), [
                'vipreseller_status_id' => $vipResellerStatus->id,
                'trxid' => $vipResellerStatus->trxid,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

