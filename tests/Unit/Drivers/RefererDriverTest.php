<?php

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\URLParserInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\RefererDriver;

it('returns null if the referer header is missing', function () {
    // 1. Arrange
    $parser = Mockery::mock(URLParserInterface::class);
    $driver = new RefererDriver($parser);
    $request = Request::create('/'); // No referer header by default

    // 2. Act
    $result = $driver->getLocale($request);

    // 3. Assert
    expect($result)->toBeNull();
});

it('detects the locale using the url parser when referer is present', function () {
    // 1. Arrange
    $refererUrl = 'https://example.com/es/dashboard';
    $expectedLocale = 'es';

    // Mock the parser behavior
    $parser = Mockery::mock(URLParserInterface::class);
    $parser->shouldReceive('getLocaleFromUrl')
        ->once()
        ->with($refererUrl)
        ->andReturn($expectedLocale);

    $driver = new RefererDriver($parser);

    // Create request with the header
    $request = Request::create('/', 'GET');
    $request->headers->set('referer', $refererUrl);

    // 2. Act
    $result = $driver->getLocale($request);

    // 3. Assert
    expect($result)->toBe($expectedLocale);
});
