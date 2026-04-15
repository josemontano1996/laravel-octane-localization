<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\UrlParserInterface;

final readonly class URLParser implements UrlParserInterface
{
    public function __construct(private LocalizationConfigInterface $config) {}

    public function getLocaleFromRequest(Request $request): ?string
    {
        return $request->segment(1);
    }

    public function getLocaleFromUrl(string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Create a temporary request object to use Laravel's segment parsing
        $fakeRequest = Request::create($url);

        return $this->getLocaleFromRequest($fakeRequest);
    }

    public function getLocalizedUrl(string $url, string $locale): string
    {
        // 1. Parse the URL into components
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';

        // Split path into segments, filtering out empty values (e.g., leading slash)
        $segments = array_values(array_filter(explode('/', $path)));
        $firstSegment = $segments[0] ?? null;

        // 2. Identify if we should replace or prepend
        // Use the config to check if the first segment is already a known locale
        if ($this->config->isSupportedLocale($firstSegment)) {
            $segments[0] = $locale;
        } else {
            array_unshift($segments, $locale);
        }

        // 3. Rebuild the Path
        $newPath = '/'.implode('/', $segments);

        // 4. Rebuild the Full URL
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'].'://' : '';
        $host = $parsedUrl['host'] ?? '';
        $port = isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '';
        $query = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#'.$parsedUrl['fragment'] : '';

        return $scheme.$host.$port.$newPath.$query.$fragment;
    }
}
