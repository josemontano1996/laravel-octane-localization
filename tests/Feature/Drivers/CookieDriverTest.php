<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\CookieDriver;
use Josemontano1996\LaravelOctaneLocalization\Tests\TestCase;

it('detects locale from cookie', function () {
    $config = app(LocalizationConfigInterface::class);
    $locale = TestCase::DEFAULT_LOCALE;

    $driver = new CookieDriver($config);

    $request = Request::create('/', 'GET', [], [$config->getParameterKey() => $locale]);

    $result = $driver->getLocale($request);

    expect($result)->toBe($locale);
});

it('queues a cookie with the correct locale and expiration', function () {
    $config = app(LocalizationConfigInterface::class);
    $driver = new CookieDriver($config);
    $request = Request::create('/');

    $driver->storeLocale(TestCase::ALTERNATIVE_LOCALE, $request);

    // Use collect() to find the cookie fluently
    $cookie = collect(Cookie::getQueuedCookies())
        ->first(fn ($c) => $c->getName() === $config->getParameterKey());

    expect($cookie)->not->toBeNull();
    expect($cookie->getValue())->toBe(TestCase::ALTERNATIVE_LOCALE);
    expect($cookie->getExpiresTime())->toBeGreaterThan(time());
});
