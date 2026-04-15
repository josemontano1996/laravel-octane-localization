<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;

final readonly class CookieDriver implements LocaleDriverInterface
{
    public function __construct(
        private LocalizationConfigInterface $config
    ) {}

    /**
     * Detect the locale from the incoming cookies.
     */
    public function getLocale(Request $request): ?string
    {
        $cookieKey = $this->config->getParameterKey();
        $locale = $request->cookie($cookieKey);

        return $this->config->isSupportedLocale($locale) ? (string) $locale : null;
    }

    /**
     * Persist the locale by queuing a cookie for the response.
     */
    public function storeLocale(string $locale, Request $request): void
    {
        $cookieKey = $this->config->getParameterKey();

        // We use the Cookie facade's 'queue' method.
        // Laravel handles attaching queued cookies to the outgoing response automatically.
        Cookie::queue(
            $cookieKey,
            $locale,
            $this->config->getCookieExpiration()
        );
    }
}
