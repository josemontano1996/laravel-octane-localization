<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;

interface LocalizationStateInterface
{
    public function get(): ?string;

    public function set(string $locale): void;

    public function exists(): bool;

    public function reset(): void;
}
