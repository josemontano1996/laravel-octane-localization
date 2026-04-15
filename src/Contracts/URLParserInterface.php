<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;

use Illuminate\Http\Request;

interface UrlParserInterface
{
    /**
     * Extract the locale candidate from a Request object.
     * Usually by inspecting the URL segments.
     */
    public function getLocaleFromRequest(Request $request): ?string;

    /**
     * Extract the locale from a raw URL string.
     * Useful for parsing referer headers or redirected URLs.
     */
    public function getLocaleFromUrl(string $url): ?string;


    public function getLocalizedUrl(string $url, string $locale): string;

}
