<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Drivers;

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\UrlParserInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\Concerns\DoesNotPersistLocale;

final readonly class RefererDriver implements LocaleDriverInterface
{
    use DoesNotPersistLocale;

    public function __construct(
        private UrlParserInterface $urlParser
    ) {}

    public function getLocale(Request $request): ?string
    {
        $referer = $request->header('referer');

        if (!$referer) {
            return null;
        }

        return $this->urlParser->getLocaleFromUrl($referer);
    }
}