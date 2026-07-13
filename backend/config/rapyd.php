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
    'currency' => env('RAPYD_CURRENCY', 'ISK'),
    'country' => env('RAPYD_COUNTRY', 'IS'),

    /*
    | Restrict the card brands offered on the hosted page. Leave empty (default)
    | to let Rapyd present every card method valid for the country/currency.
    | Production accounts must also enable the Card category in the Rapyd Client
    | Portal or checkout creation fails with
    | ERROR_HOSTED_PAGE_PAYMENT_METHOD_TYPE_CATEGORIES_NOT_ENABLED.
    | Provide a comma-separated list via RAPYD_PAYMENT_METHOD_TYPES to override
    | (e.g. is_visa_card,is_mastercard_card). Uses payment_method_types_include.
    */
    'payment_method_types' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('RAPYD_PAYMENT_METHOD_TYPES', ''))
    ))),

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
