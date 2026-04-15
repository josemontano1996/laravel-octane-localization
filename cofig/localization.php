<?php

declare(strict_types=1);


return [

    'parameter_key' => 'locale',

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | The list of locales that your application supports.
    |
    */
    'supported_locales' => ['en'],


    /*
    |--------------------------------------------------------------------------
    | IN case using cookie drivers
    |--------------------------------------------------------------------------
    |
    | The ttl of the cookies
    |
    */
    'localization.cookie_ttl' => 1440,

    'drivers' => [
        Josemontano1996\LaravelOctaneLocalization\Drivers\UrlDriver::class,
        Josemontano1996\LaravelOctaneLocalization\Drivers\SessionDriver::class,
        // Josemontano1996\LaravelOctaneLocalization\Drivers\CookieDriver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore Paths (Except)
    |--------------------------------------------------------------------------
    |
    | The following paths will be excluded from automatic localization
    | redirection. This is useful for APIs, webhooks, or other non-localized
    | endpoints. You may use "*" as a wildcard.
    |
    */
    'redirections' => [
        'active' => true,
        'except' => ['api/*', 'webhooks/*'],
    ],

    'ext' => [
        'livewire' => [
            // FirstDriver::class
        ],
    ],
];
