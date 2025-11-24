<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Send a message to Telegram
     *
     * @param string $message
     * @return bool
     */
    public static function sendMessage(string $message): bool
    {
        try {
            $botToken = config('telegram.bot_token');
            $chatId = config('telegram.chat_id');
            $apiUrl = config('telegram.api_url');
            
            if (!$botToken || !$chatId) {
                Log::error('Telegram: Missing bot token or chat ID');
                return false;
            }
            
            $url = $apiUrl . $botToken . '/sendMessage';
            
            $response = Http::post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
            
            if ($response->successful()) {
                return true;
            } else {
                Log::error('Telegram: Failed to send message', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Telegram: Exception while sending message', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Format order notification message
     *
     * @param \App\Models\Order $order
     * @return string
     */
    public static function formatOrderMessage($order): string
    {
        $gameType = $order->diamondPack->game_type ?? 'mobilelegends';
        $gameName = 'Mobile Legends';
        $currencyText = 'Diamonds';
        
        if ($gameType === 'freefire') {
            $gameName = 'Free Fire';
            $currencyText = 'Diamonds';
        } elseif ($gameType === 'pubgmobile') {
            $gameName = 'PUBG Mobile';
            $currencyText = 'UC';
        } elseif ($gameType === 'honorofkings') {
            $gameName = 'Honor of Kings';
            $currencyText = 'Tokens';
        } elseif ($gameType === 'bloodstrike') {
            $gameName = 'Blood Strike';
            $currencyText = 'Golds';
        }
        
        $packName = $order->diamondPack->name ?? ($order->diamondPack->diamonds . ' ' . $currencyText);
        if ($order->diamondPack->bonus_diamonds > 0) {
            $packName .= ' + ' . $order->diamondPack->bonus_diamonds . ' Bonus';
        }
        
        // Calculate amount
        $priceDzd = $order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260);
        $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
        $discountAmount = ($priceDzd * $discountPercentage) / 100;
        $amount = $priceDzd - $discountAmount;
        
        $userName = $order->user ? $order->user->name : 'Guest';
        $userEmail = $order->user ? $order->user->email : 'N/A';
        
        $message = "🆕 <b>New Order Created</b>\n\n";
        $message .= "📦 <b>Order:</b> {$order->order_number}\n";
        $message .= "🎮 <b>Game:</b> {$gameName}\n";
        $message .= "💎 <b>Pack:</b> {$packName}\n";
        $message .= "💰 <b>Amount:</b> " . number_format($amount, 0) . " DZD\n";
        $message .= "📊 <b>Status:</b> " . ucfirst(str_replace('_', ' ', $order->status)) . "\n";
        $message .= "👤 <b>User:</b> {$userName}\n";
        $message .= "📧 <b>Email:</b> {$userEmail}\n";
        
        // Add game-specific details
        if ($gameType === 'mobilelegends') {
            if ($order->user_id_ml) {
                $message .= "🆔 <b>User ID:</b> {$order->user_id_ml}\n";
            }
            if ($order->zone_id_ml) {
                $message .= "🌍 <b>Zone ID:</b> {$order->zone_id_ml}\n";
            }
        } elseif ($gameType === 'freefire' && $order->player_id_ff) {
            $message .= "🆔 <b>Player ID:</b> {$order->player_id_ff}\n";
        } elseif ($gameType === 'pubgmobile' && $order->player_id_pubg) {
            $message .= "🆔 <b>Player ID:</b> {$order->player_id_pubg}\n";
        } elseif ($gameType === 'honorofkings' && $order->player_id_hok) {
            $message .= "🆔 <b>Player ID:</b> {$order->player_id_hok}\n";
        } elseif ($gameType === 'bloodstrike') {
            if ($order->user_id_bs) {
                $message .= "🆔 <b>User ID:</b> {$order->user_id_bs}\n";
            }
            if ($order->server_bs) {
                $message .= "🖥️ <b>Server:</b> {$order->server_bs}\n";
            }
        }
        
        $message .= "\n⏰ <b>Created:</b> " . $order->created_at->format('Y-m-d H:i:s');
        
        return $message;
    }
}

