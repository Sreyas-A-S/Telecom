<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Terminal Route Prefix
    |--------------------------------------------------------------------------
    |
    | The path where the terminal will be accessible.
    |
    */
    'route_prefix' => 'unslay-terminal',

    /*
    |--------------------------------------------------------------------------
    | Terminal Password
    |--------------------------------------------------------------------------
    |
    | The password required to access the terminal.
    | It is highly recommended to set this in your .env file.
    |
    */
    'password' => env('UNSLAY_SHELL_PASSWORD', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to the terminal routes.
    | The 'web' middleware is usually required for sessions.
    |
    */
    'middleware' => [
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],


    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Master switch to enable/disable the terminal.
    |
    */
    'enabled' => env('UNSLAY_SHELL_ENABLED', true),
];
