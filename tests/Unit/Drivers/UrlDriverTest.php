<?php

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\URLParserInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\UrlDriver;
use Josemontano1996\LaravelOctaneLocalization\Tests\TestCase;

it('returns the locale if the parser finds a supported locale in the URL', function (): void {
    // 1. Arrange
    $locale = TestCase::ALTERNATIVE_LOCALE;

    $parser = Mockery::mock(URLParserInterface::class);
    $parser->shouldReceive('getLocaleFromRequest')
        ->once()
        ->andReturn($locale);

    $config = Mockery::mock(LocalizationConfigInterface::class);
    $config->shouldReceive('isSupportedLocale')
        ->once()
        ->with($locale)
        ->andReturn(true);

    $driver = new UrlDriver($config, $parser);
    $request = Request::create("/{$locale}/dashboard");

    // 2. Act
    $result = $driver->getLocale($request);

    // 3. Assert
    expect($result)->toBe($locale);
});

it('returns null if the parser finds an unsupported locale', function (): void {
    // 1. Arrange
    $unsupported = TestCase::UNSUPPORTED_LOCALE;

    $parser = Mockery::mock(URLParserInterface::class);
    $parser->shouldReceive('getLocaleFromRequest')
        ->once()
        ->andReturn($unsupported);

    $config = Mockery::mock(LocalizationConfigInterface::class);
    $config->shouldReceive('isSupportedLocale')
        ->once()
        ->with($unsupported)
        ->andReturn(false);

    $driver = new UrlDriver($config, $parser);
    $request = Request::create("/{$unsupported}/dashboard");

    // 2. Act
    $result = $driver->getLocale($request);

    // 3. Assert
    expect($result)->toBeNull();
});

it('returns null if the parser finds nothing', function (): void {
    // 1. Arrange
    $parser = Mockery::mock(URLParserInterface::class);
    $parser->shouldReceive('getLocaleFromRequest')
        ->once()
        ->andReturn(null);

    // If the parser returns null, the driver shouldn't even ask the config
    $config = Mockery::mock(LocalizationConfigInterface::class);
    $config->shouldNotReceive('isSupportedLocale');

    $driver = new UrlDriver($config, $parser);
    $request = Request::create('/dashboard');

    // 2. Act
    $result = $driver->getLocale($request);

    // 3. Assert
    expect($result)->toBeNull();
});
