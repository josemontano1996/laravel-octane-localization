<?php

declare(strict_types=1);

namespace Josemontano1996\LaravelOctaneLocalization\DTOs;

final readonly class SupportedLocale
{
    public function __construct(
        public string $code,
        public string $name,
        public array $extra = [],
    ) {}

    public static function fromArray(string $code, array $data): self
    {
        return new self(
            code: $code,
            name: $data['name'] ?? $code,
            extra: array_diff_key($data, ['name' => ''])
        );
    }
}
