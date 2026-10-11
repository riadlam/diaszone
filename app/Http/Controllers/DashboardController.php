<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function getDashboardData()
    {
        $authUser = Auth::user();

        $name = $authUser ? (string) ($authUser->name ?? '') : '';
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        if ($initials === '' && $authUser?->email) {
            $initials = mb_strtoupper(mb_substr((string) $authUser->email, 0, 1));
        }

        $user = [
            'email' => $authUser ? (string) ($authUser->email ?? '') : '',
            'phone' => $authUser && isset($authUser->phone) ? (string) $authUser->phone : '',
            'name' => $name,
            'avatar' => $authUser?->google_avatar,
            'google_linked' => $authUser ? filled($authUser->google_id) : false,
            'member_since' => $authUser?->created_at?->timezone(config('app.timezone'))->format('M Y'),
            'orders_count' => $authUser ? (int) $authUser->orders()->count() : 0,
            'initials' => $initials !== '' ? $initials : 'DZ',
        ];

        return [
            'user' => $user,
            'orders' => [],
            'invoices' => [],
            'notifications' => [],
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
