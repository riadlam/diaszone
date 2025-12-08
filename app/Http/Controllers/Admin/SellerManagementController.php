<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\DiamondPack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SellerManagementController extends Controller
{
    /**
     * List all sellers
     */
    public function index(Request $request)
    {
        $query = Seller::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $sellers = $query->withCount('orders')
            ->latest()
            ->paginate(20);

        return view('admin.sellers.index', compact('sellers'));
    }

    /**
     * Show seller details
     */
    public function show(Seller $seller)
    {
        $seller->load(['orders' => function ($q) {
            $q->latest()->take(20);
        }, 'walletTransactions' => function ($q) {
            $q->latest()->take(20);
        }]);

        $stats = [
            'total_orders' => $seller->orders()->count(),
            'completed_orders' => $seller->orders()->where('status', 'completed')->count(),
            'total_revenue' => $seller->total_sales,
            'total_profit' => $seller->total_earnings,
        ];

        return view('admin.sellers.show', compact('seller', 'stats'));
    }

    /**
     * Create new seller form
     */
    public function create()
    {
        $gameTypes = DiamondPack::where('is_active', true)
            ->select('game_type')
            ->distinct()
            ->pluck('game_type');

        return view('admin.sellers.create', compact('gameTypes'));
    }

    /**
     * Store new seller
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:sellers|alpha_dash',
            'email' => 'required|string|email|max:255|unique:sellers',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'store_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,pending,suspended',
            'wallet_balance' => 'nullable|numeric|min:0',
            'allowed_games' => 'nullable|array',
        ]);

        $seller = Seller::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'store_name' => $validated['store_name'] ?? $validated['name'] . "'s Store",
            'status' => $validated['status'],
            'wallet_balance' => $validated['wallet_balance'] ?? 0,
            'allowed_games' => $validated['allowed_games'] ?? null,
        ]);

        // If initial balance, create transaction
        if (($validated['wallet_balance'] ?? 0) > 0) {
            $seller->walletTransactions()->create([
                'type' => 'credit',
                'amount' => $validated['wallet_balance'],
                'balance_before' => 0,
                'balance_after' => $validated['wallet_balance'],
                'description' => 'Initial balance',
                'reference_type' => 'admin_topup',
                'admin_id' => auth()->id(),
            ]);
        }

        return redirect()->route('admin.sellers.index')
            ->with('success', 'Seller created successfully!');
    }

    /**
     * Edit seller form
     */
    public function edit(Seller $seller)
    {
        $gameTypes = DiamondPack::where('is_active', true)
            ->select('game_type')
            ->distinct()
            ->pluck('game_type');

        return view('admin.sellers.edit', compact('seller', 'gameTypes'));
    }

    /**
     * Update seller
     */
    public function update(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:sellers,email,' . $seller->id,
            'phone' => 'nullable|string|max:20',
            'store_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,pending,suspended',
            'allowed_games' => 'nullable|array',
        ]);

        $seller->update($validated);

        return redirect()->route('admin.sellers.show', $seller)
            ->with('success', 'Seller updated successfully!');
    }

    /**
     * Top up seller wallet
     */
    public function topupWallet(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $description = $validated['description'] ?? 'Admin top-up';

        $seller->creditWallet(
            $validated['amount'],
            $description,
            auth()->id(),
            'admin_topup'
        );

        return back()->with('success', "Wallet topped up with {$validated['amount']} DZD!");
    }

    /**
     * Deduct from seller wallet
     */
    public function deductWallet(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $seller->wallet_balance,
            'description' => 'nullable|string|max:255',
        ]);

        $description = $validated['description'] ?? 'Admin deduction';

        $seller->deductWallet($validated['amount'], $description);

        return back()->with('success', "Deducted {$validated['amount']} DZD from wallet!");
    }

    /**
     * Update seller status (activate/suspend)
     */
    public function updateStatus(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,pending,suspended',
        ]);

        $seller->update(['status' => $validated['status']]);

        return back()->with('success', "Seller status updated to {$validated['status']}!");
    }

    /**
     * View seller's pricing
     */
    public function pricing(Seller $seller)
    {
        $gameTypes = DiamondPack::where('is_active', true)
            ->select('game_type')
            ->distinct()
            ->pluck('game_type');

        $allPacks = DiamondPack::where('is_active', true)
            ->orderBy('game_type')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('game_type');

        $sellerPrices = $seller->gamePrices()->get()->keyBy('diamond_pack_id');

        return view('admin.sellers.pricing', compact('seller', 'gameTypes', 'allPacks', 'sellerPrices'));
    }

    /**
     * View seller's orders
     */
    public function orders(Request $request, Seller $seller)
    {
        $query = $seller->orders()->with(['diamondPack']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20);

        return view('admin.sellers.orders', compact('seller', 'orders'));
    }

    /**
     * View seller's transactions
     */
    public function transactions(Seller $seller)
    {
        $transactions = $seller->walletTransactions()
            ->with('admin')
            ->latest()
            ->paginate(30);

        return view('admin.sellers.transactions', compact('seller', 'transactions'));
    }

    /**
     * Delete seller
     */
    public function destroy(Seller $seller)
    {
        // Check if seller has orders
        if ($seller->orders()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete seller with existing orders']);
        }

        $seller->delete();

        return redirect()->route('admin.sellers.index')
            ->with('success', 'Seller deleted successfully!');
    }
}
