<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;

use Illuminate\Http\Request;

interface LocalizationManagerInterface
{
    /**
     * Iterate through drivers to identify and persist the locale.
     */
    public function detect(Request $request): void;

    /**
     * Apply the detected state to Laravel core (Translator, Carbon, URL, Number).
     */
    public function syncWithApplication(): void;

    /**
     * Reset the application state to defaults.
     * Essential for preventing state leakage in Octane.
     */
    public function flush(): void;
}
