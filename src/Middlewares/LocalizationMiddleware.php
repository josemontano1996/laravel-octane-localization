<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationRedirectorInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class LocalizationMiddleware
{
    public function __construct(
        private LocalizationManagerInterface $manager,
        private LocalizationRedirectorInterface $redirector
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->manager->detect($request);
        $this->manager->syncWithApplication();

        if ($this->redirector->shouldRedirect($request)) {
            return $this->redirector->getRedirectResponse($request);
        }

        $response = $next($request);

        $this->addLocalizationHeaders($response);

        return $response;
    }

    private function addLocalizationHeaders(Response $response): void
    {
        $locale = app()->getLocale();

        // Set Content-Language
        $response->headers->set('Content-Language', $locale);

        // Ensure proper Cache Fragmentation via the Vary header
        $vary = $response->headers->get('Vary', '');
        $varyValues = $vary ? array_map('trim', explode(',', $vary)) : [];

        if (! \in_array('Accept-Language', $varyValues, true)) {
            $varyValues[] = 'Accept-Language';
            $response->headers->set('Vary', implode(', ', $varyValues));
        }
    }
}
