<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts\Support;

/**
 * Mark a class as capable of re-hydrating localization
 * state from the Laravel 11 Context.
 */
interface LocalizationAwareInterface
{
    public function restoreLocalization(): void;

    /**
     * Cleans up the state. Should be called at the end of the handle()
     * or in the finally block.
     */
    public function resetLocalization(): void;
}
