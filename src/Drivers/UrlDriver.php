<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Drivers;

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\UrlParserInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\Concerns\DoesNotPersistLocale;

final readonly class UrlDriver implements LocaleDriverInterface
{
    use DoesNotPersistLocale;

    public function __construct(
        private LocalizationConfigInterface $config,
        private UrlParserInterface $urlParser
    ) {}

    public function getLocale(Request $request): ?string
    {
        $segment = $this->urlParser->getLocaleFromRequest($request);

        if (filled($segment) && $this->config->isSupportedLocale($segment)) {
            return $segment;
        }

        return null;
    }
}
