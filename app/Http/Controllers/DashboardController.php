<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function getDashboardData()
    {
        // Get authenticated user data (should always exist since route is protected by auth middleware)
        $authUser = Auth::user();
        
        // Use real user data
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

        // Dummy invoices data
        $invoices = [
            [
                'id' => 'INV-001',
                'invoice_number' => 'INV-2025-001',
                'order_id' => 'ORD-001',
                'amount' => 0.92,
                'status' => 'paid',
                'issue_date' => '2025-01-15',
                'due_date' => '2025-01-22',
                'payment_method' => 'Credit Card',
            ],
            [
                'id' => 'INV-002',
                'invoice_number' => 'INV-2025-002',
                'order_id' => 'ORD-002',
                'amount' => 1.84,
                'status' => 'pending',
                'issue_date' => '2025-01-16',
                'due_date' => '2025-01-23',
                'payment_method' => 'PayPal',
            ],
            [
                'id' => 'INV-003',
                'invoice_number' => 'INV-2025-003',
                'order_id' => 'ORD-003',
                'amount' => 2.76,
                'status' => 'paid',
                'issue_date' => '2025-01-17',
                'due_date' => '2025-01-24',
                'payment_method' => 'Cryptocurrency',
            ],
            [
                'id' => 'INV-004',
                'invoice_number' => 'INV-2025-004',
                'order_id' => 'ORD-004',
                'amount' => 3.68,
                'status' => 'paid',
                'issue_date' => '2025-01-18',
                'due_date' => '2025-01-25',
                'payment_method' => 'Credit Card',
            ],
            [
                'id' => 'INV-005',
                'invoice_number' => 'INV-2025-005',
                'order_id' => 'ORD-005',
                'amount' => 4.60,
                'status' => 'overdue',
                'issue_date' => '2025-01-19',
                'due_date' => '2025-01-26',
                'payment_method' => 'Bank Transfer',
            ],
            [
                'id' => 'INV-006',
                'invoice_number' => 'INV-2025-006',
                'order_id' => 'ORD-006',
                'amount' => 5.52,
                'status' => 'pending',
                'issue_date' => '2025-01-20',
                'due_date' => '2025-01-27',
                'payment_method' => 'PayPal',
            ],
            [
                'id' => 'INV-007',
                'invoice_number' => 'INV-2025-007',
                'order_id' => 'ORD-007',
                'amount' => 18.40,
                'status' => 'paid',
                'issue_date' => '2025-01-21',
                'due_date' => '2025-01-28',
                'payment_method' => 'Credit Card',
            ],
        ];

        // Dummy notifications data
        $notifications = [
            [
                'id' => 'NOTIF-001',
                'type' => 'error',
                'title' => 'Bank Transfer Issue',
                'message' => 'The bank transfer information you provided is incorrect. Please update your payment details or contact support for assistance.',
                'date' => '2025-01-21 14:30:00',
                'read' => false,
            ],
            [
                'id' => 'NOTIF-002',
                'type' => 'warning',
                'title' => 'Payment Required',
                'message' => 'Your order ORD-002 is pending payment. Please complete the payment within 24 hours to avoid cancellation.',
                'date' => '2025-01-20 10:15:00',
                'read' => false,
            ],
            [
                'id' => 'NOTIF-003',
                'type' => 'info',
                'title' => 'Order Status Update',
                'message' => 'Your order ORD-003 is now being processed and will be delivered shortly. You will receive a confirmation email once completed.',
                'date' => '2025-01-19 16:45:00',
                'read' => true,
            ],
            [
                'id' => 'NOTIF-004',
                'type' => 'warning',
                'title' => 'Action Required',
                'message' => 'Please verify your account email address. Click the verification link sent to your email to continue using all features.',
                'date' => '2025-01-18 09:20:00',
                'read' => true,
            ],
            [
                'id' => 'NOTIF-005',
                'type' => 'error',
                'title' => 'Payment Failed',
                'message' => 'Your payment for order ORD-005 has failed. Please check your payment method and try again, or contact your bank for more information.',
                'date' => '2025-01-17 11:30:00',
                'read' => false,
            ],
            [
                'id' => 'NOTIF-006',
                'type' => 'success',
                'title' => 'Order Completed',
                'message' => 'Your order ORD-001 has been successfully completed. The diamonds have been added to your account. Thank you for your purchase!',
                'date' => '2025-01-16 13:15:00',
                'read' => true,
            ],
            [
                'id' => 'NOTIF-007',
                'type' => 'info',
                'title' => 'Invoice Available',
                'message' => 'Your invoice INV-2025-007 is now available for download. You can access it from the My Invoices section.',
                'date' => '2025-01-15 08:00:00',
                'read' => true,
            ],
        ];

        return [
            'user' => $user,
            'orders' => $orders,
            'invoices' => $invoices,
            'notifications' => $notifications,
        ];
    }

    public function index()
    {
        // Redirect to myaccount by default
        return redirect()->route('dashboard.myaccount');
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

