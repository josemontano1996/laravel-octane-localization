<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateInterface;
use Josemontano1996\LaravelOctaneLocalization\Exceptions\InvalidLocale;

final class LocalizationState implements LocalizationStateInterface
{
    public function __construct(
        private ?string $locale = null
    ) {}

    public function get(): ?string
    {
        return $this->locale;
    }

    public function set(string $locale): void
    {
        $trimmed = trim($locale);

        if ($trimmed === '' || $trimmed === '0') {
            throw InvalidLocale::becauseItIsEmpty();
        }

        $this->locale = $trimmed;
    }

    public function exists(): bool
    {
        return $this->locale !== null;
    }

    public function reset(): void
    {
        $this->locale = null;
    }
}
