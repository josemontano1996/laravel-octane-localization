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
     * Get the flat list of supported ISO codes.
     *
     * * @return string[]
     */
    public function getSupportedLocaleCodes(): array;

    /**
     * Validates if a locale is supported.
     */
    public function isSupportedLocale(?string $locale): bool;

    /**
     * Get the query parameter or input key used to identify the locale.
     *
     * * @example 'locale', 'lang', or 'hl'
     *
     * @throws InvalidConfiguration
     */
    public function getParameterKey(): string;

    /**
     * Get the list of driver class names responsible for locale detection and persistence.
     *
     * @return array<int, class-string>
     *
     * @throws InvalidConfiguration
     */
    public function getPrimaryDrivers(): array;

    /**
     * Get the list of driver class names responsible for locale detection for an external packages.
     *
     * @return array<int, class-string>
     */
    public function getExtensionDrivers(string $extension): array;

    /**
     * Get the full unique list of driver class names responsible for locale detection for all external packages.
     *
     * @return array<int, class-string>
     */
    public function getAllExtensionDrivers(): array;

    /**
     * Get the cookie expiration time in minutes.
     *
     * * @example 1440 (24 hours)
     */
    public function getCookieExpiration(): int;

       /**
     * Checks if redirections are enabled.
     *
     */
    public function isRedirectionEnabled(): bool;

    /**
     * Get the redirection excluded paths.
     *
     */
    public function getRedirectionExcludedPaths(): array;
}
