<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationRedirectorInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\URLParserInterface;

final readonly class LocalizationRedirector implements LocalizationRedirectorInterface
{
    public function __construct(
        private LocalizationConfigInterface $config,
        private LocalizationStateInterface $state,
        private URLParserInterface $urlParser,
    ) {
    }

    public function shouldRedirect(Request $request): bool
    {
        // 1. Only redirect GET requests
        if (!$request->isMethod('GET')) {
            return false;
        }

        // 2. Is redirection globally active?
        if (!$this->config->isRedirectionEnabled()) {
            return false;
        }

        // 3. Never redirect AJAX/Livewire/JSON
        if ($request->ajax() || $request->hasHeader('X-Livewire')) {
            return false;
        }

        // 4. Check "except" paths
        if ($request->is(...$this->config->getRedirectionExcludedPaths())) {
            return false;
        }

        $detectedLocale = $this->state->get();

        $urlLocale = $this->urlParser->getLocaleFromRequest($request);

        return $urlLocale !== $detectedLocale;
    }

    public function getRedirectResponse(Request $request): RedirectResponse
    {
        $locale = $this->state->get();

        $localizedUrl = $this->urlParser->getLocalizedUrl(
            $request->fullUrl(),
            $locale
        );

        return new RedirectResponse($localizedUrl, 302, [
            'Vary' => 'Accept-Language',
        ]);
    }
}
