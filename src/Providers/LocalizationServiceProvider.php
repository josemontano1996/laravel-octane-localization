<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessing;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationContextInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationRedirectorInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\URLParserInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\CookieDriver;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LivewireLocalizationBridge;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LocalizationMiddleware;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LocalizationMiddlewareWithoutRedirect;
use Josemontano1996\LaravelOctaneLocalization\Registrars\BladeDirectives;
use Josemontano1996\LaravelOctaneLocalization\Registrars\Macros;
use Override;

class LocalizationServiceProvider extends ServiceProvider
{
    public const string CONFIG_PATH = __DIR__ . '/../../config/octane-localization.php';

    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, LocalizationConfig::CONFIG_KEY);


        $this->app->scoped(URLParserInterface::class, URLParser::class);
        $this->app->scoped(LocalizationConfigInterface::class, LocalizationConfig::class);
        $this->app->scoped(LocalizationRedirectorInterface::class, LocalizationRedirector::class);
        $this->app->scoped(LocalizationManagerInterface::class, LocalizationManager::class);
        $this->app->scoped(LocalizationContextInterface::class, LocalizationContext::class);
        $this->app->scoped(LocalizationStateManagerInterface::class, LocalizationStateManager::class);

        $this->app->scoped(LocalizationMiddleware::class);
        $this->app->scoped(LocalizationMiddlewareWithoutRedirect::class);
        $this->app->scoped(LivewireLocalizationBridge::class);

        $this->registerDrivers();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                self::CONFIG_PATH => config_path(LocalizationConfig::CONFIG_KEY . '.php'),
            ], LocalizationConfig::CONFIG_KEY);
        }
        // Lazy resolution
        $router = $this->app->make(Router::class);
        $router->prependMiddlewareToGroup('web', LivewireLocalizationBridge::class);

        Macros::register();
        BladeDirectives::register();

        $localeManager = $this->app->make(LocalizationManagerInterface::class);
        $localeManager->reset();

        Queue::before(function (JobProcessing $event) use ($localeManager): void {
            $localeManager->reset();
        });
    }

    private function registerDrivers(): void
    {
        $primaryDrivers = config(LocalizationConfig::PRIMARY_DRIVERS_KEY, []);
        $extensionDrivers = config(LocalizationConfig::EXT_DRIVERS_KEY, []);

        $allRegisteredDrivers = array_unique([
            ...$primaryDrivers,
            ...$extensionDrivers,
        ]);

        foreach ($allRegisteredDrivers as $driverClass) {
            // Special binding for CookieDriver
            if ($driverClass === CookieDriver::class) {
                $this->app->scoped($driverClass, fn(): CookieDriver => new CookieDriver(
                    $this->app->make(LocalizationConfigInterface::class)
                ));

                continue;
            }

            // Standard binding for everyone else
            $this->app->scoped($driverClass);
        }
    }
}