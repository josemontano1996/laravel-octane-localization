<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Illuminate\Support\Facades\Context;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationContextInterface;

final readonly class LocalizationContext implements LocalizationContextInterface
{
    private const string KEY = 'laravel-octane-localization.locale';

    public function set(string $locale): void
    {
        Context::add(self::KEY, $locale);
    }

    public function get(): ?string
    {
        return Context::get(self::KEY);
    }
}
