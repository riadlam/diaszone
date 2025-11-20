<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'total_orders' => 1250, // Dummy data - replace with actual Order model count
            'total_revenue' => 15420.50, // Dummy data
            'pending_orders' => 23, // Dummy data
            'completed_orders' => 987, // Dummy data
            'refunded_orders' => 12, // Dummy data
            'today_revenue' => 1250.75, // Dummy data
            'monthly_revenue' => 8750.25, // Dummy data
        ];

        // Recent users
        $recentUsers = User::latest()->take(5)->get();

        // Recent orders (dummy data)
        $recentOrders = [
            [
                'id' => 'ORD-001',
                'user' => 'John Doe',
                'product' => 'Mobile Legends Diamonds',
                'amount' => 18.40,
                'status' => 'completed',
                'date' => now()->subHours(2),
            ],
            [
                'id' => 'ORD-002',
                'user' => 'Jane Smith',
                'product' => 'Mobile Legends Diamonds',
                'amount' => 5.52,
                'status' => 'pending',
                'date' => now()->subHours(5),
            ],
            [
                'id' => 'ORD-003',
                'user' => 'Mike Johnson',
                'product' => 'Mobile Legends Diamonds',
                'amount' => 2.76,
                'status' => 'sending',
                'date' => now()->subHours(8),
            ],
            [
                'id' => 'ORD-004',
                'user' => 'Sarah Williams',
                'product' => 'Mobile Legends Diamonds',
                'amount' => 9.20,
                'status' => 'completed',
                'date' => now()->subHours(12),
            ],
            [
                'id' => 'ORD-005',
                'user' => 'David Brown',
                'product' => 'Mobile Legends Diamonds',
                'amount' => 4.60,
                'status' => 'completed',
                'date' => now()->subDays(1),
            ],
        ];

        // Revenue chart data (last 7 days - dummy data)
        $revenueChart = [
            ['day' => 'Mon', 'revenue' => 1250],
            ['day' => 'Tue', 'revenue' => 1890],
            ['day' => 'Wed', 'revenue' => 2100],
            ['day' => 'Thu', 'revenue' => 1750],
            ['day' => 'Fri', 'revenue' => 2300],
            ['day' => 'Sat', 'revenue' => 1950],
            ['day' => 'Sun', 'revenue' => 1680],
        ];

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
    public function orders()
    {
        // Dummy orders data
        $orders = [
            [
                'id' => 'ORD-001',
                'user' => 'John Doe',
                'email' => 'john@example.com',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '2000 Diamonds + 200 Bonus',
                'amount' => 18.40,
                'status' => 'completed',
                'date' => now()->subHours(2),
            ],
            [
                'id' => 'ORD-002',
                'user' => 'Jane Smith',
                'email' => 'jane@example.com',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '514 Diamonds + 51 Bonus',
                'amount' => 5.52,
                'status' => 'pending',
                'date' => now()->subHours(5),
            ],
            [
                'id' => 'ORD-003',
                'user' => 'Mike Johnson',
                'email' => 'mike@example.com',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '257 Diamonds + 25 Bonus',
                'amount' => 2.76,
                'status' => 'sending',
                'date' => now()->subHours(8),
            ],
            [
                'id' => 'ORD-004',
                'user' => 'Sarah Williams',
                'email' => 'sarah@example.com',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '429 Diamonds + 42 Bonus',
                'amount' => 9.20,
                'status' => 'completed',
                'date' => now()->subHours(12),
            ],
            [
                'id' => 'ORD-005',
                'user' => 'David Brown',
                'email' => 'david@example.com',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '172 Diamonds + 17 Bonus',
                'amount' => 4.60,
                'status' => 'refunded',
                'date' => now()->subDays(1),
            ],
        ];

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
}

