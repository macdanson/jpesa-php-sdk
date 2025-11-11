<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JPesa Base URL & Key
    |--------------------------------------------------------------------------
    |
    | Base URL defaults to the official JPesa API host.
    | The key is read from env(JPESA_API_KEY) by default.
    |
    */
    'base_url' => env('JPESA_BASE_URL', 'https://my.jpesa.com/api/'),
    'key'      => env('JPESA_API_KEY', ''),

    /*
    | Request timeout in seconds
    */
    'timeout'  => env('JPESA_TIMEOUT', 30),
];
