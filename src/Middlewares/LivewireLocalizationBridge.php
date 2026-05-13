<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Enums\SupportedExtensions;
use Symfony\Component\HttpFoundation\Response;

final readonly class LivewireLocalizationBridge
{
    public function __construct(
        private LocalizationManagerInterface $manager,
        private LocalizationConfigInterface $config
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isLivewire($request)) {
            return $next($request);
        }

        $this->manager->discover($request, $this->config->getExtensionDrivers(SupportedExtensions::LIVEWIRE->value));

        $this->manager->syncWithApplication();

        return $next($request);
    }

    /**
     * Determine if the request is coming from Livewire.
     */
    private function isLivewire(Request $request): bool
    {
        return $request->hasHeader('X-Livewire');
    }
}
