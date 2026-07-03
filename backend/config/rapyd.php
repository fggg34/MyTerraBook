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
    | commission_rate: fraction of the total charged online (0.20 = 20%).
    |
    */
    'commission_rate' => (float) env('RAPYD_COMMISSION_RATE', 0.20),

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
    | Rapyd redirects the guest back to these URLs after the hosted checkout.
    | {CHECKOUT_ID} is replaced with the real checkout id at runtime.
    |
    */
    'complete_payment_url' => env('RAPYD_COMPLETE_URL', env('FRONTEND_URL', 'http://127.0.0.1:5174').'/booking/rapyd/success?checkout_id={CHECKOUT_ID}'),
    'error_payment_url' => env('RAPYD_ERROR_URL', env('FRONTEND_URL', 'http://127.0.0.1:5174').'/booking/rapyd/failed?checkout_id={CHECKOUT_ID}'),
];
