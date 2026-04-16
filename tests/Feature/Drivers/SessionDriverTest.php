<?php

use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Drivers\SessionDriver;
use Josemontano1996\LaravelOctaneLocalization\Tests\TestCase;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

it('retrieves the locale from the session', function () {
    // 1. Arrange
    $config = app(LocalizationConfigInterface::class);
    $driver = new SessionDriver($config);

    $session = new Store('test_session', new NullSessionHandler);
    $session->put(TestCase::PARAMETER_KEY, TestCase::ALTERNATIVE_LOCALE);
    $session->start();

    $request = Request::create('/', 'GET');
    $request->setLaravelSession($session);

    // 2. Act
    $result = $driver->getLocale($request);

    // 3. Assert
    expect($result)->toBe(TestCase::ALTERNATIVE_LOCALE);
});

it('stores the locale in the session', function () {
    // 1. Arrange
    $config = app(LocalizationConfigInterface::class);
    $driver = new SessionDriver($config);

    $session = new Store('test_session', new NullSessionHandler);
    $session->start();

    $request = Request::create('/', 'GET');
    $request->setLaravelSession($session);

    // 2. Act
    $driver->storeLocale(TestCase::ALTERNATIVE_LOCALE, $request);

    // 3. Assert
    expect($session->get(TestCase::PARAMETER_KEY))->toBe(TestCase::ALTERNATIVE_LOCALE);
});
