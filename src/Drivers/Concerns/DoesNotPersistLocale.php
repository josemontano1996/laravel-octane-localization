<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Drivers\Concerns;

trait DoesNotPersistLocale
{
    public function storeLocale(string $locale): void
    {
        // Intentionally empty for No-op drivers
    }
}
