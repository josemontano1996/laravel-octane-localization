<?php

declare(strict_types=1);

use Josemontano1996\LaravelOctaneLocalization\Drivers\UrlDriver;

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

    'drivers' => [
        UrlDriver::class,
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
