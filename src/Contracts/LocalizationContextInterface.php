<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;

interface LocalizationContextInterface
{
    /**
     * Retrieve the locale from the current Context.
     */
    public function get(): ?string;

    /**
     * Store the locale in the Laravel 11 Context.
     */
    public function set(string $locale): void;
}
