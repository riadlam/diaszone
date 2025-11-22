<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function getDashboardData()
    {
        // Get authenticated user data (may be null for public routes)
        $authUser = Auth::user();
        
        // Use real user data if authenticated, otherwise empty
        $user = [
            'email' => $authUser ? ($authUser->email ?? '') : '',
            'phone' => $authUser && isset($authUser->phone) ? $authUser->phone : '', // Phone may not exist in users table
            'name' => $authUser ? ($authUser->name ?? '') : '',
        ];

        // Dummy orders data
        $orders = [
            [
                'id' => 'ORD-001',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '86 Diamonds + 8 Bonus',
                'diamonds' => 86,
                'bonus' => 8,
                'amount' => 0.92,
                'status' => 'completed',
                'date' => '2025-01-15 10:30:00',
            ],
            [
                'id' => 'ORD-002',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '172 Diamonds + 17 Bonus',
                'diamonds' => 172,
                'bonus' => 17,
                'amount' => 1.84,
                'status' => 'pending',
                'date' => '2025-01-16 14:20:00',
            ],
            [
                'id' => 'ORD-003',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '257 Diamonds + 25 Bonus',
                'diamonds' => 257,
                'bonus' => 25,
                'amount' => 2.76,
                'status' => 'sending',
                'date' => '2025-01-17 09:15:00',
            ],
            [
                'id' => 'ORD-004',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '344 Diamonds + 34 Bonus',
                'diamonds' => 344,
                'bonus' => 34,
                'amount' => 3.68,
                'status' => 'completed',
                'date' => '2025-01-18 16:45:00',
            ],
            [
                'id' => 'ORD-005',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '429 Diamonds + 42 Bonus',
                'diamonds' => 429,
                'bonus' => 42,
                'amount' => 4.60,
                'status' => 'refunded',
                'date' => '2025-01-19 11:30:00',
            ],
            [
                'id' => 'ORD-006',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '514 Diamonds + 51 Bonus',
                'diamonds' => 514,
                'bonus' => 51,
                'amount' => 5.52,
                'status' => 'pending',
                'date' => '2025-01-20 13:20:00',
            ],
            [
                'id' => 'ORD-007',
                'product' => 'Mobile Legends Diamonds',
                'pack' => '2000 Diamonds + 200 Bonus',
                'diamonds' => 2000,
                'bonus' => 200,
                'amount' => 18.40,
                'status' => 'completed',
                'date' => '2025-01-21 15:30:00',
            ],
        ];

        // Empty invoices data - no invoices found
        $invoices = [];

        // Empty notifications data - no notifications found
        $notifications = [];

        return [
            'user' => $user,
            'orders' => $orders,
            'invoices' => $invoices,
            'notifications' => $notifications,
        ];
    }

    public function index()
    {
        // Redirect to orders by default (public access)
        return redirect()->route('dashboard.orders');
    }

    public function myAccount()
    {
        $data = $this->getDashboardData();
        $data['activeSection'] = 'account';
        return view('pages.dashboard', $data);
    }

    public function orders()
    {
        $data = $this->getDashboardData();
        $data['activeSection'] = 'orders';
        return view('pages.dashboard', $data);
    }

    public function invoices()
    {
        $data = $this->getDashboardData();
        $data['activeSection'] = 'invoices';
        return view('pages.dashboard', $data);
    }

    public function notifications()
    {
        $data = $this->getDashboardData();
        $data['activeSection'] = 'notifications';
        return view('pages.dashboard', $data);
    }
}

