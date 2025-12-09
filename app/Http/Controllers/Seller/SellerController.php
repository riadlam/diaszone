<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\DiamondPack;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SellerGamePrice;
use App\Services\VipResellerService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SellerController extends Controller
{
    protected $vipResellerService;
    protected $telegramService;

    public function __construct(VipResellerService $vipResellerService, TelegramService $telegramService)
    {
        $this->vipResellerService = $vipResellerService;
        $this->telegramService = $telegramService;
    }

    /**
     * Get authenticated seller
     */
    protected function seller(): Seller
    {
        return Auth::guard('seller')->user();
    }

    /**
     * Seller dashboard
     */
    public function dashboard()
    {
        $seller = $this->seller();

        // Get statistics
        $todayOrders = $seller->orders()->whereDate('created_at', today())->count();
        $totalOrders = $seller->orders()->count();
        $completedOrders = $seller->orders()->where('status', 'completed')->count();
        $pendingOrders = $seller->orders()->whereIn('status', ['pending', 'processing'])->count();

        // Revenue stats
        $todayRevenue = $seller->orders()
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('seller_profit');

        $monthRevenue = $seller->orders()
            ->whereMonth('created_at', now()->month)
            ->where('status', 'completed')
            ->sum('seller_profit');

        // Recent orders
        $recentOrders = $seller->orders()
            ->with(['diamondPack', 'user'])
            ->latest()
            ->take(10)
            ->get();

        // Recent transactions
        $recentTransactions = $seller->walletTransactions()
            ->latest()
            ->take(5)
            ->get();

        // Chart data - last 7 days
        $chartData = $this->getChartData($seller, 7);

        return view('seller.dashboard', compact(
            'seller',
            'todayOrders',
            'totalOrders',
            'completedOrders',
            'pendingOrders',
            'todayRevenue',
            'monthRevenue',
            'recentOrders',
            'recentTransactions',
            'chartData'
        ));
    }

    /**
     * Get chart data for last N days
     */
    protected function getChartData(Seller $seller, int $days): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $orders = $seller->orders()
                ->whereDate('created_at', $date)
                ->where('status', 'completed')
                ->count();
            $revenue = $seller->orders()
                ->whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('seller_profit');

            $data[] = [
                'date' => $date->format('M d'),
                'orders' => $orders,
                'revenue' => (float) $revenue,
            ];
        }
        return $data;
    }

    /**
     * Show packs pricing page
     */
    public function packs(Request $request)
    {
        $seller = $this->seller();
        $gameType = $request->get('game', 'mobilelegends');

        // Get available game types
        $gameTypes = DiamondPack::where('is_active', true)
            ->select('game_type')
            ->distinct()
            ->pluck('game_type');

        // Filter by allowed games
        if (!empty($seller->allowed_games)) {
            $gameTypes = $gameTypes->intersect($seller->allowed_games);
        }

        // Get packs for selected game
        $packs = DiamondPack::where('game_type', $gameType)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('diamonds')
            ->get();

        // Get seller's custom prices
        $sellerPrices = $seller->gamePrices()
            ->whereIn('diamond_pack_id', $packs->pluck('id'))
            ->get()
            ->keyBy('diamond_pack_id');

        return view('seller.packs', compact('seller', 'gameTypes', 'gameType', 'packs', 'sellerPrices'));
    }

    /**
     * Update pack prices
     */
    public function updatePrices(Request $request)
    {
        $seller = $this->seller();

        $validated = $request->validate([
            'prices' => 'required|array',
            'prices.*.pack_id' => 'required|exists:diamond_packs,id',
            'prices.*.price_dzd' => 'required|numeric|min:0',
            // flexy_price may be left empty for sellers who don't use Flexy — validate if present
            'prices.*.flexy_price' => 'nullable|numeric|min:0',
            'prices.*.is_active' => 'boolean',
        ]);

        foreach ($validated['prices'] as $priceData) {
            $pack = DiamondPack::find($priceData['pack_id']);

            // Validate minimum price based on the pack base price (DZD)
            $baseDzd = $pack->base_price_dzd ?? $pack->price_dzd;

            if ($priceData['price_dzd'] < $baseDzd) {
                return back()->withErrors([
                    'prices' => "Price for {$pack->name} must be at least {$baseDzd} DZD"
                ]);
            }

            // If seller provided a Flexy price (DZD), ensure it is not lower than the base price (DZD)
            if (!empty($priceData['flexy_price']) && is_numeric($priceData['flexy_price'])) {
                if ($priceData['flexy_price'] < $baseDzd) {
                    return back()->withErrors([
                        'prices' => "Flexy price for {$pack->name} must be at least {$baseDzd} DZD"
                    ]);
                }
            }

            $updateData = [
                'custom_price_dzd' => $priceData['price_dzd'],
                'flexy_price' => $priceData['flexy_price'] ?? null,
                // ensure is_active is stored as boolean/int (1/0)
                'is_active' => isset($priceData['is_active']) ? (bool) $priceData['is_active'] : true,
            ];

            // Only update custom_price_usd if provided (we don't want to null out existing values)
            if (array_key_exists('price_usd', $priceData)) {
                $updateData['custom_price_usd'] = $priceData['price_usd'];
            }

            SellerGamePrice::updateOrCreate(
                [
                    'seller_id' => $seller->id,
                    'diamond_pack_id' => $pack->id,
                ],
                $updateData
            );
        }

        return back()->with('success', 'Prices updated successfully!');
    }

    /**
     * Wallet page
     */
    public function wallet()
    {
        $seller = $this->seller();

        $transactions = $seller->walletTransactions()
            ->latest()
            ->paginate(20);

        $totalCredits = $seller->walletTransactions()->credits()->sum('amount');
        $totalDebits = $seller->walletTransactions()->debits()->sum('amount');

        // Top-up requests (pending) summary
        $pendingTopups = $seller->walletRechargeAsks()->where('status', 'pending')->get();
        $pendingTopupsSum = $pendingTopups->sum('amount');

        return view('seller.wallet', compact('seller', 'transactions', 'totalCredits', 'totalDebits', 'pendingTopups', 'pendingTopupsSum'));
    }

    /**
     * Orders page
     */
    public function orders(Request $request)
    {
        $seller = $this->seller();

        $query = $seller->orders()->with(['diamondPack', 'user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by game
        if ($request->filled('game')) {
            $query->whereHas('diamondPack', function ($q) use ($request) {
                $q->where('game_type', $request->game);
            });
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Paginate the result set for the page
        // Show 10 rows per page for seller orders
        $orders = $query->latest()->paginate(10);

        // Global counts (not limited to the current page) for the seller's orders
        $globalQuery = $seller->orders();
        $totalOrdersCount = $globalQuery->count();
        $pendingFlexyCount = $globalQuery->where('status', 'pending_flexy_verification')->count();
        $processingCount = $globalQuery->where('status', 'processing')->count();
        $completedCount = $globalQuery->where('status', 'completed')->count();

        return view('seller.orders', compact(
            'seller', 'orders',
            'totalOrdersCount', 'pendingFlexyCount', 'processingCount', 'completedCount'
        ));
    }

    /**
     * Statistics page
     */
    public function statistics()
    {
        $seller = $this->seller();

        // Overall stats
        $stats = [
            'total_orders' => $seller->orders()->count(),
            'completed_orders' => $seller->orders()->where('status', 'completed')->count(),
            'total_revenue' => $seller->total_sales,
            'total_profit' => $seller->total_earnings,
            'wallet_balance' => $seller->wallet_balance,
        ];

        // Monthly breakdown
        $monthlyStats = $seller->orders()
            ->where('status', 'completed')
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as orders, SUM(seller_profit) as profit')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        // Top selling packs
        $topPacks = $seller->orders()
            ->where('status', 'completed')
            ->with('diamondPack')
            ->selectRaw('diamond_pack_id, COUNT(*) as count, SUM(seller_profit) as profit')
            ->groupBy('diamond_pack_id')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();

        // Chart data - last 30 days
        $chartData = $this->getChartData($seller, 30);

        return view('seller.statistics', compact('seller', 'stats', 'monthlyStats', 'topPacks', 'chartData'));
    }

    /**
     * Direct top-up page
     */
    public function directTopup()
    {
        $seller = $this->seller();

        // Get available game types
        $gameTypes = DiamondPack::where('is_active', true)
            ->select('game_type')
            ->distinct()
            ->pluck('game_type');

        // Filter by allowed games
        if (!empty($seller->allowed_games)) {
            $gameTypes = $gameTypes->intersect($seller->allowed_games);
        }

        return view('seller.direct-topup', compact('seller', 'gameTypes'));
    }

    /**
     * Get packs for a game (API)
     */
    public function getGamePacks(Request $request)
    {
        $seller = $this->seller();
        $gameType = $request->get('game_type');

        // Check if seller can sell this game
        if (!$seller->canSellGame($gameType)) {
            return response()->json(['error' => 'You are not allowed to sell this game'], 403);
        }

        $packs = DiamondPack::where('game_type', $gameType)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('diamonds')
            ->get();

        // Add seller's custom prices
        $sellerPrices = $seller->gamePrices()
            ->whereIn('diamond_pack_id', $packs->pluck('id'))
            ->get()
            ->keyBy('diamond_pack_id');

        $packsData = $packs->map(function ($pack) use ($sellerPrices) {
            $customPrice = $sellerPrices->get($pack->id);
            return [
                'id' => $pack->id,
                'name' => $pack->name,
                'code' => $pack->code,
                'diamonds' => $pack->diamonds,
                'bonus_diamonds' => $pack->bonus_diamonds,
                'total_diamonds' => $pack->total_diamonds,
                'base_price_dzd' => $pack->base_price_dzd ?? $pack->price_dzd,
                'base_price_usd' => $pack->price_usd,
                'seller_price_dzd' => $customPrice ? $customPrice->custom_price_dzd : $pack->price_dzd,
                'seller_price_usd' => $customPrice ? $customPrice->custom_price_usd : $pack->price_usd,
                'is_active' => $customPrice ? $customPrice->is_active : true,
            ];
        });

        return response()->json($packsData);
    }

    /**
     * Process direct top-up order
     */
    public function processDirectTopup(Request $request)
    {
        $seller = $this->seller();

        $validated = $request->validate([
            'game_type' => 'required|string',
            'pack_id' => 'required|exists:diamond_packs,id',
            'player_id' => 'required|string',
            'zone_id' => 'nullable|string',
        ]);

        $pack = DiamondPack::findOrFail($validated['pack_id']);

        // Check if seller can sell this game
        if (!$seller->canSellGame($pack->game_type)) {
            return back()->withErrors(['error' => 'You are not allowed to sell this game']);
        }

        // Get seller's base cost (original pack price). Prefer base_price_dzd when available.
        $baseCost = $pack->base_price_dzd ?? $pack->price_dzd;

        // Note: intentionally not applying a rate limit here so high-volume trusted sellers
        // can perform many direct top-ups in quick succession. We still keep a cache lock
        // per (seller,pack,player) to protect against duplicate submissions.
        // Ensure seller has at least the base cost available before placing order
        if ($seller->wallet_balance < $baseCost) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Insufficient wallet balance. You need ' . $baseCost . ' DZD'], 400)
                : back()->withErrors(['error' => 'Insufficient wallet balance. You need ' . $baseCost . ' DZD']);
        }

        // Get seller's custom price
        $customPrice = $seller->getCustomPrice($pack->id);
        $sellingPrice = $customPrice ? $customPrice->custom_price_dzd : $pack->price_dzd;
        $profit = $sellingPrice - $baseCost;

        try {
            // Capture seller balance before any changes for reporting
            $walletBefore = (float) $seller->wallet_balance;
            // Prevent duplicate submissions using cache lock
            $lockKey = 'seller_direct_topup_lock:' . $seller->id . ':' . $pack->id . ':' . md5($validated['player_id']);
            $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 10);

            if (!$lock->get()) {
                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => 'Another top-up is processing for this player — please wait.'], 423)
                    : back()->withErrors(['error' => 'Another top-up is processing for this player — please wait.']);
            }

            DB::beginTransaction();

            // create order placeholder (will be updated)
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'seller_id' => $seller->id,
                'diamond_pack_id' => $pack->id,
                'status' => 'processing',
                'user_id_ml' => $validated['game_type'] === 'mobilelegends' ? $validated['player_id'] : null,
                'zone_id_ml' => $validated['game_type'] === 'mobilelegends' ? ($validated['zone_id'] ?? null) : null,
                'player_id_ff' => $validated['game_type'] === 'freefire' ? $validated['player_id'] : null,
                'player_id_pubg' => $validated['game_type'] === 'pubgmobile' ? $validated['player_id'] : null,
                'player_id_hok' => $validated['game_type'] === 'honorofkings' ? $validated['player_id'] : null,
                'user_id_bs' => $validated['game_type'] === 'bloodstrike' ? $validated['player_id'] : null,
                'server_bs' => $validated['game_type'] === 'bloodstrike' ? ($validated['zone_id'] ?? null) : null,
                'wallet_deducted' => false,
                'seller_cost' => $baseCost,
                'seller_profit' => $profit,
                'is_direct_topup' => true,
                'original_price' => $sellingPrice,
                'final_price' => $sellingPrice,
            ]);

            // Send initial Telegram notification for the new order (processing)
            try {
                $order->load('diamondPack', 'seller');
                $initialMessage = \App\Services\TelegramService::formatOrderMessage($order);
                $messageId = \App\Services\TelegramService::sendMessage($initialMessage);
                if ($messageId) {
                    $order->tlg_message_id = $messageId;
                    $order->save();
                    Log::info('DirectTopup: saved initial telegram message id', ['order_id' => $order->id, 'message_id' => $messageId]);
                } else {
                    Log::info('DirectTopup: initial telegram send returned falsy', ['order_id' => $order->id]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send initial Telegram message for direct top-up', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Place order with VIP Reseller (only call VIP when seller has sufficient funds)
            $result = $this->placeVipResellerOrder($pack, $validated);

            if ($result['success']) {
                // Deduct from wallet now that VIP accepted order
                $tx = $seller->deductWallet($baseCost, "Direct top-up order #{$order->order_number}", $order->id);

                // Persist vipreseller_status and update order
                $apiData = $result['data']['data'] ?? [];

                $vipResellerStatus = \App\Models\VipResellerStatus::create([
                    'order_id' => $order->id,
                    'trxid' => $apiData['trxid'] ?? null,
                    'data' => $apiData['data'] ?? $validated['player_id'],
                    'zone' => $apiData['zone'] ?? ($validated['zone_id'] ?? null),
                    'service' => $apiData['service'] ?? $pack->code,
                    'status' => 'success',
                    'note' => $apiData['note'] ?? null,
                    'price' => $apiData['price'] ?? null,
                    'additional_data' => $result,
                ]);

                $order->update(['status' => 'completed', 'wallet_deducted' => true, 'seller_cost' => $baseCost, 'seller_profit' => $profit]);

                if ($tx) {
                    Log::info('Direct topup: Seller wallet deducted', ['seller_id' => $seller->id, 'order_id' => $order->id, 'tx_id' => $tx->id]);
                }

                $seller->addEarnings($profit, $sellingPrice);

                // Credit seller profit to wallet (idempotent) for direct top-ups
                try {
                    if (!$order->seller_profit_paid && (float) $order->seller_profit > 0) {
                        $seller->creditWallet((float) $order->seller_profit, "Profit for order #{$order->order_number}", null, $order->id, 'order_profit');
                        $order->seller_profit_paid = true;
                        $order->seller_profit_paid_at = now();
                        $order->save();
                        Log::info('DirectTopup: seller profit credited to wallet', ['order_id' => $order->id, 'seller_id' => $seller->id, 'amount' => $order->seller_profit]);
                    }
                } catch (\Exception $e) {
                    Log::warning('DirectTopup: Failed to credit seller profit', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                }

                // Update Telegram message with final status / VIP result if available
                try {
                    if ($order->tlg_message_id) {
                        $order->refresh();
                        $order->load('diamondPack', 'vipResellerStatuses', 'seller');
                        $updatedMessage = \App\Services\TelegramService::formatOrderMessage($order);
                        // Replace header to show completion
                        if (strpos($updatedMessage, '🆕 <b>New Order Created</b>') !== false) {
                            $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '✅ <b>Top-up Completed</b>', $updatedMessage);
                        }
                        Log::info('DirectTopup: editing telegram message', ['order_id' => $order->id, 'tlg_message_id' => $order->tlg_message_id]);
                        \App\Services\TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                    } else {
                        // Fallback: send a new notification
                        $this->sendDirectTopupNotification($seller, $order, $pack);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to update Telegram message after direct top-up success', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                DB::commit();

                // release lock
                $lock->release();

                return $request->wantsJson()
                    ? response()->json(['success' => true, 'order_number' => $order->order_number, 'status' => 'completed'])
                    : back()->with('success', 'Top-up completed successfully! Order #' . $order->order_number);
            } else {
                // VIP Reseller call failed — mark order failed and do NOT deduct wallet
                $order->update(['status' => 'failed', 'notes' => $result['error']]);

                DB::commit();
                // Update Telegram message to reflect failure if available
                try {
                    if ($order->tlg_message_id) {
                        $order->refresh();
                        $order->load('diamondPack', 'vipResellerStatuses', 'seller');
                        $updatedMessage = \App\Services\TelegramService::formatOrderMessage($order);
                        if (strpos($updatedMessage, '🆕 <b>New Order Created</b>') !== false) {
                            $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '❌ <b>Top-up Failed</b>', $updatedMessage);
                        }
                        \App\Services\TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to update Telegram message after direct top-up failure', [
                        'order_id' => $order->id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }

                $lock->release();

                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => 'Top-up failed: ' . ($result['error'] ?? 'Unknown')], 400)
                    : back()->withErrors(['error' => 'Top-up failed: ' . $result['error']]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            try { $lock->release(); } catch (\Throwable $ex) {}
            Log::error('Direct top-up failed', [
                'seller_id' => $seller->id,
                'error' => $e->getMessage()
            ]);
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'An error occurred. Please try again.'], 500)
                : back()->withErrors(['error' => 'An error occurred. Please try again.']);
        }
    }

    /**
     * Place order with VIP Reseller
     */
    protected function placeVipResellerOrder(DiamondPack $pack, array $data): array
    {
        try {
            switch ($pack->game_type) {
                case 'mobilelegends':
                    $response = $this->vipResellerService->placeOrder(
                        $pack->code,
                        $data['player_id'],
                        $data['zone_id'] ?? ''
                    );
                    break;

                case 'freefire':
                    $response = $this->vipResellerService->placeFreefireOrder(
                        $pack->code,
                        $data['player_id']
                    );
                    break;

                case 'pubgmobile':
                    $response = $this->vipResellerService->placePubgOrder(
                        $pack->code,
                        $data['player_id']
                    );
                    break;

                case 'honorofkings':
                    $response = $this->vipResellerService->placeHokOrder(
                        $pack->code,
                        $data['player_id']
                    );
                    break;

                case 'bloodstrike':
                    $response = $this->vipResellerService->placeBloodstrikeOrder(
                        $pack->code,
                        $data['player_id'],
                        $data['zone_id'] ?? 'Global'
                    );
                    break;

                default:
                    return ['success' => false, 'error' => 'Unsupported game type'];
            }

            if (isset($response['result']) && $response['result'] === true) {
                return ['success' => true, 'data' => $response];
            }

            return ['success' => false, 'error' => $response['message'] ?? 'Unknown error'];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send Telegram notification for direct top-up
     */
    protected function sendDirectTopupNotification(Seller $seller, Order $order, DiamondPack $pack): void
    {
        try {
            $gameNames = [
                'mobilelegends' => 'Mobile Legends',
                'freefire' => 'Free Fire',
                'pubgmobile' => 'PUBG Mobile',
                'honorofkings' => 'Honor of Kings',
                'bloodstrike' => 'Blood Strike',
            ];

            $playerId = $order->user_id_ml ?? $order->player_id_ff ?? $order->player_id_pubg ?? $order->player_id_hok ?? $order->user_id_bs;

            $message = "🎮 *Direct Top-Up by Seller*\n\n";
            $message .= "📦 Order: `{$order->order_number}`\n";
            $message .= "👤 Seller: {$seller->name} (@{$seller->username})\n";
            $message .= "🎯 Game: " . ($gameNames[$pack->game_type] ?? $pack->game_type) . "\n";
            $message .= "💎 Pack: {$pack->name}\n";
            $message .= "🆔 Player ID: `{$playerId}`\n";
            $message .= "💰 Cost: {$order->seller_cost} DZD\n";
            $message .= "📈 Profit: {$order->seller_profit} DZD\n";
            $message .= "✅ Status: Completed";

            // If an admin message exists for this order, update it; otherwise send a new message
            try {
                if ($order->tlg_message_id) {
                    \App\Services\TelegramService::editMessageText($order->tlg_message_id, $message);
                } else {
                    \App\Services\TelegramService::sendMessage($message);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send/edit direct-topup Telegram message', [
                    'order_id' => $order->id ?? null,
                    'error' => $e->getMessage()
                ]);
            }

        } catch (\Exception $e) {
            Log::warning('Failed to send Telegram notification for direct top-up', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Seller profile settings
     */
    public function profile()
    {
        $seller = $this->seller();
        return view('seller.profile', compact('seller'));
    }

    /**
     * Update seller profile
     */
    public function updateProfile(Request $request)
    {
        $seller = $this->seller();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'store_name' => 'nullable|string|max:255',
            'store_description' => 'nullable|string|max:1000',
        ]);

        $seller->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Seller settings page - show website parameters
     */
    public function settings()
    {
        // available games on platform
        $availableGames = DiamondPack::where('is_active', true)
            ->select('game_type')
            ->distinct()
            ->pluck('game_type')
            ->toArray();

        $seller = $this->seller();

        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'environment' => config('app.env'),
            'locale' => config('app.locale'),
            'timezone' => config('app.timezone'),
            'currency_usd_to_dzd' => config('currency.usd_to_dzd'),
            'mail_default' => config('mail.default'),
            'mail_from' => config('mail.from.address'),
            'postmark_configured' => !empty(config('services.postmark.key')),
            'resend_configured' => !empty(config('services.resend.key')),
            'telegram_configured' => !empty(config('telegram.bot_token')),
        ];

        return view('seller.settings', compact('settings', 'availableGames', 'seller'));
    }

    /**
     * Update seller settings
     */
    public function updateSettings(Request $request)
    {
        $seller = $this->seller();

        $availableGames = DiamondPack::where('is_active', true)
            ->select('game_type')
            ->distinct()
            ->pluck('game_type')
            ->toArray();

        $validated = $request->validate([
            'website_enabled' => 'sometimes|boolean',
            // we accept a slug or a full url — we'll normalize it to the slug below
            'website_url' => 'nullable|string|max:50',
            'allowed_games' => 'nullable|array',
            'allowed_games.*' => ['string', function ($attribute, $value, $fail) use ($availableGames) {
                if (!in_array($value, $availableGames)) {
                    $fail('Invalid game selected: ' . $value);
                }
            }],
            'flexy_enabled' => 'sometimes|boolean',
            'flexy_number' => 'nullable|string|max:50',
            'flexy_instruction' => 'nullable|string|max:2000',
        ]);

        // Normalize values
        $seller->website_enabled = (bool) ($validated['website_enabled'] ?? false);

        // Normalize 'website_url' input to a slug (last path segment) and validate
        if (array_key_exists('website_url', $validated)) {
            $raw = trim($validated['website_url'] ?? '');
            $slug = null;

            if ($raw !== '') {
                // if user entered a full URL, extract path and use the last segment
                if (str_contains($raw, '://') || str_contains($raw, '/')) {
                    $parts = parse_url($raw);
                    $path = $parts['path'] ?? '';
                    $segments = array_values(array_filter(explode('/', $path)));
                    $slug = end($segments) ?: null;
                } else {
                    $slug = $raw;
                }

                if ($slug) {
                    // normalize: allow letters, numbers, hyphen and underscore only
                    $slug = strtolower($slug);
                    $slug = preg_replace('/[^a-z0-9_-]+/', '', $slug);

                    if (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
                        return back()->withErrors(['website_url' => 'Store slug can only contain letters, numbers, hyphens and underscores.']);
                    }

                    // ensure slug uniqueness (exclude current seller)
                    if (\App\Models\Seller::where('website_url', $slug)->where('id', '!=', $seller->id)->exists()) {
                        return back()->withErrors(['website_url' => 'This store slug is already taken. Please choose another.']);
                    }
                }
            }

            // Save the slug (or null if blank)
            $seller->website_url = $slug ?: null;
        }
        $seller->allowed_games = $validated['allowed_games'] ?? [];
        $seller->flexy_enabled = (bool) ($validated['flexy_enabled'] ?? false);
        // Using only website_enabled / flexy_enabled going forward (no legacy flags)
        // new simulation flags and flexy details
        // removed: is_flexy/is_website simulation flags (now controlled via flexy_enabled / website_enabled)
        $seller->flexy_number = $validated['flexy_number'] ?? $seller->flexy_number;
        $seller->flexy_instruction = $validated['flexy_instruction'] ?? $seller->flexy_instruction;

        $seller->save();

        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Change seller password
     */
    public function changePassword(Request $request)
    {
        $seller = $this->seller();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if (!Hash::check($validated['current_password'], $seller->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $seller->update([
            'password' => Hash::make($validated['password'])
        ]);

        return back()->with('success', 'Password changed successfully!');
    }

    /**
     * Get order details for AJAX modal
     */
    public function getOrderDetails(string $orderNumber)
    {
        $seller = $this->seller();

        $order = $seller->orders()
            ->with(['diamondPack', 'user'])
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }

    /**
     * Confirm a Flexy order and process the top-up
     */
    public function confirmFlexyOrder(string $orderNumber)
    {
        $seller = $this->seller();

        $order = $seller->orders()
            ->with('diamondPack')
            ->where('order_number', $orderNumber)
            ->where('status', 'pending_flexy_verification')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or already processed'
            ], 404);
        }

        $pack = $order->diamondPack;
        $baseCost = $order->seller_cost;

        // Check wallet balance
        if ($seller->wallet_balance < $baseCost) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance. You need ' . $baseCost . ' DZD'
            ], 400);
        }

        try {
            // Determine player data based on game type
            $gameType = $pack->game_type;
            $playerId = $order->user_id_ml ?? $order->player_id_ff ?? $order->player_id_pubg ?? $order->player_id_hok ?? $order->user_id_bs;
            $zoneId = $order->zone_id_ml ?? $order->server_bs ?? null;

            $data = [
                'game_type' => $gameType,
                'player_id' => $playerId,
                'zone_id' => $zoneId,
            ];

            // Snapshot wallet before external top-up and any deductions
            $walletBefore = (float) $seller->wallet_balance;

            // Place order with VIP Reseller first (do not deduct wallet yet)
            $result = $this->placeVipResellerOrder($pack, $data);

            if (!$result['success']) {
                // Mark order failed on top-up failure and leave wallet unchanged
                $order->update(['status' => 'failed', 'notes' => $result['error'] ?? 'VIP Reseller error']);
                Log::warning('Flexy top-up failed at VIP reseller step', ['order_id' => $order->id, 'error' => $result['error'] ?? null]);
                // Notify via Telegram about the failed top-up
                try {
                    $this->sendFlexyFailureNotification($seller, $order, $pack, $result['error'] ?? 'VIP Reseller error', $walletBefore);
                } catch (\Throwable $ex) {
                    Log::warning('Failed to send telegram failure notification', ['order_id'=>$order->id, 'error'=>$ex->getMessage()]);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Top-up failed: ' . ($result['error'] ?? 'Unknown')
                ], 400);
            }

            // VIP Reseller succeeded — now deduct seller wallet and update order atomically
            DB::beginTransaction();

            // Ensure wallet still has funds (avoid TOCTOU problems)
            $seller->refresh();
            if ($seller->wallet_balance < $baseCost) {
                // No funds to charge — mark failed and return error (VIP already executed)
                $order->update(['status' => 'failed', 'notes' => 'Insufficient wallet after top-up confirmation']);
                Log::error('Seller missing funds after successful VIP top-up', ['seller_id' => $seller->id, 'order_id' => $order->id, 'required' => $baseCost]);
                // Inform via Telegram that VIP top-up happened but seller lacked funds
                try {
                    $this->sendFlexyFailureNotification($seller, $order, $pack, 'Insufficient wallet after successful VIP top-up', (float)$seller->wallet_balance);
                } catch (\Throwable $ex) {
                    Log::warning('Failed to send telegram notification for insufficient wallet after VIP', ['order_id'=>$order->id,'error'=>$ex->getMessage()]);
                }
                DB::commit();
                return response()->json(['success' => false, 'message' => 'Insufficient wallet balance to deduct the cost. Contact admin.'], 400);
            }

            // Mark processing then deduct within transaction
            $order->update(['status' => 'processing']);
            $seller->deductWallet($baseCost, "Flexy order #{$order->order_number}", $order->id);
            $order->update(['wallet_deducted' => true, 'status' => 'completed']);

            // Add earnings (totals) and credit profit to seller wallet (idempotent)
            $seller->addEarnings($order->seller_profit, $order->final_price);
            try { if ($order->seller_id && !$order->seller_profit_paid) { $order->creditSellerProfit(); } } catch (\Throwable $ex) { Log::warning('Seller confirm: Failed to credit seller profit', ['order_id'=>$order->id,'error'=>$ex->getMessage()]); }

            // (Telegram success notification sent below once the final balances are known)

            DB::commit();

            // Refresh seller to ensure current state
            $seller->refresh();
            $walletAfter = (float) $seller->wallet_balance;

            // Notify via Telegram about success with wallet details
            try {
                $this->sendFlexyConfirmNotification($seller, $order, $pack, $walletBefore, $walletAfter);
            } catch (\Throwable $ex) {
                Log::warning('Failed to send telegram success notification', ['order_id'=>$order->id,'error'=>$ex->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order confirmed and processed successfully!',
                'order' => [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'wallet_deducted' => (bool) $order->wallet_deducted,
                    'seller_cost' => (float) $order->seller_cost,
                    'seller_profit' => (float) $order->seller_profit,
                    'final_price' => (float) $order->final_price,
                ],
                'seller' => [
                    'id' => $seller->id,
                    'username' => $seller->username,
                    'wallet_before' => $walletBefore,
                    'wallet_after' => $walletAfter,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Flexy order confirmation failed', [
                'seller_id' => $seller->id,
                'order_number' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the order'
            ], 500);
        }
    }

    /**
     * Delete an order (only pending/failed orders)
     */
    public function deleteOrder(string $orderNumber)
    {
        $seller = $this->seller();

        $order = $seller->orders()
            ->where('order_number', $orderNumber)
            ->whereIn('status', ['pending', 'pending_flexy_verification', 'failed'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or cannot be deleted'
            ], 404);
        }

        try {
            // Delete associated receipt file if exists
            if ($order->flexy_receipt) {
                $receiptPath = storage_path('app/public/' . $order->flexy_receipt);
                if (file_exists($receiptPath)) {
                    unlink($receiptPath);
                }
            }

            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete order', [
                'seller_id' => $seller->id,
                'order_number' => $orderNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order'
            ], 500);
        }
    }

    /**
     * Send Telegram notification for Flexy order confirmation
     */
    protected function sendFlexyConfirmNotification(Seller $seller, Order $order, DiamondPack $pack, ?float $walletBefore = null, ?float $walletAfter = null): void
    {
        try {
            $gameNames = [
                'mobilelegends' => 'Mobile Legends',
                'freefire' => 'Free Fire',
                'pubgmobile' => 'PUBG Mobile',
                'honorofkings' => 'Honor of Kings',
                'bloodstrike' => 'Blood Strike',
            ];

            $playerId = $order->user_id_ml ?? $order->player_id_ff ?? $order->player_id_pubg ?? $order->player_id_hok ?? $order->user_id_bs;

            $message = "💳 *Flexy Order Confirmed*\n\n";
            $message .= "📦 Order: `{$order->order_number}`\n";
            $message .= "👤 Seller: {$seller->name} (@{$seller->username})\n";
            $message .= "🎯 Game: " . ($gameNames[$pack->game_type] ?? $pack->game_type) . "\n";
            $message .= "💎 Pack: {$pack->name}\n";
            $message .= "🆔 Player ID: `{$playerId}`\n";
            $message .= "💰 Cost: {$order->seller_cost} DZD\n";
            $message .= "📈 Profit: {$order->seller_profit} DZD\n";
            $message .= "✅ Status: Completed\n";

            if (!is_null($walletBefore) || !is_null($walletAfter)) {
                $message .= "\n🏦 <b>Seller Wallet</b>:\n";
                if (!is_null($walletBefore)) $message .= "• Balance before: " . number_format($walletBefore, 2) . " DZD\n";
                if (!is_null($walletAfter)) $message .= "• Balance after: " . number_format($walletAfter, 2) . " DZD\n";
            }

            $this->telegramService->sendMessage($message);

        } catch (\Exception $e) {
            Log::warning('Failed to send Telegram notification for Flexy confirmation', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send Telegram notification for Flexy top-up failure
     */
    protected function sendFlexyFailureNotification(Seller $seller, Order $order, DiamondPack $pack, string $reason, ?float $walletBalance = null): void
    {
        try {
            $gameNames = [
                'mobilelegends' => 'Mobile Legends',
                'freefire' => 'Free Fire',
                'pubgmobile' => 'PUBG Mobile',
                'honorofkings' => 'Honor of Kings',
                'bloodstrike' => 'Blood Strike',
            ];

            $playerId = $order->user_id_ml ?? $order->player_id_ff ?? $order->player_id_pubg ?? $order->player_id_hok ?? $order->user_id_bs;

            $message = "⚠️ <b>Flexy Order Failed</b>\n\n";
            $message .= "📦 Order: `{$order->order_number}`\n";
            $message .= "👤 Seller: {$seller->name} (@{$seller->username})\n";
            $message .= "🎯 Game: " . ($gameNames[$pack->game_type] ?? $pack->game_type) . "\n";
            $message .= "💎 Pack: {$pack->name}\n";
            $message .= "🆔 Player ID: `{$playerId}`\n";
            $message .= "❌ Reason: {$reason}\n";

            if (!is_null($walletBalance)) {
                $message .= "\n🏦 <b>Seller Wallet</b>: " . number_format($walletBalance, 2) . " DZD\n";
            }

            $this->telegramService->sendMessage($message);

        } catch (\Exception $e) {
            Log::warning('Failed to send Telegram notification for Flexy failure', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
