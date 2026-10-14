<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Featured giveaway slug
    |--------------------------------------------------------------------------
    | /giveaway redirects here so the weekly campaign stays on one URL even
    | after it ends.
    */
    'featured_slug' => env('GIVEAWAY_FEATURED_SLUG', 'weekly-pass'),

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
    | When true, spinning persists the winner on the giveaway.
    */
    'draw_live' => (bool) env('GIVEAWAY_DRAW_LIVE', true),

    /*
    |--------------------------------------------------------------------------
    | Public winner reveal
    |--------------------------------------------------------------------------
    | When true, participants see the winner card and the winner sees a
    | personal “you won” celebration.
    */
    'winner_reveal_enabled' => (bool) env('GIVEAWAY_WINNER_REVEAL_ENABLED', true),
];
