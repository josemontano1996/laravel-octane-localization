<?php

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationManagerInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationRedirectorInterface;
use Josemontano1996\LaravelOctaneLocalization\Middlewares\LocalizationMiddleware;
use Josemontano1996\LaravelOctaneLocalization\Tests\TestCase;

it('detects locale, syncs, and adds headers to the response', function () {
    // 1. Arrange
    $request = Request::create('/', 'GET');
    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $redirector = Mockery::mock(LocalizationRedirectorInterface::class);
    $locale = TestCase::ALTERNATIVE_LOCALE;

    // Mock Detection and Sync
    $manager->shouldReceive('detect')->once()->with($request);
    $manager->shouldReceive('syncWithApplication')->once();

    // Mock Redirector to say "No redirection needed"
    $redirector->shouldReceive('shouldRedirect')->once()->andReturn(false);

    $middleware = new LocalizationMiddleware($manager, $redirector);

    // Set a specific locale in the app to check headers
    app()->setLocale($locale);

    // 2. Act
    $response = $middleware->handle($request, function ($req) {
        return new Response('Hello World');
    });

    // 3. Assert
    expect($response->getContent())->toBe('Hello World');
    expect($response->headers->get('Content-Language'))->toBe($locale);
    expect($response->headers->get('Vary'))->toContain('Accept-Language');
});
it('returns a redirect response if the redirector triggers', function () {
    // 1. Arrange
    $request = Request::create('/', 'GET');
    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $redirector = Mockery::mock(LocalizationRedirectorInterface::class);
    $locale = TestCase::ALTERNATIVE_LOCALE;

    $manager->shouldReceive('detect')->once();
    $manager->shouldReceive('syncWithApplication')->once();

    // Fix: Create a proper RedirectResponse instead of a generic Response
    $redirectResponse = new RedirectResponse("/{$locale}/home", 302);

    $redirector->shouldReceive('shouldRedirect')->once()->andReturn(true);
    $redirector->shouldReceive('getRedirectResponse')->once()->andReturn($redirectResponse);

    $middleware = new LocalizationMiddleware($manager, $redirector);

    // 2. Act
    $response = $middleware->handle($request, function ($req) {
        return new Response('Should not see this');
    });

    // 3. Assert
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getStatusCode())->toBe(302);
    expect($response->headers->get('Location'))->toBe("/{$locale}/home");
});

it('appends Accept-Language to existing Vary headers', function () {
    // 1. Arrange
    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $redirector = Mockery::mock(LocalizationRedirectorInterface::class);

    $manager->shouldIgnoreMissing();
    $redirector->shouldIgnoreMissing();

    $middleware = new LocalizationMiddleware($manager, $redirector);

    // 2. Act
    $response = $middleware->handle(new Request, function ($req) {
        $res = new Response;
        $res->headers->set('Vary', 'User-Agent');

        return $res;
    });

    // 3. Assert
    // Should be "User-Agent, Accept-Language"
    expect($response->headers->get('Vary'))->toBe('User-Agent, Accept-Language');
});

it('flushes manager state on terminate', function () {
    // 1. Arrange
    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $redirector = Mockery::mock(LocalizationRedirectorInterface::class);

    $manager->shouldReceive('flush')->once();

    $middleware = new LocalizationMiddleware($manager, $redirector);

    // 2. Act
    $middleware->terminate(new Request, new Response);
});

it('calls flush even if the request throws an exception', function () {
    // 1. Arrange
    $request = Request::create('/error-route', 'GET');
    $manager = Mockery::mock(LocalizationManagerInterface::class);
    $redirector = Mockery::mock(LocalizationRedirectorInterface::class);

    // Setup: Manager should detect, sync, and EVENTUALLY flush
    $manager->shouldReceive('detect')->once();
    $manager->shouldReceive('syncWithApplication')->once();
    $manager->shouldReceive('flush')->once(); // This is what we want to guarantee

    $redirector->shouldIgnoreMissing();

    $middleware = new LocalizationMiddleware($manager, $redirector);

    // 2. Act & Assert
    try {
        // We simulate a route/closure that explodes
        $middleware->handle($request, function () {
            throw new Exception('Something went wrong in the controller');
        });
    } catch (Exception $e) {
        // We catch the exception so the test doesn't stop here
        expect($e->getMessage())->toBe('Something went wrong in the controller');
    } finally {
        // 3. The "Octane Simulation"
        // In a real app, the Kernel calls terminate() in the finally block of the request cycle
        $middleware->terminate($request, new Response);
    }

    // Mockery will verify that reset() was called exactly once
});
