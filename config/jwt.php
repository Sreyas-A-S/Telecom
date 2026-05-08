<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JWT time to live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time in minutes that the token will be valid for.
    |
    */
    'ttl' => (int) env('JWT_TTL', 525600),

    /*
    |--------------------------------------------------------------------------
    | Refresh time to live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time in minutes that the token can be refreshed within.
    |
    */
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 20160),

    /*
    |--------------------------------------------------------------------------
    | JWT hashing algorithm
    |--------------------------------------------------------------------------
    |
    | Specify the hashing algorithm that will be used to sign the token.
    |
    | See: https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
    | for a full list of supported algorithms.
    |
    */
    'algo' => env('JWT_ALGO', 'HS256'),

    /*
    |--------------------------------------------------------------------------
    | JWT secret key
    |--------------------------------------------------------------------------
    |
    | The secret key used to sign the token. Can be generated with the command:
    | `php artisan jwt:secret`
    |
    */
    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWT keys
    |--------------------------------------------------------------------------
    |
    | If you are using an RSA or ECDSA algorithm to sign the token, you will
    | need to specify the public and private keys here.
    |
    */
    'keys' => [

        'public' => env('JWT_PUBLIC_KEY', null),

        'private' => env('JWT_PRIVATE_KEY', null),

        'passphrase' => env('JWT_PASSPHRASE', null),

    ],

    /*
    |--------------------------------------------------------------------------
    | JWT Blacklist
    |--------------------------------------------------------------------------
    |
    | Here you can specify the name of the blacklist that you want to use.
    | You can also specify the name of the cache driver that you want to use.
    |
    */
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    'blacklist_grace_period' => (int) env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    'blacklist_storage' => env('JWT_BLACKLIST_STORAGE', 'tymon'),

    /*
    |--------------------------------------------------------------------------
    | JWT providers
    |--------------------------------------------------------------------------
    |
    | Here you may specify the providers used to store and retrieve your users.
    |
    | Supported: "user", "jwt"
    |
    */
    'providers' => [

        'user' => 'Tymon\\JWTAuth\\Providers\\User\\EloquentUserAdapter',

        'jwt' => 'Tymon\\JWTAuth\\Providers\\JWT\\Lcobucci',

        'storage' => 'Tymon\JWTAuth\Providers\Storage\Illuminate',

    ],


];
