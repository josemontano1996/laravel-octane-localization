<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Tests\Unit\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\SeoHelperInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\URLParserInterface;

beforeEach(function () {
    // These are registered by the ServiceProvider in the base TestCase
    $this->config = app(LocalizationConfigInterface::class);
    $this->parser = app(URLParserInterface::class);
    $this->seoHelper = app(SeoHelperInterface::class);

    // Ensure state for the test
    Config::set('octane-localization.supported_locales', ['en', 'es', 'fr']);
    Config::set('app.locale', 'en');
});

test('it generates alternate links for all supported locales using real services', function () {
    // 1. Simulate a request to a specific path
    // Note: Request::create only creates the object; we must bind it to the app
    // to affect the url() and current request context.
    $request = Request::create('http://localhost/test-page');
    $this->app->instance('request', $request);

    // 2. Generate links
    $links = $this->seoHelper->getAlternateLinks();

    // 3. Verify real URL generation output
    expect($links)->toContain('<link rel="alternate" hreflang="en" href="http://localhost/en/test-page" />')
        ->and($links)->toContain('<link rel="alternate" hreflang="es" href="http://localhost/es/test-page" />')
        ->and($links)->toContain('<link rel="alternate" hreflang="fr" href="http://localhost/fr/test-page" />')
        ->and($links)->toContain('<link rel="alternate" hreflang="x-default" href="http://localhost/en/test-page" />');
});

test('it handles query parameters in alternate links', function () {
    $request = Request::create('http://localhost/search?q=laravel');
    $this->app->instance('request', $request);

    $links = $this->seoHelper->getAlternateLinks();

    expect($links)->toContain('<link rel="alternate" hreflang="en" href="http://localhost/en/search?q=laravel" />')
        ->and($links)->toContain('<link rel="alternate" hreflang="es" href="http://localhost/es/search?q=laravel" />');
});
