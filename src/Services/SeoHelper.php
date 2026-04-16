<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\SeoHelperInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\URLParserInterface;

final readonly class SeoHelper implements SeoHelperInterface
{
    public function __construct(
        private LocalizationConfigInterface $config,
        private URLParserInterface $parser
    ) {}

    /**
     * @inheritDoc
     */
    public function getAlternateLinks(): string
    {
        $currentUrl = url()->full();
        $links = [];

        foreach ($this->config->getSupportedLocaleCodes() as $code) {
            $localizedUrl = $this->parser->getLocalizedUrl($currentUrl, $code);
            $links[] = "<link rel=\"alternate\" hreflang=\"{$code}\" href=\"{$localizedUrl}\" />";
        }

        // Add x-default pointing to the default locale
        $defaultLocale = $this->config->getDefaultLocale();
        $defaultUrl = $this->parser->getLocalizedUrl($currentUrl, $defaultLocale);
        
        // Place x-default at the end as per convention
        $links[] = "<link rel=\"alternate\" hreflang=\"x-default\" href=\"{$defaultUrl}\" />";

        return implode(PHP_EOL, $links);
    }
}
