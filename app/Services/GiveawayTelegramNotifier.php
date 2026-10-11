<?php

namespace App\Services;

use App\Models\GiveawayEntry;
use App\Support\SafeLog;
use Illuminate\Support\Facades\Log;

class GiveawayTelegramNotifier
{
    /**
     * Send (or refresh) the Telegram message for a giveaway entry and persist message id.
     */
    public function sync(GiveawayEntry $entry, bool $forceNew = false): void
    {
        try {
            $entry->loadMissing(['user', 'giveaway', 'referrals.referredUser']);

            $text = TelegramService::formatGiveawayEntryMessage($entry);

            if (! $forceNew && $entry->tlg_message_id) {
                $ok = TelegramService::editMessageText((int) $entry->tlg_message_id, $text);
                if ($ok) {
                    return;
                }
            }

            $messageId = TelegramService::sendMessage($text);
            if ($messageId) {
                $entry->forceFill(['tlg_message_id' => $messageId])->save();
            }
        } catch (\Throwable $e) {
            SafeLog::warning('Giveaway Telegram sync failed', [
                'entry_id' => $entry->id,
                'error' => $e->getMessage(),
            ]);
            Log::warning('Giveaway Telegram sync failed', [
                'entry_id' => $entry->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
