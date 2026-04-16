<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Context;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationContext;

beforeEach(function () {
    $this->context = new LocalizationContext;
    Context::forget('localization.locale');
});

test('it can hydrate locale into context', function () {
    $this->context->hydrate('en');

    expect(Context::get('localization.locale'))->toBe('en');
});

test('it can get locale from context', function () {
    Context::add('localization.locale', 'es');

    expect($this->context->get())->toBe('es');
});

test('it returns null if locale is not in context', function () {
    expect($this->context->get())->toBeNull();
});

test('it can check if locale exists in context', function () {
    expect($this->context->has())->toBeFalse();

    Context::add('localization.locale', 'fr');

    expect($this->context->has())->toBeTrue();
});

test('it can forget locale from context', function () {
    Context::add('localization.locale', 'en');

    $this->context->forget();

    expect(Context::has('localization.locale'))->toBeFalse();
});
