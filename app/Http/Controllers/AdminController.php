<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\VipResellerStatus;
use App\Services\VipResellerService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Seller\SellerStorefrontController;
use Illuminate\Support\Facades\Crypt;

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
                    'product' => $packName,
                    'amount' => $amount,
                    'status' => $order->status,
                    'date' => $order->created_at,
                ];
            });

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
        
        // Provider Status Info (get latest)
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
     * List orders pending flexy verification
     */
    public function flexyApprovals(Request $request)
    {
        $orders = Order::with(['seller', 'diamondPack'])
            ->where('status', 'pending_flexy_verification')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.flexy-approvals', compact('orders'));
    }

    /**
     * Approve a Flexy order (mark processing and try to process)
     */
    public function approveFlexy(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        if ($order->status !== 'pending_flexy_verification') {
            return back()->with('error', 'Order not in pending flexy state.');
        }

        $order->status = 'processing';
        $order->save();

        // Try to process seller order (deduct wallet, complete)
        try {
            $processed = SellerStorefrontController::processSellerOrder($order);
            if ($processed) {
                $order->status = 'completed';
                // Credit seller profit if applicable
                try { if ($order->seller_id && !$order->seller_profit_paid) { $order->creditSellerProfit(); } } catch (\Throwable $e) { Log::warning('Admin: Failed to credit seller profit after flexy approval', ['order_id'=>$order->id,'error'=>$e->getMessage()]); }
            } else {
                $order->status = 'failed';
            }
            $order->save();
            return back()->with('success', 'Flexy order processed and updated.');
        } catch (\Exception $e) {
            Log::error('Failed to approve flexy order', ['order' => $order->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to process order.');
        }
    }

    /**
     * Reject a Flexy order (mark failed)
     */
    public function rejectFlexy(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        if (!in_array($order->status, ['pending_flexy_verification'])) {
            return back()->with('error', 'Order not in pending flexy state.');
        }

        $order->status = 'failed';
        $order->save();

        return back()->with('success', 'Flexy order rejected and marked failed.');
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

        // Broadcast status update so clients can react in realtime
        // No realtime broadcasting; webhook updates DB and clients should check DB when appropriate

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
            // Check if provider status already has success (prevent duplicate requests)
            $hasSuccessStatus = $order->vipResellerStatuses()
                ->where('status', 'success')
                ->exists();
            
            if ($hasSuccessStatus) {
                Log::info('Recharge skipped: provider status already success', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
                return [
                    'success' => true,
                    'message' => 'Recharge already processed (provider success)',
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

            $vipReseller = app(VipResellerService::class);
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

            // STEP 2: Call provider API to recharge (nickname already validated)
            if (config('services.digiflazz.username') || env('DIGIFLAZZ_USERNAME')) {
                $digService = app(\App\Services\DigiflazzService::class);
                $result = $digService->placeOrder($order->diamondPack, $order);
                // Normalize result to expected shape used below
                // DigiflazzService returns ['result'=>bool, 'data'=>..., 'message'=>...]
                $apiData = $result['data'] ?? [];
                $apiStatus = $apiData['status'] ?? ($apiData['rc'] ?? ($result['message'] ?? null));
            } else {
                $result = $vipReseller->placeOrder(
                    $packageCode,
                    $order->user_id_ml,
                    $order->zone_id_ml
                );
                $apiData = $result['data'] ?? [];
                $apiStatus = $apiData['status'] ?? 'error';
            }

            // STEP 3: Normalize response and map status to our enum
            $apiData = $result['data'] ?? [];
            $apiStatus = $apiData['status'] ?? ($apiData['rc'] ?? ($result['message'] ?? null));

            // Determine which service we used
            $serviceUsed = (config('services.digiflazz.username') || env('DIGIFLAZZ_USERNAME')) ? 'digiflazz' : 'vipreseller';

            // Map API status to our enum (waiting, success, error), supporting Digiflazz codes
            if ($serviceUsed === 'digiflazz') {
                $rc = isset($apiData['rc']) ? (string)$apiData['rc'] : null;
                $status = 'error';
                $lowerStatus = strtolower((string)($apiData['status'] ?? $apiStatus));
                if ($lowerStatus === 'sukses' || $rc === '00') {
                    $status = 'success';
                } elseif ($lowerStatus === 'pending' || in_array($rc, ['03', '99'])) {
                    $status = 'waiting';
                } else {
                    $status = 'error';
                }
            } else {
                $status = match(strtolower((string)$apiStatus)) {
                    'waiting' => 'waiting',
                    'success', 'completed', 'paid' => 'success',
                    default => 'error',
                };
            }
            
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

            // Save to vipreseller_status table (record either VIP or Digiflazz for admin view)
            $vipData = [
                'order_id' => $order->id,
                'trxid' => $apiData['trxid'] ?? null,
                'data' => $apiData['data'] ?? $order->user_id_ml,
                'zone' => $apiData['zone'] ?? $order->zone_id_ml,
                // legacy `service` saved inside additional_data for compatibility
                'status' => $status,
                'note' => $apiData['note'] ?? ($result['message'] ?? null),
                'price' => $apiData['price'] ?? null,
                'additional_data' => array_merge($additionalData, ['service' => $apiData['service'] ?? $packageCode ?? $serviceUsed]),
            ];

            if (!empty($vipData['trxid'])) {
                $vipResellerStatus = VipResellerStatus::updateOrCreate(['trxid' => $vipData['trxid']], $vipData);
            } else {
                $vipResellerStatus = VipResellerStatus::create($vipData);
            }

            Log::info('provider status saved', [
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
                            'provider status' => $status,
                    ]);
                    
                    // Credit seller profit if applicable
                    try {
                        if ($order->seller_id && !$order->seller_profit_paid) {
                            $order->creditSellerProfit();
                        }
                    } catch (\Exception $e) {
                        Log::warning('Admin: Failed to credit seller profit after VIP success', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                    }

                    // Update Telegram message if exists
                    if ($order->tlg_message_id) {
                        try {
                            $order->load('diamondPack', 'user', 'vipResellerStatuses');
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
                }
            } elseif ($status === 'success') {
                // Fetch balance from VIP Reseller API when status becomes success - only for VIP Reseller service
                if ($serviceUsed === 'vipreseller') {
                    try {
                        $vipReseller = app(VipResellerService::class);
                        $profileResult = $vipReseller->getProfile();

                        if ($profileResult['result'] === true && isset($profileResult['data']['balance'])) {
                            $balance = (string) $profileResult['data']['balance'];
                            $vipResellerStatus->balance = $balance;
                            $additional = $vipResellerStatus->additional_data ?? [];
                            $additional['balance'] = $balance;
                            $vipResellerStatus->additional_data = $additional;
                            $vipResellerStatus->save();

                            Log::info('Balance fetched and saved for successful order', [
                                'provider status id' => $vipResellerStatus->id,
                                'order_id' => $order->id,
                                'balance' => $balance,
                            ]);
                        } else {
                            Log::warning('Failed to fetch balance from VIP Reseller API', [
                                'provider status id' => $vipResellerStatus->id,
                                'order_id' => $order->id,
                                'message' => $profileResult['message'] ?? 'Unknown error',
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error fetching balance from provider API', [
                            'provider status id' => $vipResellerStatus->id,
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } elseif ($serviceUsed === 'digiflazz') {
                    try {
                        $digService = app(\App\Services\DigiflazzService::class);
                        $saldo = $digService->cekSaldo();
                        if ($saldo['result'] === true && isset($saldo['deposit'])) {
                            $balance = (string) $saldo['deposit'];
                            $vipResellerStatus->balance = $balance;
                            $additional = $vipResellerStatus->additional_data ?? [];
                            $additional['balance'] = $balance;
                            $vipResellerStatus->additional_data = $additional;
                            $vipResellerStatus->save();

                            Log::info('Digiflazz balance fetched and saved for successful order', [
                                'provider status id' => $vipResellerStatus->id,
                                'order_id' => $order->id,
                                'balance' => $balance,
                            ]);
                        } else {
                            Log::warning('Failed to fetch balance from Digiflazz', [
                                'provider status id' => $vipResellerStatus->id,
                                'order_id' => $order->id,
                                'response' => $saldo,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error fetching Digiflazz balance', [
                            'provider status id' => $vipResellerStatus->id,
                            'order_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                // Provider success - set order to completed
                if ($oldOrderStatus !== 'completed') {
                    $order->status = 'completed';
                    $order->save();
                    Log::info('Order status updated to completed (provider success)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'old_status' => $oldOrderStatus,
                            'new_status' => 'completed',
                            'provider status' => $status,
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
                // Provider error - ensure order is "sending" (needs attention)
                if ($oldOrderStatus === 'completed') {
                    $order->status = 'sending';
                    $order->save();
                    Log::warning('Order status updated to sending (provider error - needs attention)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'old_status' => $oldOrderStatus,
                            'new_status' => 'sending',
                            'provider status' => $status,
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
                    'status' => 'error',
                    'note' => 'Exception: ' . $e->getMessage(),
                    'price' => null,
                    'additional_data' => [
                        'exception' => $e->getMessage(),
                        'service' => $order->diamondPack->code ?? null,
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
     * Handle provider webhook for order status updates
     * 
     * Webhook receives status updates: waiting → processing → success/error
     * Signature verification: X-Client-Signature = md5(API_ID + API_KEY)
     */
    public function vipResellerWebhook(Request $request)
    {
        try {
            // STEP 0: IP Whitelist Check
            $allowedIPs = [
                '178.248.73.218', // provider webhook IP
            ];
            
            $clientIP = $request->ip();
            if (!in_array($clientIP, $allowedIPs)) {
                Log::warning('provider webhook: IP not whitelisted', [
                    'ip' => $clientIP,
                    'allowed_ips' => $allowedIPs,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized IP',
                ], 403);
            }
            
            // Log incoming webhook request
            Log::info('provider webhook received', [
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
                Log::warning('provider webhook signature verification failed', [
                    'received_signature' => $receivedSignature,
                    'expected_signature' => $expectedSignature,
                    'ip' => $request->ip(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 401);
            }

            Log::info('provider webhook signature verified successfully');

            // Get webhook data
            $webhookData = $request->input('data', []);
            
            if (empty($webhookData)) {
                Log::warning('provider webhook: Empty data received', [
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
                Log::warning('provider webhook: Missing trxid', [
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

            Log::info('provider webhook processing', [
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

                Log::info('provider status updated', [
                    'vipreseller_status_id' => $vipResellerStatus->id,
                    'trxid' => $trxid,
                    'old_status' => $oldStatus,
                    'new_status' => $mappedStatus,
                ]);

                // Fetch balance when status becomes success
                if ($mappedStatus === 'success' && $oldStatus !== 'success') {
                    // If this status belongs to Digiflazz, use Digiflazz cek-saldo endpoint
                    $serviceName = strtolower($vipResellerStatus->service ?? '');
                    if ((config('services.digiflazz.username') || env('DIGIFLAZZ_USERNAME')) && (str_contains($serviceName, 'digiflazz') || $serviceName === 'digiflazz')) {
                        try {
                            $digService = app(\App\Services\DigiflazzService::class);
                            $saldo = $digService->cekSaldo();
                            if ($saldo['result'] === true && isset($saldo['deposit'])) {
                                $balance = (string) $saldo['deposit'];
                                $vipResellerStatus->balance = $balance;
                                $additional = $vipResellerStatus->additional_data ?? [];
                                $additional['balance'] = $balance;
                                $vipResellerStatus->additional_data = $additional;
                                $vipResellerStatus->save();

                                Log::info('Digiflazz balance fetched and saved from webhook for successful order', [
                                    'vipreseller_status_id' => $vipResellerStatus->id,
                                    'trxid' => $trxid,
                                    'balance' => $balance,
                                ]);
                            } else {
                                Log::warning('Failed to fetch Digiflazz balance from webhook', [
                                    'vipreseller_status_id' => $vipResellerStatus->id,
                                    'trxid' => $trxid,
                                    'response' => $saldo,
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error fetching Digiflazz balance (webhook)', [
                                'vipreseller_status_id' => $vipResellerStatus->id,
                                'trxid' => $trxid,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } else {
                        try {
                            $vipReseller = app(VipResellerService::class);
                            $profileResult = $vipReseller->getProfile();
                            
                            if ($profileResult['result'] === true && isset($profileResult['data']['balance'])) {
                                $balance = (string) $profileResult['data']['balance'];
                                $vipResellerStatus->balance = $balance;
                                $additional = $vipResellerStatus->additional_data ?? [];
                                $additional['balance'] = $balance;
                                $vipResellerStatus->additional_data = $additional;
                                $vipResellerStatus->save();
                                
                                Log::info('Balance fetched and saved from webhook for successful order', [
                                    'vipreseller_status_id' => $vipResellerStatus->id,
                                    'trxid' => $trxid,
                                    'balance' => $balance,
                                ]);
                            } else {
                                Log::warning('Failed to fetch balance from provider API (webhook)', [
                                    'vipreseller_status_id' => $vipResellerStatus->id,
                                    'trxid' => $trxid,
                                    'message' => $profileResult['message'] ?? 'Unknown error',
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error fetching balance from provider API (webhook)', [
                                'vipreseller_status_id' => $vipResellerStatus->id,
                                'trxid' => $trxid,
                                'error' => $e->getMessage(),
                            ]);
                        }
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
                    // Try to find order by user_id_ml and zone_id_ml (Mobile Legends)
                    $order = Order::where('user_id_ml', $data)
                        ->where('zone_id_ml', $zone)
                        ->whereIn('status', ['sending', 'completed'])
                        ->latest()
                        ->first();
                    
                    // If not found, try Free Fire by player_id_ff
                    if (!$order && !empty($data)) {
                        $order = Order::where('player_id_ff', $data)
                            ->whereIn('status', ['sending', 'completed'])
                            ->latest()
                            ->first();
                    }
                    
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

                Log::info('provider status created from webhook', [
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
            Log::error('provider webhook exception: ' . $e->getMessage(), [
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
     * Update order status from provider webhook based on status
     * Handles: waiting, success, error
     */
    private function updateOrderFromWebhook(VipResellerStatus $vipResellerStatus, array $webhookData)
    {
        try {
            // Get order using relationship
            $order = $vipResellerStatus->order;
            
            if (!$order) {
                // Try to find order by user_id_ml and zone_id_ml as fallback (Mobile Legends)
                $order = Order::where('user_id_ml', $vipResellerStatus->data)
                    ->where('zone_id_ml', $vipResellerStatus->zone)
                    ->whereIn('status', ['sending', 'completed'])
                    ->latest()
                    ->first();
                
                // If not found, try Free Fire by player_id_ff (no zone required)
                if (!$order && !empty($vipResellerStatus->data)) {
                    $order = Order::where('player_id_ff', $vipResellerStatus->data)
                        ->whereIn('status', ['sending', 'completed'])
                        ->latest()
                        ->first();
                }
                
                // If found, update the vipreseller_status with order_id
                if ($order) {
                    $vipResellerStatus->order_id = $order->id;
                    $vipResellerStatus->save();
                }
            }

            if ($order) {
                $oldOrderStatus = $order->status;
                $vipStatus = $vipResellerStatus->status;
                
                Log::info('Order found for provider webhook update', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'trxid' => $vipResellerStatus->trxid,
                    'provider_status' => $vipStatus,
                    'current_order_status' => $oldOrderStatus,
                ]);

                // Update order status based on provider webhook status
                // Flow: Chargily/Flexy paid → "sending" → provider webhook updates → final status
                
                if ($vipStatus === 'waiting') {
                    // Provider is processing the topup
                    // Set order status to "sending" (payment done, waiting for diamonds topup)
                    if ($oldOrderStatus !== 'sending') {
                        $order->status = 'sending';
                        $order->save();
                        
                        Log::info('Order status updated to sending (provider waiting - payment done, waiting for topup)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'old_status' => $oldOrderStatus,
                            'new_status' => 'sending',
                            'provider_status' => $vipStatus,
                        ]);
                        
                        // Update Telegram message if exists
                        if ($order->tlg_message_id) {
                            try {
                                $order->load('diamondPack', 'user');
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
                    } else {
                        Log::info('Order status already sending (provider waiting)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'order_status' => $oldOrderStatus,
                            'provider_status' => $vipStatus,
                        ]);
                    }
                }
                elseif ($vipStatus === 'success') {
                    // Provider successfully delivered the topup
                    // Change order status to "completed" (everything done)
                    if ($oldOrderStatus !== 'completed') {
                        $order->status = 'completed';
                        $order->save();
                        
                        Log::info('Order status updated to completed (provider success - topup delivered)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'old_status' => $oldOrderStatus,
                            'new_status' => 'completed',
                            'provider_status' => $vipStatus,
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
                        Log::info('Order status already completed (provider success)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'order_status' => $oldOrderStatus,
                            'provider_status' => $vipStatus,
                        ]);
                    }
                }
                elseif ($vipStatus === 'error') {
                    // Provider failed to deliver the topup
                    // Keep order as "sending" to indicate it needs attention (payment done, but topup failed)
                    if ($oldOrderStatus === 'completed') {
                        // Change from completed to sending (needs attention)
                        $order->status = 'sending';
                        $order->save();
                        
                        // Add error note to order
                        $errorNote = 'Provider topup error: ' . ($vipResellerStatus->note ?? 'Unknown error');
                        if (!empty($order->notes)) {
                            $order->notes = $order->notes . "\n" . $errorNote;
                        } else {
                            $order->notes = $errorNote;
                        }
                        $order->save();
                        
                        Log::warning('Order status updated to sending (provider error - payment done but topup failed, needs attention)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'old_status' => $oldOrderStatus,
                            'new_status' => 'sending',
                            'provider_status' => $vipStatus,
                            'provider_note' => $vipResellerStatus->note,
                        ]);
                    } else {
                        Log::warning('Provider topup error (order already in sending status)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'order_status' => $oldOrderStatus,
                            'provider_status' => $vipStatus,
                            'provider_note' => $vipResellerStatus->note,
                        ]);
                    }
                }
            } else {
                Log::info('No matching order found for provider webhook', [
                    'vipreseller_status_id' => $vipResellerStatus->id,
                    'data' => $vipResellerStatus->data,
                    'zone' => $vipResellerStatus->zone,
                    'trxid' => $vipResellerStatus->trxid,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating order from provider webhook: ' . $e->getMessage(), [
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

                    // Process recharge (same logic as admin dashboard)
                    $rechargeResult = $this->processRecharge($order);

                    // Decide order status based on recharge result
                    // If provider returned final success -> completed
                    // If provider returned waiting/pending -> sending (await webhook)
                    // If provider returned error -> sending (requires attention)
                    $newStatus = $order->status; // default keep existing if unchanged
                    if (isset($rechargeResult['status'])) {
                        if ($rechargeResult['status'] === 'success') {
                            $newStatus = 'completed';
                        } elseif ($rechargeResult['status'] === 'waiting') {
                            $newStatus = 'sending';
                        } else {
                            // error or unknown
                            $newStatus = 'sending';
                        }
                    }

                    $order->status = $newStatus;
                    $order->save();

                    Log::info('Telegram: Processing order confirmation', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'tlg_message_id' => $messageId,
                        'recharge_result' => $rechargeResult,
                    ]);
                    // If order completed after processing, credit seller profit
                    try {
                        if ($order->status === 'completed' && $order->seller_id && !$order->seller_profit_paid) {
                            $order->creditSellerProfit();
                        }
                    } catch (\Exception $e) {
                        Log::warning('Admin: Failed to credit seller profit after processing recharge', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                    }
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
                            $vipReseller = app(VipResellerService::class);
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
                
                // ==================== COUPON COMMANDS ====================
                
                // /coupon help - Show available commands
                if ($text === '/coupon' || $text === '/coupon help') {
                    $helpMessage = "🎟️ <b>Coupon Commands</b>\n\n";
                    $helpMessage .= "📝 <b>Create Coupon:</b>\n";
                    $helpMessage .= "<code>/coupon create CODE 10% all</code>\n";
                    $helpMessage .= "<code>/coupon create CODE 500dzd mlbb</code>\n";
                    $helpMessage .= "<code>/coupon create CODE 100% mlbb:5,10</code>\n\n";
                    $helpMessage .= "📋 <b>List Coupons:</b>\n";
                    $helpMessage .= "<code>/coupon list</code>\n\n";
                    $helpMessage .= "🔍 <b>View Coupon:</b>\n";
                    $helpMessage .= "<code>/coupon view CODE</code>\n\n";
                    $helpMessage .= "🚫 <b>Disable Coupon:</b>\n";
                    $helpMessage .= "<code>/coupon disable CODE</code>\n\n";
                    $helpMessage .= "✅ <b>Enable Coupon:</b>\n";
                    $helpMessage .= "<code>/coupon enable CODE</code>\n\n";
                    $helpMessage .= "🗑️ <b>Delete Coupon:</b>\n";
                    $helpMessage .= "<code>/coupon delete CODE</code>\n\n";
                    $helpMessage .= "📊 <b>Coupon Stats:</b>\n";
                    $helpMessage .= "<code>/coupon stats CODE</code>\n\n";
                    $helpMessage .= "⚙️ <b>Options:</b>\n";
                    $helpMessage .= "• <code>--max=10</code> Max total uses\n";
                    $helpMessage .= "• <code>--peruser=1</code> Max per user\n";
                    $helpMessage .= "• <code>--expires=7d</code> Expires in 7 days\n";
                    $helpMessage .= "• <code>--min=500</code> Min order amount";
                    
                    TelegramService::sendMessage($helpMessage);
                    return response()->json(['ok' => true]);
                }
                
                // /coupon create CODE DISCOUNT TARGET [options]
                if (preg_match('/^\/coupon create (\S+) (\d+)(%)?(dzd)? (.+)$/i', $text, $matches)) {
                    try {
                        $code = strtoupper(trim($matches[1]));
                        $discountValue = (float) $matches[2];
                        $isPercentage = !empty($matches[3]);
                        $isFixed = !empty($matches[4]);
                        $targetAndOptions = trim($matches[5]);
                        
                        // Check if coupon already exists
                        if (Coupon::where('code', $code)->exists()) {
                            TelegramService::sendMessage("❌ Coupon <code>{$code}</code> already exists.");
                            return response()->json(['ok' => true]);
                        }
                        
                        // Parse target and options
                        $parts = explode(' ', $targetAndOptions);
                        $target = strtolower($parts[0]); // all, mlbb, freefire, mlbb:5,10
                        
                        // Parse options
                        $options = [
                            'max_uses' => null,
                            'max_uses_per_user' => 1,
                            'expires_at' => null,
                            'min_order_amount' => null,
                        ];
                        
                        foreach ($parts as $part) {
                            if (preg_match('/^--max=(\d+)$/i', $part, $m)) {
                                $options['max_uses'] = (int) $m[1];
                            } elseif (preg_match('/^--peruser=(\d+)$/i', $part, $m)) {
                                $options['max_uses_per_user'] = (int) $m[1];
                            } elseif (preg_match('/^--expires=(\d+)([dhm])$/i', $part, $m)) {
                                $value = (int) $m[1];
                                $unit = strtolower($m[2]);
                                if ($unit === 'd') {
                                    $options['expires_at'] = now()->addDays($value);
                                } elseif ($unit === 'h') {
                                    $options['expires_at'] = now()->addHours($value);
                                } elseif ($unit === 'm') {
                                    $options['expires_at'] = now()->addMinutes($value);
                                }
                            } elseif (preg_match('/^--min=(\d+)$/i', $part, $m)) {
                                $options['min_order_amount'] = (float) $m[1];
                            }
                        }
                        
                        // Parse target
                        $appliesTo = 'all';
                        $allowedGames = null;
                        $allowedPackages = null;
                        
                        if ($target !== 'all') {
                            $appliesTo = 'specific';
                            
                            // Check if specific packages: mlbb:5,10,15
                            if (strpos($target, ':') !== false) {
                                list($game, $packages) = explode(':', $target, 2);
                                $allowedGames = [$game];
                                $allowedPackages = array_map('intval', explode(',', $packages));
                            } else {
                                // Just game: mlbb, freefire, pubg
                                $allowedGames = [$target];
                            }
                        }
                        
                        // Create coupon
                        $coupon = Coupon::create([
                            'code' => $code,
                            'discount_type' => $isPercentage ? 'percentage' : 'fixed',
                            'discount_value' => $discountValue,
                            'applies_to' => $appliesTo,
                            'allowed_games' => $allowedGames,
                            'allowed_packages' => $allowedPackages,
                            'max_uses' => $options['max_uses'],
                            'max_uses_per_user' => $options['max_uses_per_user'],
                            'expires_at' => $options['expires_at'],
                            'min_order_amount' => $options['min_order_amount'],
                            'is_active' => true,
                            'created_by' => 'Telegram',
                        ]);
                        
                        $discountText = $isPercentage ? "{$discountValue}%" : "{$discountValue} DZD";
                        $targetText = $target === 'all' ? 'All products' : $target;
                        
                        $successMessage = "✅ <b>Coupon Created!</b>\n\n";
                        $successMessage .= "🎟️ <b>Code:</b> <code>{$code}</code>\n";
                        $successMessage .= "💰 <b>Discount:</b> {$discountText}\n";
                        $successMessage .= "🎯 <b>Target:</b> {$targetText}\n";
                        if ($options['max_uses']) {
                            $successMessage .= "🔢 <b>Max Uses:</b> {$options['max_uses']}\n";
                        }
                        $successMessage .= "👤 <b>Per User:</b> {$options['max_uses_per_user']}\n";
                        if ($options['expires_at']) {
                            $successMessage .= "⏰ <b>Expires:</b> " . $options['expires_at']->format('Y-m-d H:i') . "\n";
                        }
                        if ($options['min_order_amount']) {
                            $successMessage .= "💵 <b>Min Order:</b> {$options['min_order_amount']} DZD";
                        }
                        
                        TelegramService::sendMessage($successMessage);
                        return response()->json(['ok' => true]);
                        
                    } catch (\Exception $e) {
                        Log::error('Telegram: Error creating coupon', ['error' => $e->getMessage()]);
                        TelegramService::sendMessage("❌ Error creating coupon: " . $e->getMessage());
                        return response()->json(['ok' => true]);
                    }
                }
                
                // /coupon list
                if ($text === '/coupon list') {
                    try {
                        $coupons = Coupon::orderBy('created_at', 'desc')->take(20)->get();
                        
                        if ($coupons->isEmpty()) {
                            TelegramService::sendMessage("📋 No coupons found.");
                            return response()->json(['ok' => true]);
                        }
                        
                        $listMessage = "📋 <b>Coupons List</b>\n\n";
                        
                        foreach ($coupons as $coupon) {
                            $status = $coupon->is_active ? '✅' : '❌';
                            $discountText = $coupon->discount_type === 'percentage' 
                                ? "{$coupon->discount_value}%" 
                                : "{$coupon->discount_value} DZD";
                            
                            $listMessage .= "{$status} <code>{$coupon->code}</code> - {$discountText}";
                            $listMessage .= " ({$coupon->used_count}";
                            if ($coupon->max_uses) {
                                $listMessage .= "/{$coupon->max_uses}";
                            }
                            $listMessage .= " uses)\n";
                        }
                        
                        TelegramService::sendMessage($listMessage);
                        return response()->json(['ok' => true]);
                        
                    } catch (\Exception $e) {
                        Log::error('Telegram: Error listing coupons', ['error' => $e->getMessage()]);
                        TelegramService::sendMessage("❌ Error listing coupons.");
                        return response()->json(['ok' => true]);
                    }
                }
                
                // /coupon view CODE
                if (preg_match('/^\/coupon view (\S+)$/i', $text, $matches)) {
                    try {
                        $code = strtoupper(trim($matches[1]));
                        $coupon = Coupon::where('code', $code)->first();
                        
                        if (!$coupon) {
                            TelegramService::sendMessage("❌ Coupon <code>{$code}</code> not found.");
                            return response()->json(['ok' => true]);
                        }
                        
                        $status = $coupon->is_active ? '✅ Active' : '❌ Disabled';
                        $discountText = $coupon->discount_type === 'percentage' 
                            ? "{$coupon->discount_value}%" 
                            : "{$coupon->discount_value} DZD";
                        
                        $viewMessage = "🎟️ <b>Coupon Details</b>\n\n";
                        $viewMessage .= "📝 <b>Code:</b> <code>{$coupon->code}</code>\n";
                        $viewMessage .= "📊 <b>Status:</b> {$status}\n";
                        $viewMessage .= "💰 <b>Discount:</b> {$discountText}\n";
                        $viewMessage .= "🎯 <b>Applies to:</b> {$coupon->applies_to}\n";
                        
                        if ($coupon->allowed_games) {
                            $viewMessage .= "🎮 <b>Games:</b> " . implode(', ', $coupon->allowed_games) . "\n";
                        }
                        if ($coupon->allowed_packages) {
                            $viewMessage .= "📦 <b>Packages:</b> " . implode(', ', $coupon->allowed_packages) . "\n";
                        }
                        
                        $viewMessage .= "🔢 <b>Used:</b> {$coupon->used_count}";
                        if ($coupon->max_uses) {
                            $viewMessage .= " / {$coupon->max_uses}";
                        }
                        $viewMessage .= "\n";
                        $viewMessage .= "👤 <b>Per User:</b> {$coupon->max_uses_per_user}\n";
                        
                        if ($coupon->min_order_amount) {
                            $viewMessage .= "💵 <b>Min Order:</b> {$coupon->min_order_amount} DZD\n";
                        }
                        if ($coupon->expires_at) {
                            $viewMessage .= "⏰ <b>Expires:</b> " . $coupon->expires_at->format('Y-m-d H:i') . "\n";
                        }
                        
                        $viewMessage .= "📅 <b>Created:</b> " . $coupon->created_at->format('Y-m-d H:i');
                        
                        TelegramService::sendMessage($viewMessage);
                        return response()->json(['ok' => true]);
                        
                    } catch (\Exception $e) {
                        Log::error('Telegram: Error viewing coupon', ['error' => $e->getMessage()]);
                        TelegramService::sendMessage("❌ Error viewing coupon.");
                        return response()->json(['ok' => true]);
                    }
                }
                
                // /coupon disable CODE
                if (preg_match('/^\/coupon disable (\S+)$/i', $text, $matches)) {
                    try {
                        $code = strtoupper(trim($matches[1]));
                        $coupon = Coupon::where('code', $code)->first();
                        
                        if (!$coupon) {
                            TelegramService::sendMessage("❌ Coupon <code>{$code}</code> not found.");
                            return response()->json(['ok' => true]);
                        }
                        
                        $coupon->is_active = false;
                        $coupon->save();
                        
                        TelegramService::sendMessage("🚫 Coupon <code>{$code}</code> has been disabled.");
                        return response()->json(['ok' => true]);
                        
                    } catch (\Exception $e) {
                        Log::error('Telegram: Error disabling coupon', ['error' => $e->getMessage()]);
                        TelegramService::sendMessage("❌ Error disabling coupon.");
                        return response()->json(['ok' => true]);
                    }
                }
                
                // /coupon enable CODE
                if (preg_match('/^\/coupon enable (\S+)$/i', $text, $matches)) {
                    try {
                        $code = strtoupper(trim($matches[1]));
                        $coupon = Coupon::where('code', $code)->first();
                        
                        if (!$coupon) {
                            TelegramService::sendMessage("❌ Coupon <code>{$code}</code> not found.");
                            return response()->json(['ok' => true]);
                        }
                        
                        $coupon->is_active = true;
                        $coupon->save();
                        
                        TelegramService::sendMessage("✅ Coupon <code>{$code}</code> has been enabled.");
                        return response()->json(['ok' => true]);
                        
                    } catch (\Exception $e) {
                        Log::error('Telegram: Error enabling coupon', ['error' => $e->getMessage()]);
                        TelegramService::sendMessage("❌ Error enabling coupon.");
                        return response()->json(['ok' => true]);
                    }
                }
                
                // /coupon delete CODE
                if (preg_match('/^\/coupon delete (\S+)$/i', $text, $matches)) {
                    try {
                        $code = strtoupper(trim($matches[1]));
                        $coupon = Coupon::where('code', $code)->first();
                        
                        if (!$coupon) {
                            TelegramService::sendMessage("❌ Coupon <code>{$code}</code> not found.");
                            return response()->json(['ok' => true]);
                        }
                        
                        $coupon->delete();
                        
                        TelegramService::sendMessage("🗑️ Coupon <code>{$code}</code> has been deleted.");
                        return response()->json(['ok' => true]);
                        
                    } catch (\Exception $e) {
                        Log::error('Telegram: Error deleting coupon', ['error' => $e->getMessage()]);
                        TelegramService::sendMessage("❌ Error deleting coupon.");
                        return response()->json(['ok' => true]);
                    }
                }
                
                // /coupon stats CODE
                if (preg_match('/^\/coupon stats (\S+)$/i', $text, $matches)) {
                    try {
                        $code = strtoupper(trim($matches[1]));
                        $coupon = Coupon::with('usages.user')->where('code', $code)->first();
                        
                        if (!$coupon) {
                            TelegramService::sendMessage("❌ Coupon <code>{$code}</code> not found.");
                            return response()->json(['ok' => true]);
                        }
                        
                        $totalDiscount = $coupon->usages->sum('discount_applied');
                        $uniqueUsers = $coupon->usages->pluck('user_id')->unique()->count();
                        
                        $statsMessage = "📊 <b>Coupon Stats: {$code}</b>\n\n";
                        $statsMessage .= "🔢 <b>Total Uses:</b> {$coupon->used_count}\n";
                        $statsMessage .= "👥 <b>Unique Users:</b> {$uniqueUsers}\n";
                        $statsMessage .= "💰 <b>Total Discount Given:</b> " . number_format($totalDiscount, 2) . " DZD\n\n";
                        
                        if ($coupon->usages->count() > 0) {
                            $statsMessage .= "<b>Recent Uses:</b>\n";
                            foreach ($coupon->usages->take(5) as $usage) {
                                $userName = $usage->user ? $usage->user->name : 'Unknown';
                                $statsMessage .= "• {$userName} - " . number_format($usage->discount_applied, 2) . " DZD (" . $usage->created_at->format('M d') . ")\n";
                            }
                        }
                        
                        TelegramService::sendMessage($statsMessage);
                        return response()->json(['ok' => true]);
                        
                    } catch (\Exception $e) {
                        Log::error('Telegram: Error getting coupon stats', ['error' => $e->getMessage()]);
                        TelegramService::sendMessage("❌ Error getting coupon stats.");
                        return response()->json(['ok' => true]);
                    }
                }
                
                // ==================== END COUPON COMMANDS ====================
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

