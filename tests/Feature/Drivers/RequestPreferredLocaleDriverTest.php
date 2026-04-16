<?php

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Drivers\RequestPreferredLocaleDriver;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Tests\TestCase;

it('detects the preferred locale from the request headers using testcase constants', function () {
    // 1. Arrange
    $supported = TestCase::SUPPORTED_LOCALES;
    $preferred = TestCase::FALLBACK_LOCALE;   
    
    $config = Mockery::mock(LocalizationConfigInterface::class);
    $config->shouldReceive('getSupportedLocaleCodes')
        ->once()
        ->andReturn($supported);

    $driver = new RequestPreferredLocaleDriver($config);

    // Create a request with 'es' (FALLBACK_LOCALE) as the highest priority
    $request = Request::create('/', 'GET');
    $request->headers->set('Accept-Language', "{$preferred},{$preferred};q=0.9,en;q=0.8");

    // 2. Act
    $result = $driver->getLocale($request);

    // 3. Assert
    expect($result)->toBe($preferred);
});

it('returns null or first match when header is an unsupported locale', function () {
    // 1. Arrange
    $config = Mockery::mock(LocalizationConfigInterface::class);
    $config->shouldReceive('getSupportedLocaleCodes')
        ->once()
        ->andReturn(TestCase::SUPPORTED_LOCALES);

    $driver = new RequestPreferredLocaleDriver($config);

    // Use the UNSUPPORTED_LOCALE constant ('de')
    $request = Request::create('/', 'GET');
    $request->headers->set('Accept-Language', TestCase::UNSUPPORTED_LOCALE);

    // 2. Act
    $result = $driver->getLocale($request);

    // 3. Assert
    expect($result)->toBeNull();
});