<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Providers;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationContextInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationRedirectorInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\SeoHelperInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\URLParserInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\CookieDriver;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LivewireLocalizationBridge;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LocalizationMiddleware;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LocalizationMiddlewareWithoutRedirect;
use Josemontano1996\LaravelOctaneLocalization\Registrars\RegisterBladeDirectives;
use Josemontano1996\LaravelOctaneLocalization\Registrars\RegisterMacros;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationConfig;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationContext;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationManager;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationRedirector;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationState;
use Josemontano1996\LaravelOctaneLocalization\Services\SeoHelper;
use Josemontano1996\LaravelOctaneLocalization\Services\URLParser;
use Override;

class LocalizationServiceProvider extends ServiceProvider
{
    public const string CONFIG_PATH = __DIR__.'/../../config/octane-localization.php';

    public const string CONFIG_KEY = 'octane-localization';

    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, self::CONFIG_KEY);

        // 1. Core Services (Singletons)
        $this->app->singleton(LocalizationConfigInterface::class, LocalizationConfig::class);
        $this->app->singleton(URLParserInterface::class, URLParser::class);

        $this->app->scoped(SeoHelperInterface::class, SeoHelper::class);
        $this->app->scoped(LocalizationRedirectorInterface::class, LocalizationRedirector::class);
        $this->app->scoped(LocalizationManagerInterface::class, LocalizationManager::class);
        $this->app->scoped(LocalizationContextInterface::class, LocalizationContext::class);

        // 2. Data/State (Scoped - Fresh for every request)
        $this->app->scoped(LocalizationStateInterface::class, LocalizationState::class);
        $this->app->scoped(LocalizationMiddleware::class);
        $this->app->scoped(LocalizationMiddlewareWithoutRedirect::class);
        $this->app->scoped(LivewireLocalizationBridge::class);

        $config = $this->app->make(LocalizationConfigInterface::class);

        // 3. Register Drivers
        $this->registerPrimaryDrivers($config);
    }

    private function registerPrimaryDrivers(LocalizationConfigInterface $config): void
    {
        $allUsedDrivers = array_unique([
            ...$config->getPrimaryDrivers(),
            ...$config->getAllExtensionDrivers(),
        ]);

        foreach ($allUsedDrivers as $driverClass) {
            // Special binding for CookieDriver
            if ($driverClass === CookieDriver::class) {
                $this->app->scoped($driverClass, fn (): CookieDriver => new CookieDriver(
                    $config
                ));

                continue;
            }

            // Standard binding for everyone else
            $this->app->scoped($driverClass);
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                self::CONFIG_PATH => config_path(self::CONFIG_KEY.'.php'),
            ], self::CONFIG_KEY);
        }
        // Lazy resolution
        $router = $this->app->make(Router::class);
        $router->prependMiddlewareToGroup('web', LivewireLocalizationBridge::class);

        RegisterMacros::register();
        RegisterBladeDirectives::register();
        $localeManager = $this->app->make(LocalizationManagerInterface::class);
        $localeManager->reset();

        Queue::before(function (JobProcessing $event) use ($localeManager): void {
            $localeManager->reset();
        });
    }
}
