#!/usr/bin/env php
<?php

// Usage: php concurrent_bleedtest.php [-t <total>] [-c <concurrency>]
// Requires: ext-curl, ext-json

$locales = ['en', 'es', 'fr', 'cz', 'de'];

$options = getopt('t:c:', ['total:', 'concurrency:']);
$total_requests = (int) ($options['t'] ?? $options['total'] ?? 100);
$concurrency    = (int) ($options['c'] ?? $options['concurrency'] ?? 50);

$delay_ms = 100;          // Delay between firing each request (ms)
$sleep_ms = $delay_ms * 3; // Query-string hint so the route can sleep and force overlap

// Must match OCTANE_URL used in test.sh (default port 8000 inside the container,
// but the script runs on the HOST so use the forwarded port).
$base_url = getenv('OCTANE_URL') ?: 'http://localhost:8000';

// The default app locale — must match what config('app.locale') returns.
// Pass via env: DEFAULT_LOCALE=en php concurrent_bleedtest.php
$default_locale = getenv('DEFAULT_LOCALE') ?: 'en';

// Routes
$localized_endpoint   = $base_url . '/%s/localized';  // /{locale}/localized
$unlocalized_endpoint = $base_url . '/unlocalized';    // /unlocalized

// -------------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------------

function make_localized_handle(string $locale, int $sleep_ms, string $endpoint): \CurlHandle
{
    $url = sprintf($endpoint, $locale) . '?sleep=' . $sleep_ms . '&expected=' . urlencode($locale);
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    return $ch;
}

function make_unlocalized_handle(string $default_locale, int $sleep_ms, string $endpoint): \CurlHandle
{
    // Pass the default locale as expected so the server can call findMismatches()
    $url = $endpoint . '?sleep=' . $sleep_ms . '&expected=' . urlencode($default_locale);
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    return $ch;
}

// -------------------------------------------------------------------------
// Main loop
// -------------------------------------------------------------------------

echo "--------------------------------------------------\n";
echo "Starting bleed test...\n";
echo "Localized endpoint  : $localized_endpoint\n";
echo "Unlocalized endpoint: $unlocalized_endpoint\n";
echo "Default locale      : $default_locale\n";
echo "Delay / server sleep: {$delay_ms}ms / {$sleep_ms}ms\n";
echo "Total: $total_requests  |  Concurrency: $concurrency\n";
echo "--------------------------------------------------\n";

$multi     = curl_multi_init();
$results   = [];
$in_flight = [];

// Build interleaved request list.
// Every 3rd request is an unlocalized probe fired mid-flight to stress the
// default-locale isolation while localized requests are still running.
$pending = [];
for ($i = 0; $i < $total_requests; $i++) {
    if ($i % 3 === 2) {
        $pending[] = ['type' => 'unlocalized', 'locale' => $default_locale];
    } else {
        $pending[] = ['type' => 'localized', 'locale' => $locales[$i % count($locales)]];
    }
}

$start_time = microtime(true);

while (count($pending) > 0 || count($in_flight) > 0) {
    // Fill up to concurrency limit
    while (count($in_flight) < $concurrency && count($pending) > 0) {
        $item = array_shift($pending);

        if ($item['type'] === 'unlocalized') {
            $ch = make_unlocalized_handle($default_locale, $sleep_ms, $unlocalized_endpoint);
        } else {
            $ch = make_localized_handle($item['locale'], $sleep_ms, $localized_endpoint);
        }

        curl_multi_add_handle($multi, $ch);
        $in_flight[(int) $ch] = $item;

        if (count($pending) > 0) {
            usleep($delay_ms * 1000);
        }
    }

    // Drive the multi handle
    do {
        $status = curl_multi_exec($multi, $active);
    } while ($status === CURLM_CALL_MULTI_PERFORM);

    // Harvest completed handles
    while ($info = curl_multi_info_read($multi)) {
        $ch  = $info['handle'];
        $key = (int) $ch;

        $item      = $in_flight[$key];
        $raw       = curl_multi_getcontent($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err  = curl_error($ch);
        $data      = json_decode($raw, true);

        $results[] = $item + compact('data', 'raw', 'http_code', 'curl_err');

        curl_multi_remove_handle($multi, $ch);
        curl_close($ch);
        unset($in_flight[$key]);
    }

    if ($active) {
        curl_multi_select($multi, 0.05);
    }
}

curl_multi_close($multi);
$duration = round(microtime(true) - $start_time, 2);

// -------------------------------------------------------------------------
// Analysis
// -------------------------------------------------------------------------

$localized_bleeds   = 0;
$unlocalized_bleeds = 0;
$errors             = 0;

foreach ($results as $i => $result) {
    $type      = $result['type'];
    $locale    = $result['locale'];
    $data      = $result['data'];
    $http_code = $result['http_code'];
    $curl_err  = $result['curl_err'];
    $label     = $type === 'unlocalized' ? 'unlocalized' : $locale;

    // Both routes return: { bleeded: bool, mismatches: array|null }
    $is_valid = is_array($data) && array_key_exists('bleeded', $data);

    if (!$is_valid) {
        $snippet = substr($result['raw'], 0, 120);
        echo "[ERROR] Request #{$i} ({$label}): invalid response"
            . " (HTTP {$http_code})"
            . ($curl_err ? ", curl: {$curl_err}" : "")
            . " — {$snippet}\n";
        $errors++;
        continue;
    }

    if ($data['bleeded']) {
        $detail = implode(', ', array_map(
            fn($f, $v) => "{$f}: got '{$v['actual']}' expected '{$v['expected']}'",
            array_keys($data['mismatches']),
            $data['mismatches']
        ));
        $tag = $type === 'unlocalized' ? '[BLEED:UNLOCALIZED]' : '[BLEED:LOCALIZED]';
        echo "{$tag} Request #{$i} ({$label}): {$detail}\n";
        $type === 'unlocalized' ? $unlocalized_bleeds++ : $localized_bleeds++;
    }
}

$total = count($results);
echo "\n--------------------------------------------------\n";
echo "Summary: {$total} requests — "
    . "{$localized_bleeds} localized bleeds, "
    . "{$unlocalized_bleeds} unlocalized bleeds, "
    . "{$errors} errors.\n";
echo "Total time: {$duration}s\n";

if ($localized_bleeds === 0 && $unlocalized_bleeds === 0 && $errors === 0) {
    echo "SUCCESS: No state bleed detected.\n";
    echo "--------------------------------------------------\n";
    exit(0);
} else {
    echo "FAILURE: Bleed or connection issues detected!\n";
    echo "--------------------------------------------------\n";
    exit(1);
}
