<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;

interface LocalizationContextInterface
{
    /**
     * Store the locale in the Laravel 11 Context.
     */
    public function hydrate(string $locale): void;

    /**
     * Retrieve the locale from the current Context.
     */
    public function get(): ?string;

    /**
     * Remove the localization data from the Context.
     */
    public function forget(): void;

    /**
     * Check if a localization context currently exists.
     */
    public function has(): bool;
}
