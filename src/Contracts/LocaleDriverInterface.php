<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;
use Josemontano1996\LaravelOctaneLocalization\Drivers\Concerns\DoesNotPersistLocale;

use Illuminate\Http\Request;

interface LocaleDriverInterface
{
    /**
     * Look for the locale (e.g., check the Session or the Cookie).
     */
    public function getLocale(Request $request): ?string;

    /**
     * Store the locale for future requests.
     *
     * * @param string $locale The validated locale to save.
     * * @see DoesNotPersistLocale Use this trait for a no-op implementation.
     */
    public function storeLocale(string $locale): void;
}
