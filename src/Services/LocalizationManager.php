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
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationContextInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Exceptions\DriverException;

final readonly class LocalizationManager implements LocalizationManagerInterface
{
    public function __construct(
        private LocalizationConfigInterface $config,
        private LocalizationStateManagerInterface $state,
        private LocalizationContextInterface $context
    ) {}

    public function setLocale(string $locale): void
    {
        $target = $this->config->isSupportedLocale($locale)
            ? $locale
            : $this->config->getDefaultLocale();

        $this->state->set($target);
    }

    public function detect(Request $request): void
    {
        $driverClasses = $this->config->getPrimaryDrivers();
        $detectedLocales = [];
        $locale = $this->config->getDefaultLocale();

        // Pass 1: Run the stack and cache the actual string outputs
        foreach ($driverClasses as $driverClass) {
            $driver = $this->resolveDriver($driverClass);
            $result = $driver->getLocale($request);

            // Save what this specific driver read from the request
            $detectedLocales[$driverClass] = $result;

            if ($this->config->isSupportedLocale($result)) {
                $locale = $result;
                break;
            }
        }

        $this->state->set($locale);

        // Pass 2: Sync back using the cached string values
        foreach ($driverClasses as $driverClass) {
            $driver = $this->resolveDriver($driverClass);

            // If this driver hasn't run yet, or if its string result doesn't match our winner
            $previouslyDetected = $detectedLocales[$driverClass] ?? null;

            if ($previouslyDetected !== $locale) {
                $driver->storeLocale($locale, $request);
            }
        }
    }

    public function discover(Request $request, array $driverClasses): void
    {
        $locale = $this->runDriverStack($request, $driverClasses);

        $this->state->set($locale);
    }

    private function runDriverStack(Request $request, array $driverClasses): string
    {
        foreach ($driverClasses as $driverClass) {
            $result = $this->resolveDriver($driverClass)->getLocale($request);

            if ($this->config->isSupportedLocale($result)) {
                return $result;
            }
        }

        return $this->config->getDefaultLocale();
    }

    public function syncWithApplication(): void
    {
        $locale = $this->state->get();

        App::setLocale($locale);

        $this->context->set($locale);

        URL::defaults([$this->config->getLocalizationParamKey() => $locale]);

        if (class_exists(Carbon::class)) {
            Carbon::setLocale($locale);
        }

        if (class_exists(Number::class)) {
            Number::useLocale($locale);
        }
    }

    public function reset(): void
    {

        $defaultLocale = $this->config->getDefaultLocale();

        if (class_exists(Number::class)) {
            Number::useLocale($defaultLocale);
        }

        if (class_exists(Carbon::class)) {
            Carbon::setLocale($defaultLocale);
        }

        URL::defaults([$this->config->getLocalizationParamKey() => $defaultLocale]);

        App::setLocale($defaultLocale);

    }

    private function resolveDriver(string $class): LocaleDriverInterface
    {
        if (! class_exists($class)) {
            throw DriverException::notFound($class);
        }

        $driver = app($class);

        if (! $driver instanceof LocaleDriverInterface) {
            throw DriverException::invalidInterface($class, LocaleDriverInterface::class);
        }

        return $driver;
    }
}
