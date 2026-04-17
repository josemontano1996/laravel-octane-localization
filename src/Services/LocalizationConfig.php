<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Illuminate\Support\Facades\Config;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Exceptions\InvalidConfiguration;

final class LocalizationConfig implements LocalizationConfigInterface
{
    public const string DEFAULT_LOCALE_CONFIG_KEY = 'octane-localization.default_locale';

    public const string FALLBACK_LOCALE_CONFIG_KEY = 'app.fallback_locale';

    public const string SUPPORTED_LOCALES_CONFIG_KEY = 'octane-localization.supported_locales';

    public const string PARAMETER_KEY_CONFIG_KEY = 'octane-localization.parameter_key';

    public const string PRIMARY_DRIVERS_KEY = 'octane-localization.drivers';

    public const string COOKIE_TTL_KEY = 'octane-localization.cookie_ttl';

    public const string EXTENSIONS_KEY = 'octane-localization.ext';

    private ?array $cachedSupported = null;

    private ?array $cachedCodes = null;

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

    public function getAllExtensionDrivers(): array
    {
        $key = self::EXTENSIONS_KEY;

        $ext = (array) Config::get($key, []);

        $allExtDrivers = [];

        foreach ($ext as $extension) {
            if (isset($extension['drivers']) && \is_array($extension['drivers'])) {
                $allExtDrivers = [...$allExtDrivers, ...$extension['drivers']];
            }
        }

        return array_unique($allExtDrivers);
    }

    public function getExtensionDrivers(string $extension): array
    {

        $key = self::EXTENSIONS_KEY.".{$extension}.drivers";

        $drivers = Config::get($key);

        if (! \is_array($drivers)) {
            return [];
        }

        return array_values($drivers);
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

    public function getSupportedLocaleCodes(): array
    {
        if ($this->cachedCodes !== null) {
            return $this->cachedCodes;
        }

        // Reuse your existing normalization logic
        return $this->cachedCodes = array_keys($this->getSupportedLocales());
    }

    public function isSupportedLocale(?string $locale): bool
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

    public function getCookieExpiration(): int
    {
        return (int) Config::get(self::COOKIE_TTL_KEY, 1440);
    }

    public function isRedirectionEnabled(): bool
    {
        return (bool) config('localization.redirections.active', true);
    }

    public function getRedirectionExcludedPaths(): array
    {
        return (array) config('localization.redirections.except', []);
    }
}
