<?php

date_default_timezone_set('UTC');

function cliArgValue(array $argv, string $name, string $default = ''): string
{
    $prefix = '--' . $name . '=';
    foreach ($argv as $arg) {
        if (strpos($arg, $prefix) === 0) return substr($arg, strlen($prefix));
    }
    return $default;
}

function cliArgIntValue(array $argv, string $name, int $default): int
{
    $value = cliArgValue($argv, $name, '');
    if ($value === '' || !is_numeric($value)) {
        return $default;
    }
    return (int)$value;
}

function loadJsonFile(string $path): array
{
    if (!is_file($path)) return [];
    $raw = @file_get_contents($path);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    return is_array($decoded) ? $decoded : [];
}

function saveJsonFile(string $path, array $data): bool
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($json) || $json === '') return false;
    return @file_put_contents($path, $json, LOCK_EX) !== false;
}

function normalizeTargets(array $targets): array
{
    $normalized = [];
    foreach ($targets as $target) {
        if (!is_array($target)) continue;
        $marketType = strtolower(trim((string)($target['market_type'] ?? '')));
        $symbol = strtoupper(trim((string)($target['symbol'] ?? '')));
        $baseUrl = trim((string)($target['base_url'] ?? ''));
        if (($marketType !== 'crypto' && $marketType !== 'stock') || $symbol === '' || $baseUrl === '') continue;
        $normalized[] = $target;
    }
    return array_values($normalized);
}

function rotateTargetsForBatch(array $targets, int $limit, string $cursorPath): array
{
    $targets = normalizeTargets($targets);
    $count = count($targets);
    if ($count <= 1 || $limit <= 0 || $count <= $limit) {
        return [
            'targets' => $targets,
            'offset' => 0,
            'next_offset' => 0,
            'total' => $count,
        ];
    }

    $cursor = loadJsonFile($cursorPath);
    $offset = is_numeric($cursor['offset'] ?? null) ? (int)$cursor['offset'] : 0;
    if ($offset < 0 || $offset >= $count) $offset = 0;

    $ordered = array_merge(array_slice($targets, $offset), array_slice($targets, 0, $offset));
    $selected = array_slice($ordered, 0, $limit);
    $nextOffset = ($offset + count($selected)) % $count;

    saveJsonFile($cursorPath, [
        'offset' => $nextOffset,
        'last_offset' => $offset,
        'batch_size' => $limit,
        'total_targets' => $count,
        'updated_at' => gmdate('Y-m-d\TH:i:s\Z'),
    ]);

    return [
        'targets' => $selected,
        'offset' => $offset,
        'next_offset' => $nextOffset,
        'total' => $count,
    ];
}

function environmentValue(string $name): string
{
    $environmentValue = getenv($name);
    if (is_string($environmentValue) && trim($environmentValue) !== '') {
        return trim($environmentValue);
    }

    $envPath = __DIR__ . '/.env';
    if (!is_file($envPath) || !is_readable($envPath)) return '';
    $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) return '';
    foreach ($lines as $line) {
        $line = trim((string)$line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!preg_match('/^' . preg_quote($name, '/') . '\s*=\s*(.*)$/', $line, $match)) continue;
        $value = trim((string)$match[1]);
        if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
            $value = substr($value, 1, -1);
        }
        return $value;
    }
    return '';
}

function httpFetch(string $url): array
{
    static $fetchCache = [];
    if (isset($fetchCache[$url])) {
        return $fetchCache[$url];
    }
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 12,
            'ignore_errors' => true,
            'header' => "User-Agent: CoinbasePortfolioCron/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    $headers = isset($http_response_header) && is_array($http_response_header) ? $http_response_header : [];
    $statusLine = $headers[0] ?? '';
    preg_match('/\s(\d{3})\s/', (string)$statusLine, $match);
    $status = isset($match[1]) ? (int)$match[1] : 0;
    $result = [
        'status' => $status,
        'ok' => $body !== false && $status >= 200 && $status < 400,
        'body' => is_string($body) ? $body : '',
        'headers' => $headers,
    ];
    $fetchCache[$url] = $result;
    return $result;
}

function responseContainsPriceJson(string $body): bool
{
    $decoded = json_decode($body, true);
    if (!is_array($decoded)) return false;
    $candidates = [
        $decoded['currentPrice'] ?? null,
        $decoded['current_price'] ?? null,
        $decoded['price'] ?? null,
        $decoded['spotPrice'] ?? null,
    ];
    foreach ($candidates as $candidate) {
        if (is_numeric($candidate) && (float)$candidate > 0) return true;
    }
    if (!empty($decoded['ok']) && (is_array($decoded['candles'] ?? null) || is_array($decoded['timeline'] ?? null))) {
        return true;
    }
    return false;
}

function responseContainsPriceCsv(string $body): bool
{
    $trimmed = trim($body);
    if ($trimmed === '') return false;
    $lines = preg_split('/\r\n|\r|\n/', $trimmed);
    if (!is_array($lines) || count($lines) < 2) return false;
    $header = strtolower(trim((string)$lines[0]));
    if (!str_contains($header, 'date') || (!str_contains($header, 'close') && !str_contains($header, 'adj close'))) {
        return false;
    }
    return substr_count((string)$lines[1], ',') >= 4;
}

function responseContainsPricePayload(array $response): bool
{
    $body = is_string($response['body'] ?? null) ? (string)$response['body'] : '';
    if ($body === '') return false;
    return responseContainsPriceJson($body) || responseContainsPriceCsv($body);
}

function microsoftConnectivityAvailable(): bool
{
    foreach ([
        'https://www.microsoft.com',
        'https://microsoft.com',
        'http://www.microsoft.com',
    ] as $url) {
        $response = httpFetch($url);
        if (!empty($response['ok'])) return true;
        $status = (int)($response['status'] ?? 0);
        if ($status >= 200 && $status < 500) return true;
    }
    return false;
}

function googleConnectivityAvailable(): bool
{
    foreach ([
        'https://www.google.com',
        'https://google.com',
        'http://www.google.com',
    ] as $url) {
        $response = httpFetch($url);
        if (!empty($response['ok'])) return true;
        $status = (int)($response['status'] ?? 0);
        if ($status >= 200 && $status < 500) return true;
    }
    return false;
}

function anyExternalConnectivityAvailable(): bool
{
    return googleConnectivityAvailable() || microsoftConnectivityAvailable();
}

function yahooSymbolProbeUrl(string $marketType, string $symbol): string
{
    $normalizedMarketType = strtolower(trim($marketType));
    $normalizedSymbol = strtoupper(trim($symbol));
    if ($normalizedMarketType === 'crypto' && !str_contains($normalizedSymbol, '-')) {
        $normalizedSymbol .= '-USD';
    }
    return 'https://query1.finance.yahoo.com/v8/finance/chart/' . rawurlencode($normalizedSymbol)
        . '?range=1d&interval=5m&includePrePost=false';
}

function yahooSymbolLooksReal(string $marketType, string $symbol): array
{
    $url = yahooSymbolProbeUrl($marketType, $symbol);
    $response = httpFetch($url);
    $decoded = json_decode((string)($response['body'] ?? ''), true);
    $looksReal = false;
    if (is_array($decoded)) {
        $result = $decoded['chart']['result'][0] ?? null;
        $meta = is_array($result) ? ($result['meta'] ?? null) : null;
        $timestamps = is_array($result) ? ($result['timestamp'] ?? null) : null;
        if (is_array($meta) && !empty($meta['symbol'])) {
            $looksReal = true;
        } elseif (is_array($timestamps) && count($timestamps) > 0) {
            $looksReal = true;
        }
    }
    return [
        'url' => $url,
        'ok' => !empty($response['ok']),
        'status' => (int)($response['status'] ?? 0),
        'looks_real' => $looksReal,
    ];
}

function alphaVantageStockLooksReal(string $symbol): array
{
    $apiKey = environmentValue('ALPHAVANTAGE_API_KEY');
    if ($apiKey === '') {
        return [
            'ok' => false,
            'status' => 0,
            'looks_real' => false,
            'inconclusive' => true,
            'reason' => 'ALPHAVANTAGE_API_KEY is not configured.',
        ];
    }

    $clean = strtoupper(trim($symbol));
    $clean = preg_replace('/[^A-Z0-9.\-^]/', '', $clean);
    if (!is_string($clean) || $clean === '') {
        return [
            'ok' => false,
            'status' => 0,
            'looks_real' => false,
            'inconclusive' => false,
            'reason' => 'Symbol was empty after normalization.',
        ];
    }

    $url = 'https://www.alphavantage.co/query?' . http_build_query([
        'function' => 'GLOBAL_QUOTE',
        'symbol' => $clean,
        'apikey' => $apiKey,
    ]);
    $response = httpFetch($url);
    $decoded = json_decode((string)($response['body'] ?? ''), true);
    if (!is_array($decoded)) {
        return [
            'url' => $url,
            'ok' => !empty($response['ok']),
            'status' => (int)($response['status'] ?? 0),
            'looks_real' => false,
            'inconclusive' => true,
            'reason' => 'Alpha Vantage did not return JSON.',
        ];
    }

    if (isset($decoded['Note']) || isset($decoded['Information']) || isset($decoded['Error Message'])) {
        return [
            'url' => $url,
            'ok' => !empty($response['ok']),
            'status' => (int)($response['status'] ?? 0),
            'looks_real' => false,
            'inconclusive' => true,
            'reason' => 'Alpha Vantage returned a note, information message, or error message.',
        ];
    }

    $quote = is_array($decoded['Global Quote'] ?? null) ? $decoded['Global Quote'] : [];
    $price = $quote['05. price'] ?? null;
    $looksReal = is_numeric($price) && (float)$price > 0.0;

    return [
        'url' => $url,
        'ok' => !empty($response['ok']),
        'status' => (int)($response['status'] ?? 0),
        'looks_real' => $looksReal,
        'inconclusive' => false,
        'reason' => $looksReal
            ? 'Alpha Vantage returned a usable Global Quote price.'
            : 'Alpha Vantage returned no usable Global Quote price for this symbol.',
    ];
}

function removeTargetFromRegistries(array $paths, string $marketType, string $symbol): void
{
    $normalizedMarketType = strtolower(trim($marketType));
    $normalizedSymbol = strtoupper(trim($symbol));
    foreach (array_values(array_unique($paths)) as $path) {
        if (!is_string($path) || trim($path) === '') continue;
        $registry = loadJsonFile($path);
        $targets = is_array($registry['targets'] ?? null) ? $registry['targets'] : [];
        $filtered = [];
        foreach ($targets as $target) {
            if (!is_array($target)) continue;
            $targetMarketType = strtolower(trim((string)($target['market_type'] ?? '')));
            $targetSymbol = strtoupper(trim((string)($target['symbol'] ?? '')));
            if ($targetMarketType === $normalizedMarketType && $targetSymbol === $normalizedSymbol) {
                continue;
            }
            $filtered[] = $target;
        }
        $registry['targets'] = array_values($filtered);
        saveJsonFile($path, $registry);
    }
}

$registryPath = cliArgValue($argv, 'registry', __DIR__ . '/wsl_portfolio_targets.json');
$singleMarketType = strtolower(trim(cliArgValue($argv, 'market-type', '')));
$singleSymbol = strtoupper(trim(cliArgValue($argv, 'symbol', '')));
$singleBaseUrl = trim(cliArgValue($argv, 'base-url', ''));
$defaultStatusPath = __DIR__ . '/wsl_portfolio_status.json';
if (($singleMarketType === 'crypto' || $singleMarketType === 'stock') && $singleSymbol !== '') {
    $safeMarketType = preg_replace('/[^a-z0-9_-]+/i', '-', $singleMarketType);
    $safeSymbol = preg_replace('/[^A-Z0-9._-]+/i', '-', $singleSymbol);
    $defaultStatusPath = __DIR__ . '/wsl_portfolio_status-' . $safeMarketType . '-' . $safeSymbol . '.json';
}
$statusPath = cliArgValue($argv, 'status', $defaultStatusPath);
$registry = loadJsonFile($registryPath);
$targets = is_array($registry['targets'] ?? null) ? $registry['targets'] : [];
$normalizedTargetCount = count(normalizeTargets($targets));
$batchLimit = cliArgIntValue($argv, 'batch-limit', $normalizedTargetCount > 0 ? $normalizedTargetCount : 0);
if ($batchLimit < 0) {
    $batchLimit = 0;
}

if (($singleMarketType === 'crypto' || $singleMarketType === 'stock') && $singleSymbol !== '') {
    $singleTarget = null;
    if ($singleBaseUrl !== '') {
        $singleTarget = [
            'market_type' => $singleMarketType,
            'symbol' => $singleSymbol,
            'base_url' => $singleBaseUrl,
        ];
    } else {
        foreach ($targets as $target) {
            if (!is_array($target)) continue;
            $targetMarketType = strtolower(trim((string)($target['market_type'] ?? '')));
            $targetSymbol = strtoupper(trim((string)($target['symbol'] ?? '')));
            if ($targetMarketType === $singleMarketType && $targetSymbol === $singleSymbol) {
                $singleTarget = $target;
                break;
            }
        }
    }
    $targets = $singleTarget !== null ? [$singleTarget] : [];
}

$batchMeta = [
    'offset' => 0,
    'next_offset' => 0,
    'total' => count($targets),
];
if (!(($singleMarketType === 'crypto' || $singleMarketType === 'stock') && $singleSymbol !== '')) {
    $cursorPath = __DIR__ . '/wsl_portfolio_batch_cursor.json';
    $batchMeta = rotateTargetsForBatch($targets, $batchLimit, $cursorPath);
    $targets = $batchMeta['targets'];
}

$status = [
    'ran_at' => gmdate('Y-m-d\TH:i:s\Z'),
    'target_count' => count($targets),
    'target_batch_limit' => $batchLimit,
    'target_batch_offset' => (int)$batchMeta['offset'],
    'target_batch_next_offset' => (int)$batchMeta['next_offset'],
    'target_total_available' => (int)$batchMeta['total'],
    'results' => [],
];

foreach ($targets as $target) {
    if (!is_array($target)) continue;
    $baseUrl = trim((string)($target['base_url'] ?? ''));
    $marketType = strtolower(trim((string)($target['market_type'] ?? '')));
    $symbol = strtoupper(trim((string)($target['symbol'] ?? '')));
    if ($baseUrl === '' || $marketType === '' || $symbol === '') continue;

    $query = http_build_query([
        'market_type' => $marketType,
        'symbol' => $symbol,
        'buy_multiplier' => is_numeric($target['buy_multiplier'] ?? null)
            ? number_format((float)$target['buy_multiplier'], 2, '.', '')
            : null,
        'sell_multiplier' => is_numeric($target['sell_multiplier'] ?? null)
            ? number_format((float)$target['sell_multiplier'], 2, '.', '')
            : null,
        'trust_percent' => is_numeric($target['trust_percent'] ?? null)
            ? number_format((float)$target['trust_percent'], 2, '.', '')
            : null,
        'break_buy' => is_numeric($target['break_buy'] ?? null)
            ? number_format((float)$target['break_buy'], 2, '.', '')
            : null,
        'break_gain' => is_numeric($target['break_gain'] ?? null)
            ? number_format((float)$target['break_gain'], 2, '.', '')
            : null,
        'break_loss' => is_numeric($target['break_loss'] ?? null)
            ? number_format((float)$target['break_loss'], 2, '.', '')
            : null,
        'run_analysis' => '1',
        'live' => '1',
    ]);
    $url = $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . $query;
    $response = httpFetch($url);
    $hasPricePayload = responseContainsPricePayload($response);
    $pageWasReachable = (int)($response['status'] ?? 0) > 0;
    $removedFromList = false;
    $removeReason = '';
    $symbolProbe = null;

    if (!$hasPricePayload) {
        if ($marketType === 'crypto') {
            $symbolProbe = yahooSymbolLooksReal($marketType, $symbol);
            if (!empty($symbolProbe['looks_real'])) {
                $removeReason = 'Kept symbol because direct crypto symbol probe still looked valid even though the page returned no usable price CSV/JSON.';
            } elseif (!$pageWasReachable) {
                $removeReason = 'Kept symbol because the local market page did not respond, so this looked like an app availability issue rather than proof that the symbol was invalid.';
            } elseif (anyExternalConnectivityAvailable()) {
                removeTargetFromRegistries([
                    $registryPath,
                    __DIR__ . '/wsl_portfolio_targets.json',
                    __DIR__ . '/cron_targets.json',
                ], $marketType, $symbol);
                $removedFromList = true;
                $removeReason = 'Removed after missing usable price CSV/JSON and failing the direct crypto symbol probe while Google or Microsoft connectivity probe succeeded.';
            } else {
                $removeReason = 'Kept symbol because both Google and Microsoft connectivity probes failed.';
            }
        } elseif ($marketType === 'stock') {
            $symbolProbe = yahooSymbolLooksReal($marketType, $symbol);
            if (!empty($symbolProbe['looks_real'])) {
                $removeReason = 'Kept symbol because Yahoo still returned a usable stock quote path even though the page returned no usable price CSV/JSON.';
            } elseif (!$pageWasReachable) {
                $removeReason = 'Kept symbol because the local market page did not respond, so this looked like an app availability issue rather than proof that the symbol was invalid.';
            } elseif (anyExternalConnectivityAvailable()) {
                removeTargetFromRegistries([
                    $registryPath,
                    __DIR__ . '/wsl_portfolio_targets.json',
                    __DIR__ . '/cron_targets.json',
                ], $marketType, $symbol);
                $removedFromList = true;
                $removeReason = 'Removed after missing usable price CSV/JSON and failing the Yahoo stock symbol probe while Google or Microsoft connectivity probe succeeded.';
            } else {
                $removeReason = 'Kept symbol because both Google and Microsoft connectivity probes failed.';
            }
        }
    }

    $status['results'][] = [
        'market_type' => $marketType,
        'symbol' => $symbol,
        'requested_url' => $url,
        'ok' => (bool)$response['ok'],
        'status' => (int)$response['status'],
        'has_price_payload' => $hasPricePayload,
        'symbol_probe' => $symbolProbe,
        'removed_from_list' => $removedFromList,
        'remove_reason' => $removeReason,
        'checked_at' => gmdate('Y-m-d\TH:i:s\Z'),
    ];
}

saveJsonFile($statusPath, $status);
echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
