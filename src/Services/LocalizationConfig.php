<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Illuminate\Support\Facades\Config;
use Josemontano1996\LaravelOctaneLocalization\Exceptions\InvalidConfiguration;

final readonly class LocalizationConfig
{
    public const string CONFIG_PATH = __DIR__.'/../../config/localization.php';
    public const string DEFAULT_LOCALE_CONFIG_KEY = 'app.locale';
    public const string FALLBACK_LOCALE_CONFIG_KEY = 'app.fallback_locale';
    public const string SUPPORTED_LOCALES_CONFIG_KEY = 'localization.supported_locales';

    /**
     * @throws InvalidConfiguration
     */
    public function getDefaultLocale(): string
    {
        $key = self::DEFAULT_LOCALE_CONFIG_KEY;
        $locale = Config::get($key);

        if (empty($locale)) {
            throw InvalidConfiguration::missingKey($key);
        }

        return (string) $locale;
    }

    /**
     * @throws InvalidConfiguration
     */
    public function getDefaultFallbackLocale(): string
    {
        $key = self::FALLBACK_LOCALE_CONFIG_KEY;
        $fallback = Config::get($key);

        if (empty($fallback)) {
            throw InvalidConfiguration::missingKey($key);
        }

        return (string) $fallback;
    }

    /**
     * @throws InvalidConfiguration
     */
    public function getSupportedLocales(): array
    {
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

        return $normalized;
    }
}