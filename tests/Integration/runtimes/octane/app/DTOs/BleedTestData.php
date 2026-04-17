<?php

namespace App\DTOs;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;

final class BleedTestData
{
    public function __construct(
        public readonly string $app,
        public readonly mixed $urlDefault,
        public readonly string $carbon,
        public readonly string $number
    ) {}

    /**
     * Captures the current locale state across the framework.
     *
     * * @param string $parameterKey The key used in URL::defaults (e.g., 'locale' or 'lang')
     */
    public static function capture(LocalizationConfigInterface $config): self
    {
        return new self(
            app: App::getLocale(),
            urlDefault: URL::getDefaultParameters()[$config->getParameterKey()] ?? null,
            carbon: Carbon::getLocale(),
            number: Number::defaultLocale(),
        );
    }

    public function findMismatches(BleedTestData $expected): ?array
    {
        $mismatches = [];
        $fields = ['app', 'urlDefault', 'carbon', 'number'];

        foreach ($fields as $field) {
            if ($this->{$field} !== $expected->{$field}) {
                $mismatches[$field] = [
                    'actual' => $this->{$field},
                    'expected' => $expected->{$field},
                ];
            }
        }

        return empty($mismatches) ? null : $mismatches;
    }
}
