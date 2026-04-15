<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Illuminate\Support\Facades\Context;

final readonly class LocalizationContext
{
    private const string KEY = 'localization.locale';

    public function hydrate(string $locale): void
    {
        Context::add(self::KEY, $locale);
    }

    public function get(): ?string
    {
        return Context::get(self::KEY);
    }

    public function forget(): void
    {
        Context::forget(self::KEY);
    }
}
