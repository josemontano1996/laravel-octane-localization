<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Traits;

use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationContextInterface;
use Josemontano1996\LaravelOctaneLocalization\Queue\LocalizationQueueMiddleware;

trait LocalizedJob
{
    public function restoreLocalization(): void
    {
        $context = app(LocalizationContextInterface::class);
        $manager = app(LocalizationManagerInterface::class);
        
        if ($locale = $context->get()) {
            $manager->setLocale($locale);
            $manager->syncWithApplication();
        }
    }

    public function resetLocalization(): void
    {
        app(LocalizationManagerInterface::class)->flush();
    }
}