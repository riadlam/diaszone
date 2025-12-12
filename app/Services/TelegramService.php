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
     * @param bool $addConfirmButton Whether to add "Confirm Order" button (for pending_confirmation orders)
     * @return int|null Returns message_id on success, null on failure
     */
    public static function sendMessage(string $message, bool $addConfirmButton = false): ?int
    {
        try {
            $botToken = config('telegram.bot_token');
            $chatId = config('telegram.chat_id');
            $apiUrl = config('telegram.api_url');
            
            if (!$botToken || !$chatId) {
                Log::error('Telegram: Missing bot token or chat ID', [
                    'bot_token_set' => !empty($botToken),
                    'chat_id_set' => !empty($chatId),
                ]);
                return null;
            }
            
            $url = $apiUrl . $botToken . '/sendMessage';
            
            // Ensure chat_id is a string (Telegram API requirement)
            $chatId = (string) $chatId;
            
            Log::info('Telegram: Sending message', [
                'chat_id' => $chatId,
                'message_length' => strlen($message),
                'url' => str_replace($botToken, '***', $url), // Hide token in logs
            ]);
            
            $payload = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ];
            
            // Add inline keyboard buttons for pending_confirmation orders
            if ($addConfirmButton) {
                $payload['reply_markup'] = json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '✅ Confirm Order',
                                'callback_data' => 'confirm_order'
                            ],
                            [
                                'text' => '❌ Cancel Order',
                                'callback_data' => 'cancel_order'
                            ]
                        ],
                        [
                            [
                                'text' => '📄 View Receipt',
                                'callback_data' => 'view_receipt'
                            ]
                        ]
                    ]
                ]);
            }
            
            $response = Http::timeout(10)->post($url, $payload);
            
            $responseData = $response->json();
            
            if ($response->successful() && isset($responseData['ok']) && $responseData['ok'] === true) {
                $messageId = $responseData['result']['message_id'] ?? null;
                Log::info('Telegram: Message sent successfully', [
                    'message_id' => $messageId,
                ]);
                return $messageId ? (int) $messageId : null;
            } else {
                Log::error('Telegram: Failed to send message', [
                    'status' => $response->status(),
                    'response' => $responseData,
                    'chat_id' => $chatId,
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Telegram: Exception while sending message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
    
    /**
     * Answer callback query (for button clicks)
     *
     * @param string $callbackQueryId
     * @param string $text
     * @param bool $showAlert
     * @return bool
     */
    public static function answerCallbackQuery(string $callbackQueryId, string $text, bool $showAlert = false): bool
    {
        try {
            $botToken = config('telegram.bot_token');
            $apiUrl = config('telegram.api_url');
            
            if (!$botToken) {
                return false;
            }
            
            $url = $apiUrl . $botToken . '/answerCallbackQuery';
            
            $response = Http::timeout(10)->post($url, [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
                'show_alert' => $showAlert,
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram: Exception while answering callback query', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Edit message text
     *
     * @param int $messageId
     * @param string $newText
     * @return bool
     */
    public static function editMessageText(int $messageId, string $newText): bool
    {
        try {
            $botToken = config('telegram.bot_token');
            $chatId = config('telegram.chat_id');
            $apiUrl = config('telegram.api_url');
            
            if (!$botToken || !$chatId) {
                return false;
            }
            
            $url = $apiUrl . $botToken . '/editMessageText';
            
            $response = Http::timeout(10)->post($url, [
                'chat_id' => (string) $chatId,
                'message_id' => $messageId,
                'text' => $newText,
                'parse_mode' => 'HTML',
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram: Exception while editing message', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Send photo to Telegram
     *
     * @param string $photoUrl
     * @param string|null $caption
     * @return bool
     */
    public static function sendPhoto(string $photoUrl, ?string $caption = null): bool
    {
        try {
            $botToken = config('telegram.bot_token');
            $chatId = config('telegram.chat_id');
            $apiUrl = config('telegram.api_url');
            
            if (!$botToken || !$chatId) {
                return false;
            }
            
            $url = $apiUrl . $botToken . '/sendPhoto';
            
            $payload = [
                'chat_id' => (string) $chatId,
                'photo' => $photoUrl,
            ];
            
            if ($caption) {
                $payload['caption'] = $caption;
                $payload['parse_mode'] = 'HTML';
            }
            
            $response = Http::timeout(10)->post($url, $payload);
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram: Exception while sending photo', [
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
        
        // Calculate amount - prefer order final_price if present (supports quantity multiplies)
        if (!empty($order->final_price)) {
            $amount = $order->final_price;
        } else {
            $priceDzd = $order->diamondPack->price_dzd ?? ($order->diamondPack->price * 260);
            $discountPercentage = $order->diamondPack->discount_percentage ?? 0;
            $discountAmount = ($priceDzd * $discountPercentage) / 100;
            $quantity = $order->quantity ?? 1;
            $amount = ($priceDzd - $discountAmount) * $quantity;
        }
        
        // Escape input for HTML parse mode to avoid injection
        $escape = function ($s) {
            if (is_null($s)) return '';
            return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        $userName = $order->user ? $escape($order->user->name) : 'Guest';
        $userEmail = $order->user ? $escape($order->user->email) : 'N/A';
        
        $message = "🆕 <b>New Order Created</b>\n\n";
        $message .= "📦 <b>Order:</b> {$escape($order->order_number)}\n";
        $message .= "🎮 <b>Game:</b> {$escape($gameName)}\n";
        $message .= "💎 <b>Pack:</b> {$escape($packName)}\n";
        $message .= "💰 <b>Amount:</b> " . number_format($amount, 0) . " DZD\n";
        $message .= "📊 <b>Status:</b> " . ucfirst(str_replace('_', ' ', $order->status)) . "\n";

        // If order has quantity (multi-topup pass), show progress
        if (!empty($order->quantity) && $order->quantity > 1) {
            $succeeded = method_exists($order, 'successfulDigiflazzTopupsCount') ? $order->successfulDigiflazzTopupsCount() : 0;
            $message .= "🔁 <b>Top-ups:</b> {$succeeded}/{$order->quantity} completed\n";
            $message .= "🏷️ <b>Offer:</b> {$order->quantity}× Weekly Pass\n";
        }
        $message .= "👤 <b>User:</b> {$userName}\n";
        // Include seller information when present (seller storefront orders)
        if (isset($order->seller) && $order->seller) {
            $sellerName = $order->seller->name ?? $order->seller->username ?? 'Seller';
            $sellerUsername = $order->seller->username ?? null;
            $message .= "🏬 <b>Seller:</b> {$escape($sellerName)}";
            if ($sellerUsername) {
                $message .= " ({$sellerUsername})";
            }
            $message .= "\n";
        }
        $message .= "📧 <b>Email:</b> {$userEmail}\n";
        
        // Add game-specific details
        if ($gameType === 'mobilelegends') {
            if ($order->user_id_ml) {
                $message .= "🆔 <b>User ID:</b> {$escape($order->user_id_ml)}\n";
            }
            if ($order->zone_id_ml) {
                $message .= "🌍 <b>Zone ID:</b> {$escape($order->zone_id_ml)}\n";
            }
        } elseif ($gameType === 'freefire' && $order->player_id_ff) {
            $message .= "🆔 <b>Player ID:</b> {$escape($order->player_id_ff)}\n";
        } elseif ($gameType === 'pubgmobile' && $order->player_id_pubg) {
            $message .= "🆔 <b>Player ID:</b> {$escape($order->player_id_pubg)}\n";
        } elseif ($gameType === 'honorofkings' && $order->player_id_hok) {
            $message .= "🆔 <b>Player ID:</b> {$escape($order->player_id_hok)}\n";
        } elseif ($gameType === 'bloodstrike') {
            if ($order->user_id_bs) {
                $message .= "🆔 <b>User ID:</b> {$escape($order->user_id_bs)}\n";
            }
            if ($order->server_bs) {
                $message .= "🖥️ <b>Server:</b> {$escape($order->server_bs)}\n";
            }
        }
        
        // Add provider balance if available: prefer VipResellerStatus, fall back to DigiflazzStatus
        $balance = null;
        $latestVipStatus = $order->vipResellerStatuses()->latest()->first();
        if ($latestVipStatus) {
            if (isset($latestVipStatus->additional_data['balance'])) {
                $balance = $latestVipStatus->additional_data['balance'];
            } elseif (!empty($latestVipStatus->balance)) {
                $balance = $latestVipStatus->balance;
            }
        }

        if ($balance === null) {
            // Try DigiflazzStatus for buyer_last_saldo
            if (method_exists($order, 'digiflazzStatuses')) {
                $latestDig = $order->digiflazzStatuses()->latest()->first();
                if ($latestDig) {
                    $additional = $latestDig->additional_data ?? [];
                    $balance = $additional['buyer_last_saldo'] ?? $additional['buyer_last_saldo'] ?? ($latestDig->buyer_last_saldo ?? null);
                }
            }
        }

        if ($balance !== null) {
            $message .= "\n💳 <b>Provider Balance:</b> " . number_format($balance, 0) . " IDR";
        }
        
        // Format date in Algeria timezone (Africa/Algiers - UTC+1)
        $createdAt = $order->created_at->setTimezone('Africa/Algiers');
        $message .= "\n⏰ <b>Created:</b> " . $createdAt->format('Y-m-d H:i:s') . " (Algeria Time)";
        
        return $message;
    }
}

