<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Exceptions;

use InvalidArgumentException;

class InvalidLocale extends InvalidArgumentException
{
    public static function becauseItIsEmpty(): self
    {
        return new self("The locale cannot be empty.");
    }

    public static function unsupported(string $locale): self
    {
        return new self("The locale [{$locale}] is not in your supported list.");
    }
}