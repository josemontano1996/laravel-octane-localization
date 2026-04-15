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

        return $request->getPreferredLanguage($supported);
    }
}
