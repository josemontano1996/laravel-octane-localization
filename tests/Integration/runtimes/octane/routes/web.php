<?php

use App\DTOS\BleedTestData;
use Illuminate\Support\Facades\Route;
use Josemontano1996\LaravelOctaneLocalization\Contracts\LocalizationConfigInterface;

Route::get('/unlocalized', function (LocalizationConfigInterface $config) {
    $expected = $config->getDefaultLocale(); // default locale passed by the test script
    $actual   = BleedTestData::capture($config);
    $expectedData = new BleedTestData($expected, $expected, $expected, $expected);
    $mismatches   = $actual->findMismatches($expectedData);

    return response()->json([
        'bleeded'    => $mismatches !== null,
        'mismatches' => $mismatches,
    ]);
});

Route::localizedWithPrefix(function () {
    Route::get('/localized', function (LocalizationConfigInterface $config) {
        $expected = request('expected');
        $actual = BleedTestData::capture($config);
        $expectedData = new BleedTestData($expected, $expected, $expected, $expected);
        $mismatches = $actual->findMismatches($expectedData);

        return response()->json([
            'bleeded'    => $mismatches !== null,
            'mismatches' => $mismatches,
        ]);
    });
});
