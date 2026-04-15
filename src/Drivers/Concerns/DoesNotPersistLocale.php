<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Drivers\Concerns;

use Symfony\Component\HttpFoundation\Request;

trait DoesNotPersistLocale
{
    public function storeLocale(string $locale, Request $request): void
    {
        // Intentionally empty for No-op drivers
    }
}
