<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Facades;

use Illuminate\Support\Facades\Facade;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationServiceInterface;

/**
 * @method static string getLocale() Retrieves the currently active locale code (e.g., 'en', 'es_ES').
 * 
 * @method static void setLocale(string $locale, ?\Illuminate\Http\Request $request = null) Sets the current application locale and synchronizes the environment. 
 * If a request is provided, it triggers the drivers to persist the locale.
 * 
 * @method static bool isSupported(string $locale) Determines if the provided locale is valid and supported by the application.
 * 
 * @method static array getSupportedLocales() Retrieves the list of all supported ISO locale codes with their metadata.
 * 
 * @method static string[] getSupportedLocaleCodes() Retrieves the list of all supported ISO locale codes.
 * 
 * @method static string getDefaultLocale() Retrieves the default locale defined in the configuration.
 * 
 * @method static void resolve(\Illuminate\Http\Request $request) Executes the full locale resolution process. Intended for Middleware or ServiceProviders.
 * 
 * @method static void reset() Resets the application localization state to defaults. @internal For framework lifecycle management (e.g., Octane).
 * 
 * @see \Josemontano1996\LaravelOctaneLocalization\Services\LocalizationService
 */
final class Localization extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return LocalizationServiceInterface::class;
    }
}