<?php

namespace App\Http\Controllers;

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

        // Orders are loaded on the My Orders section via API (see dashboard view: /api/orders/mine or localStorage).
        $orders = [];

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
