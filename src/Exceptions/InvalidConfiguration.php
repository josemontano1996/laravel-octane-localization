<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Exceptions;

use RuntimeException;

final class InvalidConfiguration extends RuntimeException
{
    public static function missingKey(string $configKey): self
    {
        return new self("The configuration key [{$configKey}] is missing. This is required for the localization engine.");
    }

    public static function missingSupportedLocales(string $configKey): self
    {
        return new self("You must define at least one locale in [{$configKey}].");
    }
}
