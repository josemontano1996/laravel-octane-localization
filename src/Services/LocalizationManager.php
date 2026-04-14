<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateInterface;
use Josemontano1996\LaravelOctaneLocalization\Exceptions\DriverException;

final readonly class LocalizationManager
{
    public function __construct(
        private LocalizationConfigInterface $config,
        private LocalizationStateInterface $state,
    ) {}

    public function detect(Request $request): void
    {
        $driverClasses = config('localization.drivers', []);

        $resolvedDrivers = [];

        // 1. Resolve and Detect
        foreach ($driverClasses as $driverClass) {
            $driver = $this->resolveDriver($driverClass);
            $resolvedDrivers[] = $driver;

            if (! $this->state->exists()) {
                $result = $driver->getLocale($request);
                if ($this->config->isSupported($result)) {
                    $this->state->set($result);
                }
            }
        }

        // 2. Final Fallback (if no driver found anything)
        if (! $this->state->exists()) {
            $this->state->set($this->config->getDefaultLocale());
        }

        // 3. Global Sync (Persistence)
        $finalLocale = $this->state->get();
        foreach ($resolvedDrivers as $driver) {
            $driver->storeLocale($finalLocale);
        }
    }

    public function syncWithApplication(): void
    {
        $locale = $this->state->get();

        App::setLocale($locale);

        URL::defaults([$this->config->getParameterKey() => $locale]);

        if (class_exists(Carbon::class)) {
            Carbon::setLocale($locale);
        }

        if (class_exists(Number::class)) {
            Number::useLocale($locale);
        }
    }

    public function flush(): void
    {
        $defaultLocale = $this->config->getDefaultLocale();

        if (class_exists(Number::class)) {
            Number::useLocale($defaultLocale);
        }

        if (class_exists(Carbon::class)) {
            Carbon::setLocale($defaultLocale);
        }
        URL::defaults([$this->config->getParameterKey() => null]);
        
        App::setLocale($defaultLocale);

        $this->state->reset();
    }

    private function resolveDriver(string $class): LocaleDriverInterface
    {
        if (! class_exists($class)) {
            throw DriverException::notFound($class);
        }

        $driver = app($class);

        if (! $driver instanceof LocaleDriverInterface) {
            throw DriverException::invalidInterface($class);
        }

        return $driver;
    }
}
