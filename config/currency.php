<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Currency / Conversion settings
    |--------------------------------------------------------------------------
    |
    | usd_to_dzd - conversion multiplier used across the app when converting
    | USD prices to DZD for comparisons and seeding purposes. Default is 250.
    |
    */
    'usd_to_dzd' => env('USD_TO_DZD', 250),
];
