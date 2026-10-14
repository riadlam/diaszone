<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin wheel email
    |--------------------------------------------------------------------------
    | Only this logged-in email can see and spin the giveaway draw wheel.
    */
    'wheel_admin_email' => env('GIVEAWAY_WHEEL_ADMIN_EMAIL', 'riadlaamari@gmail.com'),

    /*
    |--------------------------------------------------------------------------
    | Live draw
    |--------------------------------------------------------------------------
    | When false (default), spins are preview-only and do not persist a winner.
    */
    'draw_live' => (bool) env('GIVEAWAY_DRAW_LIVE', false),

    /*
    |--------------------------------------------------------------------------
    | Public winner reveal
    |--------------------------------------------------------------------------
    | When false (default), the logged-in winner sparkle UI stays hidden even
    | if a winner_entry_id is stored.
    */
    'winner_reveal_enabled' => (bool) env('GIVEAWAY_WINNER_REVEAL_ENABLED', false),
];
