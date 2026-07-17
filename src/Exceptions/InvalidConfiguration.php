<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Exceptions;

use RuntimeException;

final class InvalidConfiguration extends RuntimeException
{
    public static function missingKey(string $configKey): self
    {
        return new self(
            "The configuration key '{$configKey}' is missing. " .
            "Please ensure this key exists in your localization config file."
        );
    }

    public static function missingValue(string $configKey): self
    {
        return new self(
            "The configuration key '{$configKey}' cannot be empty. " .
            "Please provide a valid value for this setting."
        );
    }

    public static function missingSupportedLocales(string $configKey): self
    {
        return new self(
            "No locales found under '{$configKey}'. " .
            "You must define at least one supported locale code to run the localization engine."
        );
    }

    public static function invalidType(string $configKey, string $expectedType): self
    {
        return new self(
            "Invalid type for '{$configKey}'. " .
            "The value must be of type [{$expectedType}]."
        );
    }
}