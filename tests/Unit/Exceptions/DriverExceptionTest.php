<?php

declare(strict_types=1);

use Josemontano1996\LaravelOctaneLocalization\Exceptions\DriverException;

it('creates an invalid interface exception with the correct message', function (): void {
    // 1. Arrange
    $driver = 'InvalidDriver';
    $interface = 'LocaleDriverInterface';

    // 2. Act
    $exception = DriverException::invalidInterface($driver, $interface);

    // 3. Assert
    expect($exception)->toBeInstanceOf(DriverException::class)
        ->and($exception->getMessage())->toBe('The driver [InvalidDriver] must implement [LocaleDriverInterface]');
});

it('creates a not found exception with the correct message', function (): void {
    // 1. Arrange
    $driver = 'MissingDriverClass';

    // 2. Act
    $exception = DriverException::notFound($driver);

    // 3. Assert
    expect($exception)->toBeInstanceOf(DriverException::class)
        ->and($exception->getMessage())->toBe('The localization driver class [MissingDriverClass] does not exist.');
});
