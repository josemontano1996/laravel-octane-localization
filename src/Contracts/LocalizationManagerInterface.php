<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;

use Illuminate\Http\Request;

interface LocalizationManagerInterface
{
    /**
     * Sets the locale state in the manager
     */
    public function setLocale(string $locale): void;

    /**
     * Identify the locale and persist it to all configured drivers.
     * Used for primary localized routes where the language should be "remembered".
     */
    public function detect(Request $request): void;

    /**
     * Identify the locale for the current request context without persisting it.
     * Used for extensions like Livewire or background requests.
     *
     * * @param array<int, class-string> $driverClasses
     */
    public function discover(Request $request, array $driverClasses): void;

    /**
     * Apply the detected state to Laravel core (Translator, Carbon, URL, Number).
     */
    public function syncWithApplication(): void;

    /**
     * Reset the application state to defaults.
     * Essential for preventing state leakage in Octane environments.
     */
    public function reset(): void;
}
