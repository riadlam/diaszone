<?php

use Illuminate\Broadcasting\Broadcast;use Illuminate\Broadcasting\Channel;use Illuminate\Broadcasting\PrivateChannel;use Illuminate\Broadcasting\PresenceChannel;use Illuminate\Broadcasting\BroadcastException;use Illuminate\Support\Facades\Log;

/**
 * Here you may register all of the event broadcasting channels that your
 * application supports. The given channel authorization callbacks are
 * used to check if an authenticated user can listen to the channel.
 */

Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::find($orderId);
    if (!$order) return false;

    // Allow admin user
    if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) return true;

    // Allow owning customer
    if ($user && $order->user_id && $user->id === $order->user_id) return true;

    // Allow seller guard users (check seller session)
    try {
        $seller = auth()->guard('seller')->user();
        if ($seller && $order->seller_id && $seller->id === $order->seller_id) return true;
    } catch (\Throwable $e) {
        // ignore
    }

    return false;
});