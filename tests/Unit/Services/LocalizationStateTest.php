<?php

declare(strict_types=1);

use Josemontano1996\LaravelOctaneLocalization\Exceptions\InvalidLocale;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationState;

beforeEach(function (): void {
    $this->state = new LocalizationState;
});

test('it can set and get locale', function (): void {
    $this->state->set('en');
    expect($this->state->get())->toBe('en');
});

test('it trims locale when setting', function (): void {
    $this->state->set('  es  ');
    expect($this->state->get())->toBe('es');
});

test('it throws exception when setting empty locale', function (): void {
    expect(fn () => $this->state->set(''))
        ->toThrow(InvalidLocale::class);

    expect(fn () => $this->state->set('0'))
        ->toThrow(InvalidLocale::class);
});

test('it can check if locale exists', function (): void {
    expect($this->state->exists())->toBeFalse();

    $this->state->set('fr');
    expect($this->state->exists())->toBeTrue();
});

test('it can reset state', function (): void {
    $this->state->set('en');
    $this->state->reset();

    expect($this->state->get())->toBeNull();
    expect($this->state->exists())->toBeFalse();
});
