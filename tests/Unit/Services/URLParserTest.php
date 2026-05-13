<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Services\URLParser;

beforeEach(function (): void {
    $this->config = Mockery::mock(LocalizationConfigInterface::class);
    $this->parser = new URLParser($this->config);
});

test('it can get locale from request segments', function (): void {
    $request = Request::create('/en/some-path');

    expect($this->parser->getLocaleFromRequest($request))->toBe('en');
});

test('it can get locale from arbitrary URL', function (): void {
    expect($this->parser->getLocaleFromUrl('https://example.com/es/path'))->toBe('es');
    expect($this->parser->getLocaleFromUrl(''))->toBeNull();
});

test('it can localize a URL by replacing existing locale', function (): void {
    $this->config->shouldReceive('isSupportedLocale')->with('en')->andReturn(true);

    $url = 'https://example.com/en/path?query=1#frag';
    $localized = $this->parser->getLocalizedUrl($url, 'es');

    expect($localized)->toBe('https://example.com/es/path?query=1#frag');
});

test('it can localize a URL by prepending locale', function (): void {
    $this->config->shouldReceive('isSupportedLocale')->with('path')->andReturn(false);

    $url = 'https://example.com/path?query=1';
    $localized = $this->parser->getLocalizedUrl($url, 'fr');

    expect($localized)->toBe('https://example.com/fr/path?query=1');
});

test('it handles root URL localization', function (): void {
    $this->config->shouldReceive('isSupportedLocale')->with(null)->andReturn(false);

    $url = 'https://example.com/';
    $localized = $this->parser->getLocalizedUrl($url, 'en');

    expect($localized)->toBe('https://example.com/en');
});
