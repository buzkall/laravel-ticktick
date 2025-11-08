<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TickTick API Credentials
    |--------------------------------------------------------------------------
    |
    | Your TickTick API credentials. You can get these from:
    | https://developer.ticktick.com/
    |
    */

    'client_id'     => env('TICKTICK_CLIENT_ID'),
    'client_secret' => env('TICKTICK_CLIENT_SECRET'),
    'redirect_uri'  => env('TICKTICK_REDIRECT_URI'),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for TickTick API. You typically don't need to change this.
    |
    */

    'base_url' => env('TICKTICK_BASE_URL', 'https://api.ticktick.com'),

    /*
    |--------------------------------------------------------------------------
    | Open API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for TickTick Open API endpoints.
    |
    */

    'open_api_url' => env('TICKTICK_OPEN_API_URL', 'https://api.ticktick.com/open/v1'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for TickTick OAuth endpoints (authorization and token).
    |
    */

    'oauth_url' => env('TICKTICK_OAUTH_URL', 'https://ticktick.com'),

    /*
    |--------------------------------------------------------------------------
    | Access Token
    |--------------------------------------------------------------------------
    |
    | If you already have an access token, you can set it here.
    | This is useful for testing or when using a pre-authenticated token.
    |
    */

    'access_token' => env('TICKTICK_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout for API requests in seconds.
    |
    */

    'timeout' => env('TICKTICK_TIMEOUT', 30),
];
