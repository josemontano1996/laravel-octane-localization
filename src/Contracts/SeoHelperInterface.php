<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;

interface SeoHelperInterface
{
    /**
     * Generate HTML <link rel="alternate" ... /> tags for all supported locales.
     * Includes the x-default tag pointing to the default locale.
     */
    public function getAlternateLinks(): string;
}
