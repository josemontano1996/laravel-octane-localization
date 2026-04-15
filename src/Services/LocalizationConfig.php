<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Illuminate\Support\Facades\Config;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Exceptions\InvalidConfiguration;

final class LocalizationConfig implements LocalizationConfigInterface
{
    public const string DEFAULT_LOCALE_CONFIG_KEY = 'app.locale';

    public const string FALLBACK_LOCALE_CONFIG_KEY = 'app.fallback_locale';

    public const string SUPPORTED_LOCALES_CONFIG_KEY = 'localization.supported_locales';

    public const string PARAMETER_KEY_CONFIG_KEY = 'localization.parameter_key';

    public const string PRIMARY_DRIVERS_KEY = 'localization.drivers';

    private ?array $cachedSupported = null;

    public function getPrimaryDrivers(): array
    {
        $key = self::PRIMARY_DRIVERS_KEY;
        $drivers = Config::get($key);

        if (empty($drivers)) {
            throw InvalidConfiguration::missingKey($key);
        }

        if (! \is_array($drivers)) {
            throw InvalidConfiguration::invalidType($key, 'array');
        }

        return $drivers;
    }

    public function getDefaultLocale(): string
    {
        $key = self::DEFAULT_LOCALE_CONFIG_KEY;
        $locale = Config::get($key);

        if (empty($locale)) {
            throw InvalidConfiguration::missingKey($key);
        }

        return (string) $locale;
    }

    public function getDefaultFallbackLocale(): string
    {
        $key = self::FALLBACK_LOCALE_CONFIG_KEY;
        $fallback = Config::get($key);

        if (empty($fallback)) {
            throw InvalidConfiguration::missingKey($key);
        }

        return (string) $fallback;
    }

    public function getSupportedLocales(): array
    {
        if ($this->cachedSupported !== null) {
            return $this->cachedSupported;
        }

        $configKey = self::SUPPORTED_LOCALES_CONFIG_KEY;
        $raw = Config::get($configKey);

        if (empty($raw)) {
            throw InvalidConfiguration::missingKey($configKey);
        }

        $locales = \is_string($raw) ? [$raw] : (array) $raw;
        $normalized = [];

        foreach ($locales as $localeCode => $value) {
            if (\is_int($localeCode)) {
                // Handle simple list: ['en', 'es']
                $normalized[$value] = ['name' => $value];

                continue;
            }

            // Handle detailed list: ['en' => ['name' => 'English']]
            $normalized[$localeCode] = $value;
        }

        return $this->cachedSupported = $normalized;
    }

    public function isSupported(?string $locale): bool
    {
        if ($locale === null || $locale === '') {
            return false;
        }

        return \array_key_exists($locale, $this->getSupportedLocales());
    }

    public function getParameterKey(): string
    {
        $key = self::PARAMETER_KEY_CONFIG_KEY;

        $parameterKey = Config::get($key);

        if (empty($parameterKey)) {
            throw InvalidConfiguration::missingKey($key);
        }

        return (string) $parameterKey;
    }
}
