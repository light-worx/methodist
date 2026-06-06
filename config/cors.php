<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/public/*',   // <-- add this; keep your existing api/* entry
        'api/*',
    ],

    'allowed_methods' => ['GET'],   // read-only — no need for POST/PUT/DELETE

    /*
    * '*' allows any origin. In production replace with an explicit list:
    *   'allowed_origins' => ['https://your-partner-site.com'],
    */
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 3600,          // 1-hour preflight cache

    'supports_credentials' => false,
];