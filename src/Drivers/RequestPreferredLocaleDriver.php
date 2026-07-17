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
    ) {
    }

    public function getLocale(Request $request): ?string
    {
        if (!$request->header('accept-language')) {
            return null;
        }

        $supported = $this->config->getSupportedLocaleCodes();
        $preferred = $request->getPreferredLanguage($supported);

        // Double check: if Symfony just fell back to index 0 because there was zero overlap
        if (!$this->hasLanguageOverlap($request->getLanguages(), $supported)) {
            return null;
        }

        return $preferred;
    }
    
    /**
     * Helper to verify if the browser's language values share any base language with supported locales.
     */
    private function hasLanguageOverlap(array $browserLanguages, array $supportedLocales): bool
    {
        foreach ($browserLanguages as $browserLang) {
            $baseBrowser = \strtok($browserLang, '_-');
            if ($baseBrowser === false) {
                continue;
            }

            foreach ($supportedLocales as $supported) {
                $baseSupported = \strtok($supported, '_-');
                if ($baseSupported === false) {
                    continue;
                }

                if (\strcasecmp($baseBrowser, $baseSupported) === 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
