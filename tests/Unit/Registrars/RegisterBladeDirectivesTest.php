<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\Tests\Unit\Registrars;

use Illuminate\Support\Facades\Blade;
use Josemontano1996\LaravelOctaneLocalization\Registrars\RegisterBladeDirectives;

beforeEach(function () {
    RegisterBladeDirectives::register();
});

test('it registers @t translation shorthand directive', function () {
    $compiled = Blade::compileString("@t('auth.login')");
    expect($compiled)->toBe("<?php echo __('auth.login'); ?>");
});

test('it registers @currentLocale directive', function () {
    $compiled = Blade::compileString('@currentLocale');
    expect($compiled)->toBe('<?php echo app()->getLocale(); ?>');
});

test('it registers @isLocale directive using Blade::if', function () {
    $compiled = Blade::compileString("@isLocale('en') yes @elseisLocale('es') maybe @else no @endisLocale");
    
    // Blade::if generates internal code using the Blade::check helper
    expect($compiled)->toContain("\Illuminate\Support\Facades\Blade::check('isLocale', 'en')")
        ->and($compiled)->toContain("else: ?>")
        ->and($compiled)->toContain("endif; ?>");
});

test('it registers @supportedLocales directive', function () {
    $compiled = Blade::compileString('@supportedLocales($code, $data) {{ $code }} @endsupportedLocales');
    
    expect($compiled)->toContain('foreach(app(Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface::class)->getSupportedLocales() as $code => $data)');
});

test('it registers @localizedUrl directive', function () {
    $compiled = Blade::compileString("@localizedUrl('es')");
    expect($compiled)->toContain('app(Josemontano1996\LaravelOctaneLocalization\Contracts\URLParserInterface::class)->getLocalizedUrl(url()->current(), \'es\')');
});

test('it registers @alternateLinks directive', function () {
    $compiled = Blade::compileString('@alternateLinks');
    expect($compiled)->toContain('app(Josemontano1996\LaravelOctaneLocalization\Contracts\SeoHelperInterface::class)->getAlternateLinks()');
});
