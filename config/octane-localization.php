<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The locale that will be used when no supported locale is detected.
    | This is also the locale restored by the package when resetting
    | Octane and queue worker state between requests, 
    | SHOULD NOT reference the app.locale variable.
    |
    */
    'default_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | A whitelist of locale codes your application will accept.
    | Locales not included here will fall back to the package default.
    |
    */
    'supported_locales' => ['en'],

    /*
    |--------------------------------------------------------------------------
    | Route / Storage Parameter Key
    |--------------------------------------------------------------------------
    |
    | The locale key used by localized routes and persistence drivers.
    | It is applied to the URL parameter for localized routes and also
    | used when storing the locale into session or cookie drivers.
    |
    */
    'parameter_key' => 'locale',

    /*
    |--------------------------------------------------------------------------
    | Cookie TTL
    |--------------------------------------------------------------------------
    |
    | When using cookie-based locale storage, this value controls the
    | lifetime of the locale cookie in minutes.
    |
    */
    'cookie_ttl' => 1440,

    /*
    |--------------------------------------------------------------------------
    | Locale Detection Drivers
    |--------------------------------------------------------------------------
    |
    | Driver classes are executed in order for each incoming request.
    | The first driver that resolves a supported locale wins.
    | Some drivers may persist locale state (cookies/session), while others
    | are read-only and only detect the user's preferred locale.
    |
    */
    'drivers' => [
        // Josemontano1996\LaravelOctaneLocalization\Drivers\UrlDriver::class,
        Josemontano1996\LaravelOctaneLocalization\Drivers\SessionDriver::class,
        // Josemontano1996\LaravelOctaneLocalization\Drivers\CookieDriver::class,
        Josemontano1996\LaravelOctaneLocalization\Drivers\RequestPreferredLocaleDriver::class,
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
        'active' => false,
        // 'except' => ['api/*', 'webhooks/*'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Extension Drivers
    |--------------------------------------------------------------------------
    |
    | This section allows you to define driver stacks for package extensions
    | such as Livewire. The configured drivers will be used only when the
    | extension-specific request path is active.
    |
    */
    'ext' => [
        'livewire' => [
            'drivers' => [
                Josemontano1996\LaravelOctaneLocalization\Drivers\RefererDriver::class,
                Josemontano1996\LaravelOctaneLocalization\Drivers\SessionDriver::class,
                // Josemontano1996\LaravelOctaneLocalization\Drivers\CookieDriver::class,
                Josemontano1996\LaravelOctaneLocalization\Drivers\RequestPreferredLocaleDriver::class,
            ], ],
    ],
];
