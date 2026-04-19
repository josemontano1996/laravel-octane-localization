<?php

namespace Josemontano1996\LaravelOctaneLocalization\Tests;

use Josemontano1996\LaravelOctaneLocalization\Drivers\RefererDriver;
use Josemontano1996\LaravelOctaneLocalization\Drivers\RequestPreferredLocaleDriver;
use Josemontano1996\LaravelOctaneLocalization\Drivers\SessionDriver;
use Josemontano1996\LaravelOctaneLocalization\Drivers\UrlDriver;
use Josemontano1996\LaravelOctaneLocalization\Providers\LocalizationServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public const string DEFAULT_LOCALE = 'en';

    public const string FALLBACK_LOCALE = 'es';

    public const array SUPPORTED_LOCALES = ['en', 'es', 'fr'];

    public const string UNSUPPORTED_LOCALE = 'de';

    public const string ALTERNATIVE_LOCALE = 'fr';

    public const string PARAMETER_KEY = 'locale';

    public const string EXCEPT_REDIRECTION_ROUTE = 'api/*';

    protected function getPackageProviders($app): array
    {
        return [
            LocalizationServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['router']->middlewareGroup('web', []);
        // 1. Basic Laravel Setup
        $app['config']->set('database.default', 'testing');
        $app['config']->set('app.locale', self::DEFAULT_LOCALE);
        $app['config']->set('app.fallback_locale', self::FALLBACK_LOCALE);

        // 2. Package-specific Configuration
        // We use the same 'octane-localization' key defined in your ServiceProvider
        $app['config']->set('octane-localization', [
            'default_locale' => self::DEFAULT_LOCALE,
            'parameter_key' => self::PARAMETER_KEY,
            'supported_locales' => self::SUPPORTED_LOCALES,
            'cookie_ttl' => 1440,

            'drivers' => [
                UrlDriver::class,
                SessionDriver::class,
                RequestPreferredLocaleDriver::class,
            ],

            'redirections' => [
                'active' => true,
                'except' => [self::EXCEPT_REDIRECTION_ROUTE],
            ],

            'ext' => [
                'livewire' => [
                    'drivers' => [
                        RefererDriver::class,
                        SessionDriver::class,
                        RequestPreferredLocaleDriver::class,
                    ],
                ],
            ],
        ]);
    }
}
