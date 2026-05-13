<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationStateInterface;
use Josemontano1996\LaravelOctaneLocalization\Contracts\URLParserInterface;
use Josemontano1996\LaravelOctaneLocalization\Services\LocalizationRedirector;

beforeEach(function (): void {
    $this->config = Mockery::mock(LocalizationConfigInterface::class);
    $this->state = Mockery::mock(LocalizationStateInterface::class);
    $this->urlParser = Mockery::mock(URLParserInterface::class);

    $this->redirector = new LocalizationRedirector(
        $this->config,
        $this->state,
        $this->urlParser
    );
});

test('it should not redirect non-GET requests', function (): void {
    $request = Request::create('/test', 'POST');
    expect($this->redirector->shouldRedirect($request))->toBeFalse();
});

test('it should not redirect if redirection is disabled', function (): void {
    $request = Request::create('/test', 'GET');
    $this->config->shouldReceive('isRedirectionEnabled')->andReturn(false);

    expect($this->redirector->shouldRedirect($request))->toBeFalse();
});

test('it should not redirect AJAX or Livewire requests', function (): void {
    $this->config->shouldReceive('isRedirectionEnabled')->andReturn(true);

    $ajaxRequest = Request::create('/test', 'GET');
    $ajaxRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
    expect($this->redirector->shouldRedirect($ajaxRequest))->toBeFalse();

    $livewireRequest = Request::create('/test', 'GET');
    $livewireRequest->headers->set('X-Livewire', 'true');
    expect($this->redirector->shouldRedirect($livewireRequest))->toBeFalse();
});

test('it should not redirect excluded paths', function (): void {
    $request = Request::create('/api/test', 'GET');
    $this->config->shouldReceive('isRedirectionEnabled')->andReturn(true);
    $this->config->shouldReceive('getRedirectionExcludedPaths')->andReturn(['api/*']);

    expect($this->redirector->shouldRedirect($request))->toBeFalse();
});

test('it should redirect if URL locale does not match detected locale', function (): void {
    $request = Request::create('/en/test', 'GET');
    $this->config->shouldReceive('isRedirectionEnabled')->andReturn(true);
    $this->config->shouldReceive('getRedirectionExcludedPaths')->andReturn([]);

    $this->urlParser->shouldReceive('getLocaleFromRequest')->andReturn('en');
    $this->state->shouldReceive('get')->andReturn('es');

    expect($this->redirector->shouldRedirect($request))->toBeTrue();
});

test('it should not redirect if URL locale matches detected locale', function (): void {
    $request = Request::create('/es/test', 'GET');
    $this->config->shouldReceive('isRedirectionEnabled')->andReturn(true);
    $this->config->shouldReceive('getRedirectionExcludedPaths')->andReturn([]);

    $this->urlParser->shouldReceive('getLocaleFromRequest')->andReturn('es');
    $this->state->shouldReceive('get')->andReturn('es');

    expect($this->redirector->shouldRedirect($request))->toBeFalse();
});

test('it can generate redirect response', function (): void {
    $request = Request::create('/test', 'GET');
    $this->state->shouldReceive('get')->andReturn('es');
    $this->urlParser->shouldReceive('getLocalizedUrl')
        ->with($request->fullUrl(), 'es')
        ->andReturn('http://localhost/es/test');

    $response = $this->redirector->getRedirectResponse($request);

    expect($response->getTargetUrl())->toBe('http://localhost/es/test');
    expect($response->getStatusCode())->toBe(302);
    expect($response->headers->get('Vary'))->toBe('Accept-Language');
});
