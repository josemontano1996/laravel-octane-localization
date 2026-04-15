<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Contracts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface LocalizationRedirectorInterface
{
    /**
     * Determine if the current request should be redirected to a localized URL.
     * * Logic should typically exclude:
     * - Non-GET requests.
     * - AJAX/Livewire requests.
     * - Paths defined in the 'except' configuration.
     * - Requests where the URL locale already matches the detected state.
     */
    public function shouldRedirect(Request $request): bool;

    /**
     * Generate a RedirectResponse to the localized version of the current URL.
     *
     * * @param Request $request The current request to extract the URL from.
     */
    public function getRedirectResponse(Request $request): RedirectResponse;
}
