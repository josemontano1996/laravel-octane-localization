<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Drivers;

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocaleDriverInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\Concerns\DoesNotPersistLocale;

final readonly class RequestPreferredLocaleDriver implements LocaleDriverInterface
{
    use DoesNotPersistLocale;

    public function __construct(
        private LocalizationConfigInterface $config
    ) {}

    public function getLocale(Request $request): ?string
    {
        $supported = $this->config->getSupportedLocaleCodes();

        $preferred = $request->getPreferredLanguage($supported);

        // Symfony returns the first element of $supported if no match is found.
        // We check if $preferred actually exists in the browser's languages.
        // getLanguages() returns all locales from the header sorted by priority.
        if (! \in_array($preferred, $request->getLanguages())) {
            return null;
        }

        return $preferred;
    }
}
