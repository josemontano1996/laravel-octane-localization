<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\UrlParserInterface;

final readonly class URLParser implements UrlParserInterface
{
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
}
