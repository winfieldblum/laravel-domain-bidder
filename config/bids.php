<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bid notification email
    |--------------------------------------------------------------------------
    |
    | Fallback recipient when a domain does not define notification_email.
    |
    */

    'notification_email' => env('BID_NOTIFICATION_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Minimum bid amount (USD, whole dollars)
    |--------------------------------------------------------------------------
    */

    'minimum_amount' => 100,

    /*
    |--------------------------------------------------------------------------
    | UI bid increment over the current highest accepted bid
    |--------------------------------------------------------------------------
    */

    'increment' => 100,

    /*
    |--------------------------------------------------------------------------
    | Rebid invitation token lifetime
    |--------------------------------------------------------------------------
    |
    | When a bid is accepted, lower verified bidders receive a one-time link
    | that stays valid for this many hours.
    |
    */

    'rebid_token_hours' => (int) env('BID_REBID_TOKEN_HOURS', 24),

];
