<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Providers;

use Illuminate\Support\ServiceProvider;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateInterface;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationConfig;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationManager;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationState;
use LocalizationManagerInterface;
use Override;

class LocalizationServiceProvider extends ServiceProvider
{
    private const string  CONFIG_PATH = __DIR__.'/../../config/localization.php';

    #[Override]
    public function register()
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'localization');

        $this->app->singleton(LocalizationConfigInterface::class, LocalizationConfig::class);
        $this->app->scoped(LocalizationManagerInterface::class, LocalizationManager::class);

        $this->app->scoped(LocalizationStateInterface::class, LocalizationState::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/localization.php' => config_path('localization.php'),
            ], 'localization-config');
        }
    }
}
