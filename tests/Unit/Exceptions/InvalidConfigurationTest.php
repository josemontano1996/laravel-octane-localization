<?php

use Josemontano1996\LaravelOctaneLocalization\Exceptions\InvalidConfiguration;

it('creates a missing key exception', function () {
    // 1. Act
    $exception = InvalidConfiguration::missingKey('octane-localization.parameter_key');

    // 2. Assert
    expect($exception)->toBeInstanceOf(InvalidConfiguration::class)
        ->and($exception->getMessage())->toBe('The configuration key [octane-localization.parameter_key] is missing. This is required for the localization engine.');
});

it('creates a missing supported locales exception', function () {
    // 1. Act
    $exception = InvalidConfiguration::missingSupportedLocales('octane-localization.supported_locales');

    // 2. Assert
    expect($exception)->toBeInstanceOf(InvalidConfiguration::class)
        ->and($exception->getMessage())->toBe('You must define at least one locale in [octane-localization.supported_locales].');
});

it('creates an invalid type exception', function () {
    // 1. Act
    $exception = InvalidConfiguration::invalidType('octane-localization.supported_locales', 'array');

    // 2. Assert
    expect($exception)->toBeInstanceOf(InvalidConfiguration::class)
        ->and($exception->getMessage())->toBe('The configuration key [octane-localization.supported_locales] must be of type [array].');
});
