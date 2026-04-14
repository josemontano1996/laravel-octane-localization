<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;

// You MUST import the DTO here for the IDE to "see" it in the docblock
use Josemontano1996\LaravelOctaneLocalization\DataObjects\SupportedLocale;
use Josemontano1996\LaravelOctaneLocalization\Exceptions\InvalidConfiguration;

interface LocalizationConfigInterface
{
    /**
     * Get the application's primary default locale.
     *
     * @throws InvalidConfiguration
     */
    public function getDefaultLocale(): string;

    /**
     * Get the application's fallback locale.
     *
     * @throws InvalidConfiguration
     */
    public function getDefaultFallbackLocale(): string;

    /**
     * Get the list of supported locales as DTO objects.
     *
     * @return array<string, SupportedLocale>
     *
     * @throws InvalidConfiguration
     */
    public function getSupportedLocales(): array;

    /**
     * Validates if a locale is supported.
     */
    public function isSupported(?string $locale): bool;

    /**
     * Get the query parameter or input key used to identify the locale.
     *
     * * @example 'locale', 'lang', or 'hl'
     *
     * @throws InvalidConfiguration
     */
    public function getParameterKey(): string;
}
