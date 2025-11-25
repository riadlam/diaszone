<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\VipResellerStatus;
use App\Services\VipResellerService;
use App\Services\TelegramService;
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
        // Check if this is a DataTables AJAX request
        if ($request->ajax()) {
            return $this->getOrdersData($request);
        }
        
        // Regular page load - return view
        return view('admin.orders');
    }
    
    /**
     * Get orders data for DataTables (AJAX)
     */
    private function getOrdersData(Request $request)
    {
        // Get DataTables parameters
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $searchValue = $request->get('search')['value'] ?? '';
        $orderColumn = $request->get('order')[0]['column'] ?? 0;
        $orderDir = $request->get('order')[0]['dir'] ?? 'desc';
        
        // Get orders from database with relationships
        $ordersQuery = Order::with(['user', 'diamondPack']);
        
        // Apply search filter
        if (!empty($searchValue)) {
            $searchTerm = trim($searchValue);
            $searchTerm = preg_replace('/[^a-zA-Z0-9@._\s-]/', '', $searchTerm); // Sanitize
            
            $ordersQuery->where(function($q) use ($searchTerm) {
                $q->where('order_number', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', '%' . $searchTerm . '%')
                                ->orWhere('email', 'like', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('diamondPack', function($packQuery) use ($searchTerm) {
                      $packQuery->where('game_type', 'like', '%' . $searchTerm . '%')
                                ->orWhere('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        
        // Apply status filter if provided
        if ($request->has('status') && !empty($request->status)) {
            $ordersQuery->where('status', $request->status);
        }
        
        // Get total count before filtering
        $totalRecords = Order::count();
        $filteredRecords = $ordersQuery->count();
        
        // Apply ordering - default to created_at desc
        if ($orderColumn == 0) {
            $ordersQuery->orderBy('order_number', $orderDir);
        } elseif ($orderColumn == 1) {
            // Amount - join with diamond_packs
            $ordersQuery->join('diamond_packs', 'orders.diamond_pack_id', '=', 'diamond_packs.id')
                ->orderBy('diamond_packs.price_dzd', $orderDir)
                ->select('orders.*');
        } elseif ($orderColumn == 2) {
            $ordersQuery->orderBy('status', $orderDir);
        } elseif ($orderColumn == 3) {
            // Game - join with diamond_packs
            $ordersQuery->join('diamond_packs', 'orders.diamond_pack_id', '=', 'diamond_packs.id')
                ->orderBy('diamond_packs.game_type', $orderDir)
                ->select('orders.*');
        } else {
            $ordersQuery->orderBy('created_at', $orderDir);
        }
        
        // Get paginated results
        $orders = $ordersQuery->skip($start)->take($length)->get();
        
        // Transform orders for DataTables
        $data = $orders->map(function ($order) {
            $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
            $gameName = 'Mobile Legends';
            
            if ($gameType === 'freefire') {
                $gameName = 'Free Fire';
            } elseif ($gameType === 'pubgmobile') {
                $gameName = 'PUBG Mobile';
            } elseif ($gameType === 'honorofkings') {
                $gameName = 'Honor of Kings';
            } elseif ($gameType === 'bloodstrike') {
                $gameName = 'Blood Strike';
            }
            
            // Calculate amount from price_dzd with discount
            $priceDzd = $order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260);
            $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
            $discountAmount = ($priceDzd * $discountPercentage) / 100;
            $amount = $priceDzd - $discountAmount;
            
            // Status class
            $status = $order->status;
            $statusClass = 'text-gray-600';
            $statusText = ucfirst(str_replace('_', ' ', $status));
            
            if ($status === 'completed') {
                $statusClass = 'text-green-600';
            } elseif (in_array($status, ['pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay', 'pending_confirmation'])) {
                $statusClass = 'text-yellow-600';
            } elseif ($status === 'sending') {
                $statusClass = 'text-blue-600';
            } elseif (in_array($status, ['cancelled', 'refunded'])) {
                $statusClass = 'text-red-600';
            }
            
            return [
                'id' => $order->order_number,
                'amount' => number_format(round($amount), 0) . ' DZD',
                'status' => '<span class="text-sm font-medium ' . $statusClass . '">' . $statusText . '</span>',
                'game' => $gameName,
                'action' => '<button onclick="viewOrder(\'' . $order->order_number . '\')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">View</button>',
            ];
        });
        
        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
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
            Log::warning('Admin attempted to deactivate own account', [
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email,
                'target_user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $oldStatus = $user->status;
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        // Log critical admin action
        Log::info('Admin toggled user status', [
            'admin_id' => Auth::id(),
            'admin_email' => Auth::user()->email,
            'target_user_id' => $user->id,
            'target_user_email' => $user->email,
            'old_status' => $oldStatus,
            'new_status' => $user->status,
            'ip' => $request->ip(),
        ]);

        return back()->with('success', "User status updated to {$user->status}.");
    }

    /**
     * Get order details by order number
     */
    public function getOrderDetails($orderNumber)
    {
        $order = Order::with([
            'user', 
            'diamondPack', 
            'flexy', 
            'bmccp', 
            'cryptopay',
            'chargilyStatus',
            'vipResellerStatuses' => function($query) {
                $query->latest(); // Get most recent first
            }
        ])
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

        // Prepare payment information
        $paymentInfo = [];
        
        // Chargily Payment Info
        if ($order->chargilyStatus) {
            $chargily = $order->chargilyStatus;
            $paymentInfo['chargily'] = [
                'checkout_id' => $chargily->checkout_id,
                'status' => $chargily->status,
                'event_type' => $chargily->event_type,
                'amount' => $chargily->amount,
                'fees' => $chargily->fees,
                'payment_method' => $chargily->payment_method,
                'created_at' => $chargily->created_at->format('M d, Y H:i'),
                'updated_at' => $chargily->updated_at->format('M d, Y H:i'),
            ];
        }
        
        // VIP Reseller Status Info (get latest)
        if ($order->vipResellerStatuses && $order->vipResellerStatuses->count() > 0) {
            $vipStatus = $order->vipResellerStatuses->first();
            $paymentInfo['vip_reseller'] = [
                'trxid' => $vipStatus->trxid,
                'status' => $vipStatus->status,
                'data' => $vipStatus->data,
                'zone' => $vipStatus->zone,
                'service' => $vipStatus->service,
                'note' => $vipStatus->note,
                'price' => $vipStatus->price,
                'created_at' => $vipStatus->created_at->format('M d, Y H:i'),
                'updated_at' => $vipStatus->updated_at->format('M d, Y H:i'),
            ];
        }
        
        // Flexy Payment Info
        if ($order->flexy) {
            $flexy = $order->flexy;
            $paymentInfo['flexy'] = [
                'id' => $flexy->id,
                'status' => $flexy->status,
                'receipt_image' => $flexy->receipt_image ? asset($flexy->receipt_image) : null,
                'created_at' => $flexy->created_at->format('M d, Y H:i'),
                'updated_at' => $flexy->updated_at->format('M d, Y H:i'),
            ];
        }
        
        // BMCCP Payment Info (old Chargily v1)
        if ($order->bmccp) {
            $bmccp = $order->bmccp;
            $paymentInfo['bmccp'] = [
                'id' => $bmccp->id,
                'status' => $bmccp->status,
                'invoice_number' => $bmccp->invoice_number,
                'receipt_image' => $bmccp->receipt_image ? asset($bmccp->receipt_image) : null,
                'notes' => $bmccp->notes,
                'created_at' => $bmccp->created_at->format('M d, Y H:i'),
                'updated_at' => $bmccp->updated_at->format('M d, Y H:i'),
            ];
        }
        
        // Cryptopay Payment Info
        if ($order->cryptopay) {
            $cryptopay = $order->cryptopay;
            $paymentInfo['cryptopay'] = [
                'id' => $cryptopay->id,
                'payment_id' => $cryptopay->payment_id,
                'transaction_id' => $cryptopay->transaction_id,
                'status' => $cryptopay->status,
                'amount' => $cryptopay->amount,
                'currency' => $cryptopay->currency,
                'created_at' => $cryptopay->created_at->format('M d, Y H:i'),
                'updated_at' => $cryptopay->updated_at->format('M d, Y H:i'),
            ];
        }

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
                'chargily_status_id' => $order->chargily_status_id,
                'payment_info' => $paymentInfo,
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

        // Send Telegram notification for important status changes (skip pending_flexy)
        if ($newStatus !== 'pending_flexy' && in_array($newStatus, ['pending_confirmation', 'sending', 'completed'])) {
            try {
                $order->load('diamondPack', 'user');
                $message = TelegramService::formatOrderMessage($order);
                // Add confirm button only for pending_confirmation orders
                $addButton = ($newStatus === 'pending_confirmation');
                $messageId = TelegramService::sendMessage($message, $addButton);
                if ($messageId) {
                    $order->tlg_message_id = $messageId;
                    $order->save();
                }
            } catch (\Exception $e) {
                Log::error('Telegram notification failed for admin status update', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Reload order to get fresh data including flexy_id
        $order->refresh();

        // Log critical admin action
        Log::info('Admin updated order status', [
            'admin_id' => Auth::id(),
            'admin_email' => Auth::user()->email,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'ip' => $request->ip(),
        ]);

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
            // Check if VIP Reseller status already has success (prevent duplicate requests)
            $hasSuccessStatus = $order->vipResellerStatuses()
                ->where('status', 'success')
                ->exists();
            
            if ($hasSuccessStatus) {
                Log::info('Recharge skipped: VIP Reseller status already success', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                return [
                    'success' => true,
                    'message' => 'Recharge already processed (VIP Reseller success)',
                ];
            }
            
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
                    
                    // Update Telegram message if exists
                    if ($order->tlg_message_id) {
                        try {
                            $order->load('diamondPack', 'user', 'vipResellerStatuses');
                            $updatedMessage = TelegramService::formatOrderMessage($order);
                            $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '⏳ <b>Order Confirmed - Waiting for VIP Reseller</b>', $updatedMessage);
                            TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                        } catch (\Exception $e) {
                            Log::error('Failed to update Telegram message', [
                                'order_id' => $order->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            } elseif ($status === 'success') {
                // Fetch balance from VIP Reseller API when status becomes success
                try {
                    $vipReseller = new VipResellerService();
                    $profileResult = $vipReseller->getProfile();
                    
                    if ($profileResult['result'] === true && isset($profileResult['data']['balance'])) {
                        $balance = (string) $profileResult['data']['balance'];
                        $vipResellerStatus->balance = $balance;
                        $vipResellerStatus->save();
                        
                        Log::info('Balance fetched and saved for successful order', [
                            'vipreseller_status_id' => $vipResellerStatus->id,
                            'order_id' => $order->id,
                            'balance' => $balance,
                        ]);
                    } else {
                        Log::warning('Failed to fetch balance from VIP Reseller API', [
                            'vipreseller_status_id' => $vipResellerStatus->id,
                            'order_id' => $order->id,
                            'message' => $profileResult['message'] ?? 'Unknown error',
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error fetching balance from VIP Reseller API', [
                        'vipreseller_status_id' => $vipResellerStatus->id,
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
                
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
                    
                    // Update Telegram message if exists
                    if ($order->tlg_message_id) {
                        try {
                            $order->load('diamondPack', 'user', 'vipResellerStatuses');
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
            // STEP 0: IP Whitelist Check
            $allowedIPs = [
                '178.248.73.218', // VIP Reseller webhook IP
            ];
            
            $clientIP = $request->ip();
            if (!in_array($clientIP, $allowedIPs)) {
                Log::warning('VIP Reseller webhook: IP not whitelisted', [
                    'ip' => $clientIP,
                    'allowed_ips' => $allowedIPs,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized IP',
                ], 403);
            }
            
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

                // Fetch balance when status becomes success
                if ($mappedStatus === 'success' && $oldStatus !== 'success') {
                    try {
                        $vipReseller = new VipResellerService();
                        $profileResult = $vipReseller->getProfile();
                        
                        if ($profileResult['result'] === true && isset($profileResult['data']['balance'])) {
                            $balance = (string) $profileResult['data']['balance'];
                            $vipResellerStatus->balance = $balance;
                            $vipResellerStatus->save();
                            
                            Log::info('Balance fetched and saved from webhook for successful order', [
                                'vipreseller_status_id' => $vipResellerStatus->id,
                                'trxid' => $trxid,
                                'balance' => $balance,
                            ]);
                        } else {
                            Log::warning('Failed to fetch balance from VIP Reseller API (webhook)', [
                                'vipreseller_status_id' => $vipResellerStatus->id,
                                'trxid' => $trxid,
                                'message' => $profileResult['message'] ?? 'Unknown error',
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error fetching balance from VIP Reseller API (webhook)', [
                            'vipreseller_status_id' => $vipResellerStatus->id,
                            'trxid' => $trxid,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

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
                        
                        // Update Telegram message if exists
                        if ($order->tlg_message_id) {
                            try {
                                $order->load('diamondPack', 'user');
                                $updatedMessage = TelegramService::formatOrderMessage($order);
                                $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '⏳ <b>Order Confirmed - Waiting for VIP Reseller</b>', $updatedMessage);
                                TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                            } catch (\Exception $e) {
                                Log::error('Failed to update Telegram message', [
                                    'order_id' => $order->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
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
                        
                        // Update Telegram message if exists
                        if ($order->tlg_message_id) {
                            try {
                                $order->load('diamondPack', 'user');
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
    
    /**
     * Handle Telegram webhook (for button callbacks)
     */
    public function telegramWebhook(Request $request)
    {
        try {
            $data = $request->all();
            
            Log::info('Telegram webhook received', [
                'data' => $data,
            ]);
            
            // Handle callback query (button clicks)
            if (isset($data['callback_query'])) {
                $callbackQuery = $data['callback_query'];
                $callbackQueryId = $callbackQuery['id'];
                $callbackData = $callbackQuery['data'] ?? '';
                $message = $callbackQuery['message'] ?? null;
                $messageId = $message['message_id'] ?? null;
                
                if ($callbackData === 'confirm_order' && $messageId) {
                    // Find order by tlg_message_id
                    $order = Order::with(['diamondPack', 'user'])
                        ->where('tlg_message_id', $messageId)
                        ->first();
                    
                    if (!$order) {
                        TelegramService::answerCallbackQuery(
                            $callbackQueryId,
                            '❌ Order not found',
                            true
                        );
                        return response()->json(['ok' => true]);
                    }
                    
                    // Check if order is already completed
                    if ($order->status === 'completed') {
                        TelegramService::answerCallbackQuery(
                            $callbackQueryId,
                            '✅ Order is already completed',
                            false
                        );
                        return response()->json(['ok' => true]);
                    }
                    
                    // Check if VIP Reseller status is already success (prevent duplicate)
                    // Reload order with vipResellerStatuses relationship
                    $order->load('vipResellerStatuses');
                    $hasSuccessStatus = $order->vipResellerStatuses()
                        ->where('status', 'success')
                        ->exists();
                    
                    if ($hasSuccessStatus) {
                        TelegramService::answerCallbackQuery(
                            $callbackQueryId,
                            '⚠️ Order recharge already processed (VIP Reseller success)',
                            true
                        );
                        return response()->json(['ok' => true]);
                    }
                    
                    // Check conditions: pending_confirmation -> completed, has flexy_id
                    $oldStatus = $order->status;
                    $hasFlexyId = !is_null($order->flexy_id);
                    
                    if ($oldStatus !== 'pending_confirmation') {
                        TelegramService::answerCallbackQuery(
                            $callbackQueryId,
                            '❌ Order status must be pending_confirmation',
                            true
                        );
                        return response()->json(['ok' => true]);
                    }
                    
                    if (!$hasFlexyId) {
                        TelegramService::answerCallbackQuery(
                            $callbackQueryId,
                            '❌ Order does not have Flexy payment',
                            true
                        );
                        return response()->json(['ok' => true]);
                    }
                    
                    // Answer callback immediately
                    TelegramService::answerCallbackQuery(
                        $callbackQueryId,
                        '⏳ Processing order confirmation...',
                        false
                    );
                    
                    // Update order status to completed
                    $order->status = 'completed';
                    $order->save();
                    
                    Log::info('Telegram: Processing order confirmation', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'old_status' => $oldStatus,
                        'new_status' => 'completed',
                        'tlg_message_id' => $messageId,
                    ]);
                    
                    // Process recharge (same logic as admin dashboard)
                    $rechargeResult = $this->processRecharge($order);
                    
                    // Reload order to get updated status from processRecharge
                    $order->refresh();
                    $order->load('diamondPack', 'user', 'vipResellerStatuses');
                    
                    // Update Telegram message with proper header
                    $updatedMessage = TelegramService::formatOrderMessage($order);
                    
                    // Change header based on status
                    if ($order->status === 'completed') {
                        $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '✅ <b>Order Confirmed & Completed</b>', $updatedMessage);
                    } elseif ($order->status === 'sending') {
                        $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '⏳ <b>Order Confirmed - Processing Recharge</b>', $updatedMessage);
                        // Check VIP Reseller status
                        $latestVipStatus = $order->vipResellerStatuses()->latest()->first();
                        if ($latestVipStatus && $latestVipStatus->status === 'waiting') {
                            $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '⏳ <b>Order Confirmed - Waiting for VIP Reseller</b>', $updatedMessage);
                        }
                    }
                    
                    TelegramService::editMessageText($messageId, $updatedMessage);
                    
                    return response()->json(['ok' => true]);
                } elseif ($callbackData === 'cancel_order' && $messageId) {
                    // Find order by tlg_message_id
                    $order = Order::with(['diamondPack', 'user'])
                        ->where('tlg_message_id', $messageId)
                        ->first();
                    
                    if (!$order) {
                        TelegramService::answerCallbackQuery(
                            $callbackQueryId,
                            '❌ Order not found',
                            true
                        );
                        return response()->json(['ok' => true]);
                    }
                    
                    // Check if order can be cancelled (not already completed or cancelled)
                    if ($order->status === 'completed') {
                        TelegramService::answerCallbackQuery(
                            $callbackQueryId,
                            '❌ Cannot cancel completed order',
                            true
                        );
                        return response()->json(['ok' => true]);
                    }
                    
                    if ($order->status === 'cancelled') {
                        TelegramService::answerCallbackQuery(
                            $callbackQueryId,
                            '✅ Order is already cancelled',
                            false
                        );
                        return response()->json(['ok' => true]);
                    }
                    
                    // Answer callback immediately
                    TelegramService::answerCallbackQuery(
                        $callbackQueryId,
                        '⏳ Cancelling order...',
                        false
                    );
                    
                    // Update order status to cancelled
                    $oldStatus = $order->status;
                    $order->status = 'cancelled';
                    $order->save();
                    
                    Log::info('Telegram: Order cancelled', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'old_status' => $oldStatus,
                        'new_status' => 'cancelled',
                        'tlg_message_id' => $messageId,
                    ]);
                    
                    // Update Telegram message
                    $order->load('diamondPack', 'user');
                    $updatedMessage = TelegramService::formatOrderMessage($order);
                    $updatedMessage = str_replace(
                        '🆕 <b>New Order Created</b>',
                        '❌ <b>Order Cancelled</b>',
                        $updatedMessage
                    );
                    
                    TelegramService::editMessageText($messageId, $updatedMessage);
                    
                    return response()->json(['ok' => true]);
                } elseif ($callbackData === 'view_receipt' && $messageId) {
                    // Find order by tlg_message_id
                    $order = Order::with(['diamondPack', 'user', 'flexy'])
                        ->where('tlg_message_id', $messageId)
                        ->first();
                    
                    if (!$order) {
                        TelegramService::answerCallbackQuery(
                            $callbackQueryId,
                            '❌ Order not found',
                            true
                        );
                        return response()->json(['ok' => true]);
                    }
                    
                    // Check if order has Flexy receipt
                    if (!$order->flexy || !$order->flexy->receipt_image) {
                        TelegramService::answerCallbackQuery(
                            $callbackQueryId,
                            '❌ Receipt not found for this order',
                            true
                        );
                        return response()->json(['ok' => true]);
                    }
                    
                    // Answer callback immediately
                    TelegramService::answerCallbackQuery(
                        $callbackQueryId,
                        '📄 Sending receipt...',
                        false
                    );
                    
                    // Construct receipt URL (format: https://diaszone.com/storage/flexy_receipts/{filename})
                    $receiptPath = $order->flexy->receipt_image;
                    // Generate full URL using asset() helper
                    $receiptUrl = asset($receiptPath);
                    
                    // Send receipt photo
                    $caption = "📄 <b>Receipt for Order:</b> {$order->order_number}\n";
                    $caption .= "📦 <b>Pack:</b> " . ($order->diamondPack->name ?? 'N/A') . "\n";
                    $caption .= "💰 <b>Amount:</b> " . number_format(($order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260)), 0) . " DZD";
                    
                    TelegramService::sendPhoto($receiptUrl, $caption);
                    
                    Log::info('Telegram: Receipt sent', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'receipt_url' => $receiptUrl,
                    ]);
                    
                    return response()->json(['ok' => true]);
                }
            }
            
            // Handle text messages (commands)
            if (isset($data['message']['text'])) {
                $text = trim($data['message']['text']);
                
                // Handle /profit command
                if ($text === '/profit' || strtolower($text) === 'profit') {
                    try {
                        // Query all successful VIP Reseller statuses with balance not empty
                        $successfulOrders = VipResellerStatus::where('status', 'success')
                            ->whereNotNull('price')
                            ->whereNotNull('balance')
                            ->where('balance', '!=', '')
                            ->get();
                        
                        // Count failed orders with status 'error'
                        $failedOrdersCount = VipResellerStatus::where('status', 'error')->count();
                        
                        // Calculate total profit in IDR
                        $totalProfitIdr = $successfulOrders->sum('price');
                        
                        // Convert to USD (1 USD = 16600 IDR)
                        $totalProfitUsd = $totalProfitIdr / 16600;
                        
                        // Calculate total profit margin: 0.37 USD per successful order
                        $profitMarginPerOrder = 0.37; // USD
                        $totalProfitMargin = $successfulOrders->count() * $profitMarginPerOrder;
                        
                        // Get current balance from VIP Reseller API
                        $currentBalanceIdr = null;
                        $currentBalanceUsd = null;
                        try {
                            $vipReseller = new VipResellerService();
                            $profileResult = $vipReseller->getProfile();
                            
                            if ($profileResult['result'] === true && isset($profileResult['data']['balance'])) {
                                $currentBalanceIdr = $profileResult['data']['balance'];
                                // Convert to USD (1 USD = 16600 IDR)
                                $currentBalanceUsd = $currentBalanceIdr / 16600;
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to fetch current balance for profit command', [
                                'error' => $e->getMessage(),
                            ]);
                        }
                        
                        // Format the response message
                        $profitMessage = "💰 <b>Total Profit</b>\n\n";
                        $profitMessage .= "📊 <b>Successful Orders:</b> " . $successfulOrders->count() . "\n";
                        $profitMessage .= "❌ <b>Failed Orders:</b> " . $failedOrdersCount . "\n";
                        $profitMessage .= "💵 <b>Total (IDR):</b> " . number_format($totalProfitIdr, 2) . " IDR\n";
                        $profitMessage .= "💵 <b>Total (USD):</b> $" . number_format($totalProfitUsd, 2) . " USD\n";
                        $profitMessage .= "📈 <b>Total Profit Margin:</b> $" . number_format($totalProfitMargin, 2) . " USD\n";
                        
                        if ($currentBalanceIdr !== null) {
                            $profitMessage .= "💳 <b>Current Balance:</b> " . number_format($currentBalanceIdr, 0) . " IDR ($" . number_format($currentBalanceUsd, 2) . " USD)";
                        } else {
                            $profitMessage .= "💳 <b>Current Balance:</b> N/A";
                        }
                        
                        // Send profit message
                        TelegramService::sendMessage($profitMessage);
                        
                        Log::info('Telegram: Profit calculated via /profit command', [
                            'successful_orders_count' => $successfulOrders->count(),
                            'failed_orders_count' => $failedOrdersCount,
                            'total_profit_idr' => $totalProfitIdr,
                            'total_profit_usd' => $totalProfitUsd,
                            'total_profit_margin' => $totalProfitMargin,
                            'current_balance_idr' => $currentBalanceIdr,
                            'current_balance_usd' => $currentBalanceUsd,
                        ]);
                        
                        return response()->json(['ok' => true]);
                    } catch (\Exception $e) {
                        Log::error('Telegram: Error calculating profit from /profit command', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        
                        TelegramService::sendMessage('❌ Error calculating profit. Please try again.');
                        return response()->json(['ok' => true]);
                    }
                }
            }
            
            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

