<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Listeners;

use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;

class ResetLocalizationStateListener
{
    public function handle(mixed $event): void
    {
        app(LocalizationManagerInterface::class)->reset();
    }
}
