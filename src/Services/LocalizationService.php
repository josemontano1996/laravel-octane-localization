<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Services;

use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationContextInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationServiceInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateManagerInterface;
use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Override;

final readonly class LocalizationService implements LocalizationServiceInterface
{
    public function __construct(
        private LocalizationConfigInterface $config,
        private LocalizationStateManagerInterface $state,
        private LocalizationManagerInterface $manager,
    ) {
    }

    public function getLocale(): string
    {
        return $this->state->get();

    }

    public function setLocale(string $locale, ?Request $request = null): void
    {
        $this->manager->setLocale($locale);
        $this->manager->syncWithApplication();

        // If a request was explicitly provided, use it to persist.
        // If not, we skip persistence (safe for Jobs/CLI).
        if ($request !== null) {
            $this->manager->storeLocale($locale, $request);
        }
    }

    public function isSupported(string $locale): bool
    {
        return $this->config->isSupportedLocale($locale);
    }

    public function getSupportedLocales(): array
    {
        return $this->config->getSupportedLocales();
    }

    #[Override]
    public function getSupportedLocaleCodes(): array
    {
        return $this->config->getSupportedLocaleCodes();
    }

    public function getDefaultLocale(): string
    {
        return $this->config->getDefaultLocale();
    }

    public function resolve(Request $request): void
    {
        $this->manager->resolve($request);
    }

    public function reset(): void
    {
        $this->manager->reset();
    }
}
