<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Exceptions;

use InvalidArgumentException;

final class DriverException extends InvalidArgumentException
{
    public static function invalidInterface(string $driverClass): self
    {
        return new self(
            "The driver [{$driverClass}] must implement Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface.");
    }

    public static function notFound(string $driverClass): self
    {
        return new self("The localization driver class [{$driverClass}] does not exist.");
    }
}
