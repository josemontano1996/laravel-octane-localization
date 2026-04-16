<?php

use Josemontano1996\LaravelOctaneLocalization\DTOs\SupportedLocale;

it('can be created from an array', function () {
    $code = 'es';
    $data = [
        'name' => 'Spanish',
        'native' => 'Español',
        'region' => 'Spain'
    ];

    $locale = SupportedLocale::fromArray($code, $data);

    expect($locale->code)->toBe('es')
        ->and($locale->name)->toBe('Spanish')
        ->and($locale->extra)->toBe(['native' => 'Español', 'region' => 'Spain']);
});

it('uses the code as the name if the name is missing', function () {
    $locale = SupportedLocale::fromArray('fr', ['extra_stuff' => 'foo']);

    expect($locale->name)->toBe('fr');
});