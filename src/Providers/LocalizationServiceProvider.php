<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Providers;

use Illuminate\Support\ServiceProvider;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateInterface;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationConfig;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationState;
use Override;

class LocalizationServiceProvider extends ServiceProvider
{
    #[Override]
    public function register()
    {
        $this->mergeConfigFrom(LocalizationConfig::CONFIG_PATH, 'localization');

        $this->app->singleton(LocalizationConfigInterface::class, LocalizationConfig::class);

        $this->app->scoped(LocalizationStateInterface::class, LocalizationState::class);
    }

    public function boot(): void {}
}
