<?php

namespace App\DTOs;

final readonly class DataHolder
{
    public const string DEFAULT_LOCALE = 'en';

    public const array SUPPORTED_LOCALES = ['en', 'es', 'fr', 'de'];

    public const string UNSUPPORTED_LOCALE = 'it';

    public const string PARAMETER_KEY = 'locale';
}
