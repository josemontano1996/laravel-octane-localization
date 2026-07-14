<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Illuminate\Support\Facades\Config;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Exceptions\InvalidConfiguration;

final class LocalizationConfig implements LocalizationConfigInterface
{
    public const string CONFIG_KEY = 'octane-localization';
    public const string DEFAULT_LOCALE_CONFIG_KEY = self::CONFIG_KEY . '.' .'default_locale';
    public const string FALLBACK_LOCALE_CONFIG_KEY = 'app.fallback_locale';
    public const string SUPPORTED_LOCALES_CONFIG_KEY = self::CONFIG_KEY . '.' .'supported_locales';
    public const string LOCALIZATION_PARAM_KEY = self::CONFIG_KEY . '.' .'parameter_key';
    public const string PRIMARY_DRIVERS_KEY = self::CONFIG_KEY . '.' .'drivers';
    public const string COOKIE_TTL_KEY = self::CONFIG_KEY . '.' .'cookie_ttl';
    public const string EXT_DRIVERS_KEY = self::CONFIG_KEY . '.' .'ext';
    public const string IS_REDIRECTION_ACTIVE = self::CONFIG_KEY . '.' .'redirections.active';
    public const string REDIRECTION_ROUTE_EXCEPTIONS = self::CONFIG_KEY . '.' .'redirections.redirections.except';

    private ?array $cachedSupported = null;
    private ?array $cachedLocalizationCodes = null;

    public function getPrimaryDrivers(): array
    {
        $key = self::PRIMARY_DRIVERS_KEY;
        $drivers = Config::get($key);

        if ($drivers === null) {
            throw InvalidConfiguration::missingKey($key);
        }

        if (!\is_array($drivers)) {
            throw InvalidConfiguration::invalidType($key, 'array');
        }

        if (\count($drivers) === 0) {
            throw InvalidConfiguration::missingValue($key);
        }

        return $drivers;
    }

    public function getAllExtensionDrivers(): array
    {
        $key = self::EXT_DRIVERS_KEY;
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

        $key = self::EXT_DRIVERS_KEY . ".{$extension}.drivers";

        $drivers = Config::get($key);

        if (!\is_array($drivers)) {
            return [];
        }

        return array_values($drivers);
    }

    public function getDefaultLocale(): string
    {
        $key = self::DEFAULT_LOCALE_CONFIG_KEY;
        $locale = Config::get($key);

        if ($locale === null) {
            throw InvalidConfiguration::missingKey($key);
        }

        if (!\is_string($locale)) {
            throw InvalidConfiguration::invalidType($key, 'string');
        }

        if (trim($locale) === '') {
            throw InvalidConfiguration::missingValue($key);
        }

        return $locale;
    }

    public function getDefaultFallbackLocale(): string
    {
        $key = self::FALLBACK_LOCALE_CONFIG_KEY;
        $fallback = Config::get($key);

        if ($fallback === null) {
            throw InvalidConfiguration::missingKey($key);
        }

        if (!\is_string($fallback)) {
            throw InvalidConfiguration::invalidType($key, 'string');
        }

        if (trim($fallback) === '') {
            throw InvalidConfiguration::missingValue($key);
        }

        return $fallback;
    }

    public function getSupportedLocales(): array
    {
        if ($this->cachedSupported !== null) {
            return $this->cachedSupported;
        }

        $configKey = self::SUPPORTED_LOCALES_CONFIG_KEY;
        $raw = Config::get($configKey);

        if ($raw == null) {
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
        if ($this->cachedLocalizationCodes !== null) {
            return $this->cachedLocalizationCodes;
        }

        // Reuse your existing normalization logic
        return $this->cachedLocalizationCodes = array_keys($this->getSupportedLocales());
    }


    public function isSupportedLocale(?string $locale): bool
    {
        if ($locale === null || $locale === '') {
            return false;
        }

        return \array_key_exists($locale, $this->getSupportedLocales());
    }

    public function getLocalizationParamKey(): string
    {
        $key = self::LOCALIZATION_PARAM_KEY;

        $parameterKey = Config::get($key);

        if ($parameterKey === null) {
            throw InvalidConfiguration::missingKey($key);
        }

        if (!\is_string($parameterKey)) {
            throw InvalidConfiguration::invalidType($key, 'string');
        }

        if (trim($parameterKey) === '') {
            throw InvalidConfiguration::missingValue($key);
        }
        return $parameterKey;
    }

    public function getCookieExpiration(): int
    {
        return (int) Config::get(self::COOKIE_TTL_KEY, 1440);
    }

    public function isRedirectionEnabled(): bool
    {
        $key = self::IS_REDIRECTION_ACTIVE;
        $val = Config::get($key, false);

        if (!\is_bool($val)) {
            throw InvalidConfiguration::invalidType($key, 'bool');
        }

        return $val;
    }

    public function getRedirectionExcludedPaths(): array
    {
        $key = self::REDIRECTION_ROUTE_EXCEPTIONS;
        $val = Config::get($key, []);

        if (!\is_array($val)) {
            throw InvalidConfiguration::invalidType($key, 'array');
        }

        return $val;

    }


}