<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Bot Token: Your Telegram bot token from @BotFather
    | Chat ID: The chat ID where notifications will be sent
    |         - For personal chats: Your user ID (get it from @userinfobot)
    |         - For groups: Negative number (e.g., -1001234567890)
    |         - Important: Start a conversation with the bot first!
    |
    | Add these to your .env file:
    | TELEGRAM_BOT_TOKEN=your_bot_token_here
    | TELEGRAM_CHAT_ID=your_chat_id_here
    |
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
    'chat_id' => env('TELEGRAM_CHAT_ID', ''),
    'api_url' => 'https://api.telegram.org/bot',
];

