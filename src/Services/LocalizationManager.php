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
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateInterface;
use Josemontano1996\LaravelOctaneLocalization\Exceptions\DriverException;

final readonly class LocalizationManager implements LocalizationManagerInterface
{
    public function __construct(
        private LocalizationConfigInterface $config,
        private LocalizationStateInterface $state,
    ) {}

    public function detect(Request $request): void
    {
        $locale = $this->runDriverStack($request, $this->config->getPrimaryDrivers());

        $this->state->set($locale);

        foreach ($this->config->getPrimaryDrivers() as $driverClass) {
            $this->resolveDriver($driverClass)->storeLocale($locale, $request);
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
            throw DriverException::invalidInterface($class, LocaleDriverInterface::class);
        }

        return $driver;
    }
}
