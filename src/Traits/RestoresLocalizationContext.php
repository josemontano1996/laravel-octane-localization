<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Traits;

use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationContextInterface;

trait RestoresLocalizationContext
{
    /**
     * Re-hydrates the application state based on the captured context.
     * This should be called at the beginning of the handle() method.
     */
    public function restoreLocalization(): void
    {
        $context = app(LocalizationContextInterface::class);
        $manager = app(LocalizationManagerInterface::class);
        
        if ($locale = $context->get()) {
            $manager->setLocale($locale);
            $manager->syncWithApplication();
        }
    }

    /**
     * Cleans up the state. Should be called at the end of the handle() 
     * or in the finally block.
     */
    public function resetLocalization(): void
    {
        app(LocalizationManagerInterface::class)->flush();
    }
}