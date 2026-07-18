<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;

use Illuminate\Http\Request;

interface LocalizationServiceInterface
{
    /**
     * Retrieves the currently active locale code.
     *
     * @return string The ISO locale code (e.g., 'en', 'es_ES').
     */
    public function getLocale(): string;

    /**
     * Sets the current application locale and synchronizes the environment.
     * 
     * This method validates the locale, updates the internal memory state, and syncs 
     * the application environment (Translator, Carbon, URL, Number).
     * 
     * If a Request object is provided, it triggers the drivers to persist the locale 
     * (e.g., updating cookies or headers). If null, persistence is skipped, 
     * making this safe for use in background jobs, console commands, or testing.
     *
     * @param string $locale The ISO locale code to apply.
     * @param Request|null $request The optional HTTP request instance to persist against.
     */
    public function setLocale(string $locale, ?Request $request = null): void;

    /**
     * Determines if the provided locale is valid and supported by the application.
     *
     * @param string $locale The ISO locale code to check.
     * @return bool True if supported, false otherwise.
     */
    public function isSupported(string $locale): bool;

    /**
     * Get the full configuration for all supported locales.
     * 
     * @return array<string, array<string, mixed>> Mapping of locale code to configuration details.
     */
    public function getSupportedLocales(): array;

    /**
     * Get the list of all supported locale codes.
     * 
     * Useful for validation or URL generation where only the code is required.
     *
     * @return string[] List of supported locale codes.
     */
    public function getSupportedLocaleCodes(): array;

    /**
     * Retrieves the default locale defined in the configuration.
     *
     * @return string The default ISO locale code.
     */
    public function getDefaultLocale(): string;

    /**
     * Executes the full locale resolution process.
     * 
     * This detects the locale from available drivers (headers, cookies, etc.), 
     * updates the application state, and synchronizes the environment.
     * 
     * This is intended to be called by Middleware or ServiceProviders.
     *
     * @param Request $request The current incoming request.
     */
    public function resolve(Request $request): void;

    /**
     * Resets the application localization state to its defaults.
     * 
     * @internal This method is intended for framework lifecycle management (e.g., Octane)
     * or manual state cleanup. Avoid usage in standard application logic.
     */
    public function reset(): void;
}