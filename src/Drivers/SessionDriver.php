<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Drivers;

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;

final readonly class SessionDriver implements LocaleDriverInterface
{
    public function __construct(
        private LocalizationConfigInterface $config) {}

    public function getLocale(Request $request): ?string
    {
        if (! $request->hasSession()) {
            return null;
        }

        $sessionKey = $this->config->getParameterKey();
        $locale = $request->session()->get($sessionKey);

        return $this->config->isSupportedLocale($locale) ? $locale : null;
    }

    public function storeLocale(string $locale, Request $request): void
    {
        if ($request->hasSession()) {
            $request->session()->put($this->config->getParameterKey(), $locale);
        }
    }
}
