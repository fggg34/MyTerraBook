<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Built SPA index.html
    |--------------------------------------------------------------------------
    |
    | Path to the production Vite build entry (usually public_html/index.html,
    | one level above the Laravel app directory).
    |
    */
    'index_path' => env('SPA_INDEX_PATH', dirname(base_path()).'/index.html'),

    /*
    |--------------------------------------------------------------------------
    | Bootstrap injection marker
    |--------------------------------------------------------------------------
    |
    | Place this exact HTML comment in frontend/index.html before </head>.
    | The SPA shell replaces it with an inline bootstrap script.
    |
    */
    'bootstrap_marker' => '<!-- MYTERRABOOK_SITE_BOOTSTRAP -->',
];
