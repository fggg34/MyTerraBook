<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rapyd API credentials
    |--------------------------------------------------------------------------
    |
    | The access/secret keys and base URL are issued from the Rapyd dashboard.
    | Use the sandbox base URL while testing and switch to the production URL
    | (https://api.rapyd.net) once you go live.
    |
    */
    'access_key' => env('RAPYD_ACCESS_KEY', ''),
    'secret_key' => env('RAPYD_SECRET_KEY', ''),
    'base_url' => rtrim((string) env('RAPYD_BASE_URL', 'https://sandboxapi.rapyd.net'), '/'),
    'webhook_secret' => env('RAPYD_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Commission model
    |--------------------------------------------------------------------------
    |
    | The platform only collects the commission online (via Rapyd card payment).
    | The remaining balance is paid in cash to the host on arrival and is never
    | processed by Rapyd.
    |
    | commission_rate: fraction of the total charged online (0.15 = 15%).
    |
    */
    'commission_rate' => (float) env('RAPYD_COMMISSION_RATE', 0.15),

    /*
    |--------------------------------------------------------------------------
    | Checkout defaults
    |--------------------------------------------------------------------------
    */
    'currency' => env('RAPYD_CURRENCY', 'USD'),
    'country' => env('RAPYD_COUNTRY', 'US'),
    'payment_method_types' => ['us_visa_card', 'us_mastercard'],

    /*
    |--------------------------------------------------------------------------
    | Redirect URLs (frontend SPA)
    |--------------------------------------------------------------------------
    |
    | Rapyd does NOT substitute placeholders in the redirect URL, so the
    | controller appends our own order_id / order_type query params (which are
    | known when the checkout is created) and the SPA polls the status by those.
    |
    */
    'frontend_url' => rtrim((string) env('RAPYD_FRONTEND_URL', env('FRONTEND_URL', 'http://127.0.0.1:5174')), '/'),
    'success_path' => env('RAPYD_SUCCESS_PATH', '/booking/rapyd/success'),
    'error_path' => env('RAPYD_ERROR_PATH', '/booking/rapyd/failed'),
];
