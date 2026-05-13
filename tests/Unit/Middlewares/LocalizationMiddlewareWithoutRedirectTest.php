<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationRedirectorInterface;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LocalizationMiddlewareWithoutRedirect;
use Josemontano1996\LaravelOctaneLocalization\Tests\TestCase;

it('detects locale, syncs, and adds headers without checking redirector', function () {
    // 1. Arrange
    $request = Request::create('/', 'GET');
    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $redirector = Mockery::mock(LocalizationRedirectorInterface::class);
    $locale = TestCase::ALTERNATIVE_LOCALE;

    $manager->shouldReceive('detect')->once()->with($request);
    $manager->shouldReceive('syncWithApplication')->once();

    // ASSERTION: This middleware should NEVER touch the redirector
    $redirector->shouldNotReceive('shouldRedirect');

    $middleware = new LocalizationMiddlewareWithoutRedirect($manager, $redirector);

    app()->setLocale($locale);

    // 2. Act
    $response = $middleware->handle($request, function ($req) {
        return new Response('Hello World');
    });

    // 3. Assert
    expect($response->getContent())->toBe('Hello World');
    expect($response->headers->get('Content-Language'))->toBe($locale);
});

it('appends Accept-Language to existing Vary headers', function () {
    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $redirector = Mockery::mock(LocalizationRedirectorInterface::class);

    $manager->shouldIgnoreMissing();
    $redirector->shouldNotReceive('shouldRedirect'); // Ensure it's ignored here too

    $middleware = new LocalizationMiddlewareWithoutRedirect($manager, $redirector);

    $response = $middleware->handle(new Request, function ($req) {
        $res = new Response;
        $res->headers->set('Vary', 'User-Agent');
        return $res;
    });

    expect($response->headers->get('Vary'))->toBe('User-Agent, Accept-Language');
});