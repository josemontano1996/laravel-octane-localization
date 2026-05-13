<?php

declare(strict_types=1);

use Josemontano1996\LaravelOctaneLocalization\Exceptions\InvalidLocale;

it('creates an exception for empty locales', function (): void {
    // 1. Act
    $exception = InvalidLocale::becauseItIsEmpty();

    // 2. Assert
    expect($exception)->toBeInstanceOf(InvalidLocale::class)
        ->and($exception->getMessage())->toBe('The locale cannot be empty.');
});

it('creates an exception for unsupported locales', function (): void {
    // 1. Arrange
    $unsupported = 'zh';

    // 2. Act
    $exception = InvalidLocale::unsupported($unsupported);

    // 3. Assert
    expect($exception)->toBeInstanceOf(InvalidLocale::class)
        ->and($exception->getMessage())->toBe('The locale [zh] is not in your supported list.');
});
