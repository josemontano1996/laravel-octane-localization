<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;

use Illuminate\Http\Request;

interface LocalizationManagerInterface
{
    
    /**
     * Sets the current locale in the state manager.
     * 
     * This updates the in-memory state of the application. 
     * Note: This does not trigger persistence to external drivers.
     *
     * @param string $locale The locale code to set.
     */
    public function setLocale(string $locale): void;

    /**
     * Persists the given locale to all configured primary drivers.
     * 
     * This iterates through the primary drivers and triggers their storage logic 
     * (e.g., writing cookies, updating session data, or headers).
     *
     * @param string $locale The locale to persist.
     * @param Request $request The current HTTP request instance.
     */
    public function storeLocale(string $locale, Request $request): void;


    /**
     * Identify the locale and persist it to all configured drivers.
     * 
     * Used for primary localized routes where the language should be "remembered"
     * for future requests.
     *
     * @param Request $request The current HTTP request.
     */
    public function resolve(Request $request): void;

/**
     * Identify the locale for the current request context without persisting it.
     * 
     * Used for extensions like Livewire, API calls, or background requests 
     * where you want to detect the language but not change the user's saved preferences.
     *
     * @param Request $request The current HTTP request.
     * @param array<int, class-string> $driverClasses The stack of drivers to check.
     */
    public function discover(Request $request, array $driverClasses): void;

    /**
     * Apply the detected state to Laravel core components.
     * 
     * This synchronizes the state with the Translator, Carbon, URL defaults, and Number formatting.
     */
    public function syncWithApplication(): void;

    /**
     * Reset the application state to default values.
     * 
     * Essential for preventing state leakage when using persistent memory 
     * environments like Laravel Octane.
     */
    public function reset(): void;
}
