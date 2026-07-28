<?php

date_default_timezone_set('UTC');

function pdLoadJson(string $path): array
{
    if (!is_file($path)) return [];
    $raw = @file_get_contents($path);
    $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
    return is_array($decoded) ? $decoded : [];
}

function pdSaveJson(string $path, array $data): bool
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($json) || $json === '') return false;
    return @file_put_contents($path, $json, LOCK_EX) !== false;
}

function pdEnvironmentValue(string $name): string
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

function pdDetectBaseUrl(): string
{
    $host = trim((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost:8081'));
    $https = strtolower((string)($_SERVER['HTTPS'] ?? ''));
    $scheme = ($https !== '' && $https !== 'off' && $https !== '0') ? 'https' : 'http';
    return $scheme . '://' . $host . '/index.php';
}

function pdNormalizeSymbol(string $marketType, string $symbol): string
{
    $clean = strtoupper(trim($symbol));
    $clean = preg_replace('/[^A-Z0-9.\-^]/', '', $clean);
    if (!is_string($clean)) $clean = '';
    if ($marketType === 'crypto' && $clean !== '' && !str_contains($clean, '-')) {
        $clean .= '-USD';
    }
    return $clean;
}

function pdSymbolPresetSettings(string $marketType, string $symbol): array
{
    $normalizedMarket = strtolower(trim($marketType));
    $normalizedSymbol = strtoupper(trim($symbol));
    if ($normalizedMarket === 'crypto' && $normalizedSymbol === 'BTC-USD') {
        return [
            'buy_multiplier' => 0.90,
            'sell_multiplier' => 0.80,
            'trust_percent' => 90.0,
        ];
    }
    return [
        'buy_multiplier' => 1.10,
        'sell_multiplier' => 1.00,
        'trust_percent' => 75.0,
    ];
}

function pdInstallWindowsScheduler(string $cronPhpPath, string $registryPath, string $statusPath): array
{
    if (!function_exists('exec')) {
        return ['ok' => false, 'message' => 'PHP exec() is disabled for this page.'];
    }

    $phpBinary = 'C:\\Users\\g0d77\\AppData\\Local\\Microsoft\\WinGet\\Packages\\PHP.PHP.8.5_Microsoft.Winget.Source_8wekyb3d8bbwe\\php.exe';
    if (!is_file($phpBinary)) {
        $phpBinary = 'php';
    }

    $runnerPath = __DIR__ . '/windows_portfolio_scheduler.ps1';
    $taskName = 'CoinbasePortfolio5Minute';
    $taskCommand = 'powershell.exe -WindowStyle Hidden -NoProfile -ExecutionPolicy Bypass -File '
        . escapeshellarg($runnerPath);
    $previewCommand = '"' . $phpBinary . '" "' . $cronPhpPath . '" --registry="' . $registryPath . '" --status="' . $statusPath . '"';
    $scheduleStart = gmdate('H:i', time() + 120);

    $command = 'schtasks /Create /F /SC MINUTE /MO 5 /TN '
        . escapeshellarg($taskName)
        . ' /TR '
        . escapeshellarg($taskCommand)
        . ' /ST '
        . escapeshellarg($scheduleStart);

    $output = [];
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);
    if ($exitCode === 0) {
        exec('schtasks /Query /TN ' . escapeshellarg($taskName) . ' /FO LIST /V 2>&1', $output, $queryExitCode);
        if ($queryExitCode !== 0) {
            $exitCode = $queryExitCode;
        }
    }

    return [
        'ok' => $exitCode === 0,
        'message' => trim(implode(PHP_EOL, $output)),
        'cron_line' => $previewCommand,
        'exit_code' => $exitCode,
        'task_name' => $taskName,
    ];
}

function pdLatestTickerPrice(string $symbol): ?float
{
    $summary = pdLoadJson(__DIR__ . '/tickers/' . $symbol . '-cron-summary.json');
    if (is_numeric($summary['currentPrice'] ?? null)) return (float)$summary['currentPrice'];

    $csvPath = __DIR__ . '/tickers/' . $symbol . '.csv';
    if (!is_file($csvPath)) return null;
    $lines = @file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines) || count($lines) < 2) return null;
    $last = str_getcsv((string)$lines[count($lines) - 1]);
    return isset($last[4]) && is_numeric($last[4]) ? (float)$last[4] : null;
}

function pdCoinGeckoIdForTicker(string $ticker): ?string
{
    $base = strtoupper(trim(strtok($ticker, '-')));
    $map = [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'DOGE' => 'dogecoin',
        'SOL' => 'solana',
        'XRP' => 'ripple',
        'ADA' => 'cardano',
        'BNB' => 'binancecoin',
        'LTC' => 'litecoin',
        'BCH' => 'bitcoin-cash',
        'TRX' => 'tron',
        'DOT' => 'polkadot',
        'LINK' => 'chainlink',
        'AVAX' => 'avalanche-2',
        'SHIB' => 'shiba-inu',
        'UNI' => 'uniswap',
        'PEPE' => 'pepe',
    ];
    return $map[$base] ?? null;
}

function pdFetchCoinGeckoUsdPrices(array $symbols): array
{
    $idToSymbol = [];
    foreach ($symbols as $symbol) {
        if (!is_string($symbol) || trim($symbol) === '') continue;
        $coinId = pdCoinGeckoIdForTicker($symbol);
        if ($coinId === null) continue;
        $idToSymbol[$coinId] = strtoupper(trim($symbol));
    }
    if (!$idToSymbol) return [];

    $params = http_build_query([
        'ids' => implode(',', array_keys($idToSymbol)),
        'vs_currencies' => 'usd',
        'include_last_updated_at' => 'true',
        'precision' => 'full',
    ]);
    $url = 'https://api.coingecko.com/api/v3/simple/price?' . $params;
    $headers = [
        'Accept: application/json',
        'Cache-Control: no-cache, no-store, must-revalidate',
        'Pragma: no-cache',
        'User-Agent: Mozilla/5.0 (compatible; CoinbasePortfolioDashboard/1.0)',
    ];

    $demoKey = trim((string)(getenv('COINGECKO_DEMO_API_KEY') ?: ''));
    $proKey = trim((string)(getenv('COINGECKO_PRO_API_KEY') ?: ''));
    if ($proKey !== '') {
        $headers[] = 'x-cg-pro-api-key: ' . $proKey;
    } elseif ($demoKey !== '') {
        $headers[] = 'x-cg-demo-api-key: ' . $demoKey;
    }

    $body = false;
    $httpCode = 0;
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
        ]);
        $body = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 12,
                'ignore_errors' => true,
                'header' => implode("\r\n", $headers),
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
            $httpCode = (int)$match[1];
        }
    }

    if (!is_string($body) || $body === '' || ($httpCode !== 0 && ($httpCode < 200 || $httpCode >= 300))) {
        return [];
    }

    $json = json_decode($body, true);
    if (!is_array($json)) return [];

    $prices = [];
    foreach ($json as $coinId => $record) {
        if (!isset($idToSymbol[$coinId]) || !is_array($record)) continue;
        $price = $record['usd'] ?? null;
        if (!is_numeric($price)) continue;
        $prices[$idToSymbol[$coinId]] = (float)$price;
    }
    return $prices;
}

function pdFetchYahooLatestPrices(array $symbols): array
{
    $normalized = [];
    foreach ($symbols as $symbol) {
        if (!is_string($symbol) || trim($symbol) === '') continue;
        $clean = strtoupper(trim($symbol));
        if ($clean === '') continue;
        $normalized[$clean] = $clean;
    }
    if (!$normalized) return [];

    $prices = [];
    foreach (array_chunk(array_values($normalized), 25) as $chunk) {
        $params = http_build_query([
            'symbols' => implode(',', $chunk),
            'range' => '1d',
            'interval' => '5m',
            'indicators' => 'close',
            'includeTimestamps' => 'true',
            'includePrePost' => 'false',
        ]);
        $response = pdHttpFetch('https://query1.finance.yahoo.com/v7/finance/spark?' . $params, 12);
        $json = json_decode((string)($response['body'] ?? ''), true);
        $results = is_array($json['spark']['result'] ?? null) ? $json['spark']['result'] : [];
        foreach ($results as $result) {
            if (!is_array($result)) continue;
            $symbol = strtoupper(trim((string)($result['symbol'] ?? '')));
            $payload = is_array($result['response'][0] ?? null) ? $result['response'][0] : [];
            $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
            $closeSeries = is_array($payload['indicators']['quote'][0]['close'] ?? null)
                ? $payload['indicators']['quote'][0]['close']
                : [];
            $price = $meta['regularMarketPrice'] ?? null;
            if (!is_numeric($price)) {
                for ($index = count($closeSeries) - 1; $index >= 0; $index--) {
                    if (is_numeric($closeSeries[$index] ?? null)) {
                        $price = (float)$closeSeries[$index];
                        break;
                    }
                }
            }
            if ($symbol === '' || !is_numeric($price)) continue;
            $prices[$symbol] = (float)$price;
        }
    }

    return $prices;
}

function pdYahooSymbolLooksReal(string $symbol): ?bool
{
    $params = http_build_query([
        'symbols' => strtoupper(trim($symbol)),
        'range' => '1d',
        'interval' => '5m',
        'indicators' => 'close',
        'includeTimestamps' => 'true',
        'includePrePost' => 'false',
    ]);
    $response = pdHttpFetch('https://query1.finance.yahoo.com/v7/finance/spark?' . $params, 12);
    $body = (string)($response['body'] ?? '');
    if ($body === '') return null;
    $json = json_decode($body, true);
    if (!is_array($json)) return null;
    $results = is_array($json['spark']['result'] ?? null) ? $json['spark']['result'] : [];
    foreach ($results as $result) {
        if (!is_array($result)) continue;
        $payload = is_array($result['response'][0] ?? null) ? $result['response'][0] : [];
        $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
        $timestamps = is_array($payload['timestamp'] ?? null) ? $payload['timestamp'] : [];
        if (!empty($meta['symbol']) || count($timestamps) > 0) {
            return true;
        }
    }
    return false;
}

function pdFetchAlphaVantageBulkStockPrices(array $symbols): array
{
    $apiKey = pdEnvironmentValue('ALPHAVANTAGE_API_KEY');
    if ($apiKey === '') return [];

    $normalized = [];
    foreach ($symbols as $symbol) {
        if (!is_string($symbol) || trim($symbol) === '') continue;
        $clean = strtoupper(trim($symbol));
        $clean = preg_replace('/[^A-Z0-9.\-^]/', '', $clean);
        if (!is_string($clean) || $clean === '') continue;
        $normalized[$clean] = $clean;
    }
    if (!$normalized) return [];

    $quotes = [];
    foreach (array_chunk(array_values($normalized), 100) as $chunk) {
        $params = http_build_query([
            'function' => 'REALTIME_BULK_QUOTES',
            'symbol' => implode(',', $chunk),
            'datatype' => 'csv',
            'apikey' => $apiKey,
        ]);
        $url = 'https://www.alphavantage.co/query?' . $params;
        $headers = [
            'Accept: text/csv,application/json;q=0.9,*/*;q=0.8',
            'Cache-Control: no-cache, no-store, must-revalidate',
            'Pragma: no-cache',
            'User-Agent: Mozilla/5.0 (compatible; CoinbasePortfolioDashboard/1.0)',
        ];

        $body = false;
        $httpCode = 0;
        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_ENCODING => '',
            ]);
            $body = curl_exec($curl);
            $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 12,
                    'ignore_errors' => true,
                    'header' => implode("\r\n", $headers),
                ],
            ]);
            $body = @file_get_contents($url, false, $context);
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
                $httpCode = (int)$match[1];
            }
        }

        if (!is_string($body) || trim($body) === '' || ($httpCode !== 0 && ($httpCode < 200 || $httpCode >= 300))) {
            continue;
        }

        $trimmed = ltrim($body);
        if ($trimmed !== '' && $trimmed[0] === '{') {
            $json = json_decode($body, true);
            $records = is_array($json['data'] ?? null) ? $json['data'] : [];
            foreach ($records as $record) {
                if (!is_array($record)) continue;
                $symbol = strtoupper(trim((string)($record['symbol'] ?? '')));
                $price = $record['price'] ?? ($record['close'] ?? null);
                if ($symbol === '' || !is_numeric($price)) continue;
                $quotes[$symbol] = (float)$price;
            }
            continue;
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($body));
        if (!is_array($lines) || count($lines) < 2) continue;
        $headersRow = str_getcsv((string)array_shift($lines));
        $headerIndex = [];
        foreach ($headersRow as $index => $header) {
            $normalizedHeader = strtolower(trim((string)$header));
            $normalizedHeader = preg_replace('/[^a-z0-9]+/', '_', $normalizedHeader);
            if (!is_string($normalizedHeader) || $normalizedHeader === '') continue;
            $headerIndex[$normalizedHeader] = $index;
        }
        $symbolIndex = $headerIndex['symbol'] ?? null;
        $priceIndex = $headerIndex['price'] ?? ($headerIndex['last_price'] ?? ($headerIndex['close'] ?? null));
        if (!is_int($symbolIndex) || !is_int($priceIndex)) continue;

        foreach ($lines as $line) {
            if (trim((string)$line) === '') continue;
            $row = str_getcsv((string)$line);
            $symbol = strtoupper(trim((string)($row[$symbolIndex] ?? '')));
            $price = $row[$priceIndex] ?? null;
            if ($symbol === '' || !is_numeric($price)) continue;
            $quotes[$symbol] = (float)$price;
        }
    }

    return $quotes;
}

function pdHttpFetch(string $url, int $timeout = 12): array
{
    $headers = [
        'Accept: application/json,text/csv;q=0.9,*/*;q=0.8',
        'Cache-Control: no-cache, no-store, must-revalidate',
        'Pragma: no-cache',
        'User-Agent: Mozilla/5.0 (compatible; CoinbasePortfolioDashboard/1.0)',
    ];
    $body = false;
    $httpCode = 0;
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        $curlOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => max(2, min(8, $timeout)),
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
        ];
        curl_setopt_array($curl, $curlOptions);
        $body = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        if (($body === false || $body === '') && str_contains(strtolower($curlError), 'certificate')) {
            curl_setopt_array($curl, $curlOptions + [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            $body = curl_exec($curl);
            $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }
        curl_close($curl);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
                'ignore_errors' => true,
                'header' => implode("\r\n", $headers),
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
            $httpCode = (int)$match[1];
        }
    }
    return [
        'ok' => is_string($body) && $body !== '' && ($httpCode === 0 || ($httpCode >= 200 && $httpCode < 300)),
        'status' => $httpCode,
        'body' => is_string($body) ? $body : '',
    ];
}

function pdAnyExternalConnectivityAvailable(): bool
{
    foreach ([
        'https://www.google.com',
        'https://google.com',
        'https://www.microsoft.com',
        'https://microsoft.com',
    ] as $url) {
        $response = pdHttpFetch($url, 8);
        if (!empty($response['ok'])) return true;
        $status = (int)($response['status'] ?? 0);
        if ($status >= 200 && $status < 500) return true;
    }
    return false;
}

function pdAlphaVantageStockLooksReal(string $symbol): ?bool
{
    $apiKey = pdEnvironmentValue('ALPHAVANTAGE_API_KEY');
    if ($apiKey === '') return null;

    $clean = strtoupper(trim($symbol));
    $clean = preg_replace('/[^A-Z0-9.\-^]/', '', $clean);
    if (!is_string($clean) || $clean === '') return false;

    $params = http_build_query([
        'function' => 'GLOBAL_QUOTE',
        'symbol' => $clean,
        'apikey' => $apiKey,
    ]);
    $response = pdHttpFetch('https://www.alphavantage.co/query?' . $params, 12);
    $body = (string)($response['body'] ?? '');
    if ($body === '') return null;
    $json = json_decode($body, true);
    if (!is_array($json)) return null;
    if (isset($json['Note']) || isset($json['Information']) || isset($json['Error Message'])) {
        return null;
    }
    $quote = is_array($json['Global Quote'] ?? null) ? $json['Global Quote'] : [];
    $price = $quote['05. price'] ?? null;
    if (is_numeric($price) && (float)$price > 0.0) return true;
    if ($quote) return false;
    return false;
}

function pdRemoveTargets(array $targets, array $symbolsToRemove): array
{
    $removeMap = [];
    foreach ($symbolsToRemove as $symbol) {
        if (!is_string($symbol) || trim($symbol) === '') continue;
        $removeMap[strtoupper(trim($symbol))] = true;
    }
    if (!$removeMap) return array_values($targets);

    $filtered = [];
    foreach ($targets as $target) {
        if (!is_array($target)) continue;
        $symbol = strtoupper(trim((string)($target['symbol'] ?? '')));
        if ($symbol !== '' && isset($removeMap[$symbol])) {
            continue;
        }
        $filtered[] = $target;
    }
    return array_values($filtered);
}

function pdWalletState(string $symbol): array
{
    return pdLoadJson(__DIR__ . '/tickers/' . $symbol . '-model-wallet-state.json');
}

function pdChartPoints(string $symbol, int $limit = 48): array
{
    $csvPath = __DIR__ . '/tickers/' . $symbol . '.csv';
    if (!is_file($csvPath)) return [];
    $lines = @file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines) || count($lines) < 2) return [];
    $points = [];
    $slice = array_slice($lines, -max(2, $limit));
    foreach ($slice as $index => $line) {
        if ($index === 0 && stripos((string)$line, 'timestamp') !== false) continue;
        $row = str_getcsv((string)$line);
        if (!isset($row[0], $row[4]) || !is_numeric($row[4])) continue;
        $points[] = [
            'time' => (string)$row[0],
            'close' => (float)$row[4],
        ];
    }
    return $points;
}

$registryPath = __DIR__ . '/wsl_portfolio_targets.json';
$statusPath = __DIR__ . '/wsl_portfolio_status.json';
$cronPhpPath = __DIR__ . '/wsl_portfolio_cron.php';
$registry = pdLoadJson($registryPath);
$targets = is_array($registry['targets'] ?? null) ? $registry['targets'] : [];
$status = pdLoadJson($statusPath);
$message = '';
$error = '';
$installResult = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marketType = (($_POST['market_type'] ?? '') === 'stock') ? 'stock' : 'crypto';
    $symbol = pdNormalizeSymbol($marketType, (string)($_POST['symbol'] ?? ''));
    $buyMultiplier = is_numeric($_POST['buy_multiplier'] ?? null) ? max(0.10, min(5.00, (float)$_POST['buy_multiplier'])) : 1.10;
    $sellMultiplier = is_numeric($_POST['sell_multiplier'] ?? null) ? max(0.10, min(5.00, (float)$_POST['sell_multiplier'])) : 1.00;
    $trustPercent = is_numeric($_POST['trust_percent'] ?? null) ? max(1.0, min(100.0, (float)$_POST['trust_percent'])) : 75.0;
    $intervalMinutes = is_numeric($_POST['interval_minutes'] ?? null) ? max(5, min(60, (int)$_POST['interval_minutes'])) : 5;
    $futureSteps = is_numeric($_POST['future_steps'] ?? null) ? max(1, min(49, (int)$_POST['future_steps'])) : 49;

    if ($symbol === '') {
        $error = 'Symbol is required.';
    } else {
        $targetsByKey = [];
        foreach ($targets as $target) {
            if (!is_array($target)) continue;
            $key = strtolower(trim((string)($target['market_type'] ?? ''))) . '|' . strtoupper(trim((string)($target['symbol'] ?? '')));
            if ($key === '|') continue;
            $targetsByKey[$key] = $target;
        }

        $key = $marketType . '|' . $symbol;
        $targetsByKey[$key] = [
            'market_type' => $marketType,
            'symbol' => $symbol,
            'base_url' => pdDetectBaseUrl(),
            'buy_multiplier' => $buyMultiplier,
            'sell_multiplier' => $sellMultiplier,
            'trust_percent' => $trustPercent,
            'interval_minutes' => $intervalMinutes,
            'future_steps' => $futureSteps,
            'updated_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ];

        uasort($targetsByKey, static function (array $a, array $b): int {
            return strcmp(($a['market_type'] ?? '') . '|' . ($a['symbol'] ?? ''), ($b['market_type'] ?? '') . '|' . ($b['symbol'] ?? ''));
        });
        $targets = array_values($targetsByKey);
        pdSaveJson($registryPath, ['targets' => $targets]);
        $installResult = pdInstallWindowsScheduler($cronPhpPath, $registryPath, $statusPath);
        $message = 'Saved ' . $symbol . ' for 5-minute Windows scheduler tracking with a 49-step forward horizon.';
        if (!($installResult['ok'] ?? false)) {
            $error = 'Windows Task Scheduler did not confirm from this request. The generated runner command is still shown below.';
        }
        $status = pdLoadJson($statusPath);
    }
}

$trackedCryptoSymbols = [];
$trackedStockSymbols = [];
foreach ($targets as $target) {
    if (!is_array($target)) continue;
    $marketType = strtolower(trim((string)($target['market_type'] ?? ''))) === 'stock' ? 'stock' : 'crypto';
    $symbol = strtoupper(trim((string)($target['symbol'] ?? '')));
    if ($symbol === '') continue;
    if ($marketType === 'crypto') {
        $trackedCryptoSymbols[] = $symbol;
    } else {
        $trackedStockSymbols[] = $symbol;
    }
}
$batchAllPrices = pdFetchYahooLatestPrices(array_values(array_unique(array_merge($trackedCryptoSymbols, $trackedStockSymbols))));
$missingCryptoSymbols = [];
foreach (array_values(array_unique($trackedCryptoSymbols)) as $symbol) {
    if (!array_key_exists($symbol, $batchAllPrices)) {
        $missingCryptoSymbols[] = $symbol;
    }
}
if ($missingCryptoSymbols) {
    $targets = pdRemoveTargets($targets, $missingCryptoSymbols);
    pdSaveJson($registryPath, ['targets' => $targets]);
}

$missingStockSymbols = [];
foreach (array_values(array_unique($trackedStockSymbols)) as $symbol) {
    if (array_key_exists($symbol, $batchAllPrices)) continue;
    $looksReal = pdYahooSymbolLooksReal($symbol);
    if ($looksReal === false && pdAnyExternalConnectivityAvailable()) {
        $missingStockSymbols[] = $symbol;
    }
}
if ($missingStockSymbols) {
    $targets = pdRemoveTargets($targets, $missingStockSymbols);
    pdSaveJson($registryPath, ['targets' => $targets]);
}

$cards = [];
foreach ($targets as $target) {
    if (!is_array($target)) continue;
    $symbol = strtoupper(trim((string)($target['symbol'] ?? '')));
    $marketType = strtolower(trim((string)($target['market_type'] ?? ''))) === 'stock' ? 'stock' : 'crypto';
    if ($symbol === '') continue;
    $wallet = pdWalletState($symbol);
    if (isset($batchAllPrices[$symbol])) {
        $resolvedPrice = (float)$batchAllPrices[$symbol];
    } else {
        $resolvedPrice = pdLatestTickerPrice($symbol);
    }
    $cards[] = [
        'market_type' => $marketType,
        'symbol' => $symbol,
        'price' => $resolvedPrice,
        'equity' => is_numeric($wallet['equity_value'] ?? null) ? (float)$wallet['equity_value'] : null,
        'net_pnl' => is_numeric($wallet['net_pnl'] ?? null) ? (float)$wallet['net_pnl'] : null,
        'updated_at' => trim((string)($target['updated_at'] ?? '')),
        'buy_multiplier' => is_numeric($target['buy_multiplier'] ?? null) ? (float)$target['buy_multiplier'] : 1.10,
        'sell_multiplier' => is_numeric($target['sell_multiplier'] ?? null) ? (float)$target['sell_multiplier'] : 1.00,
        'trust_percent' => is_numeric($target['trust_percent'] ?? null) ? (float)$target['trust_percent'] : 75.0,
        'future_steps' => is_numeric($target['future_steps'] ?? null) ? (int)$target['future_steps'] : 49,
        'interval_minutes' => is_numeric($target['interval_minutes'] ?? null) ? (int)$target['interval_minutes'] : 5,
        'chart_points' => pdChartPoints($symbol),
    ];
}

$symbol_lookup = [];
foreach ($cards as $card) {
    $symbol_lookup[$card['symbol']] = $card;
}

$cronLinePreview = 'php "' . $cronPhpPath . '"'
    . ' --registry="' . $registryPath . '"'
    . ' --status="' . $statusPath . '"';
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portfolio Cron Dashboard</title>
    <style>
        :root {
            --bg: #07111d;
            --panel: #101e2d;
            --panel-2: #15283c;
            --text: #e7eef8;
            --muted: #9eb0c3;
            --line: rgba(255,255,255,.08);
            --accent: #7ff1b8;
            --danger: #ff7d7d;
            --warning: #ffd36e;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(127,241,184,.10), transparent 24rem),
                linear-gradient(180deg, #07111d 0%, #0a1624 100%);
            color: var(--text);
        }
        .wrap { max-width: 1200px; margin: 0 auto; padding: 28px 20px 40px; }
        .hero { display: grid; gap: 16px; margin-bottom: 22px; }
        .hero h1 { margin: 0; font-size: clamp(2rem, 4vw, 3.2rem); }
        .hero p { margin: 0; color: var(--muted); line-height: 1.5; }
        .notice, .error {
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 14px;
            margin-top: 8px;
            background: rgba(255,255,255,.04);
        }
        .error { border-color: rgba(255,125,125,.25); color: #ffd7d7; }
        .panel {
            background: linear-gradient(180deg, rgba(16,30,45,.96), rgba(12,22,34,.96));
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: 0 18px 40px rgba(0,0,0,.18);
        }
        form { display: grid; gap: 14px; }
        .grid { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
        label { display: block; font-size: .88rem; color: var(--muted); margin-bottom: 6px; }
        input, select, button, textarea {
            width: 100%;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(8,16,24,.85);
            color: var(--text);
            padding: 11px 12px;
            font: inherit;
        }
        button {
            background: linear-gradient(135deg, #8cf0bf, #5db3ff);
            color: #04111d;
            border: none;
            font-weight: 700;
            cursor: pointer;
        }
        .meta { font-size: .84rem; color: var(--muted); }
        .scroll-row {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(260px, 320px);
            gap: 14px;
            overflow-x: auto;
            padding-bottom: 6px;
        }
        .symbol-list {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 6px;
            flex-wrap: nowrap;
        }
        .symbol-chip {
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(21,40,60,.9);
            color: var(--text);
            border-radius: 999px;
            padding: 10px 14px;
            white-space: nowrap;
            cursor: pointer;
        }
        .card {
            border-radius: 20px;
            padding: 16px;
            background: linear-gradient(180deg, rgba(21,40,60,.98), rgba(10,18,29,.98));
            border: 1px solid rgba(255,255,255,.10);
            min-height: 230px;
            cursor: pointer;
        }
        .card h2 { margin: 0 0 4px; font-size: 1.35rem; }
        .kicker { color: var(--muted); font-size: .83rem; text-transform: uppercase; letter-spacing: .08em; }
        .price { font-size: 2rem; margin: 14px 0 10px; }
        .stat { margin: 8px 0; color: var(--muted); }
        .stat strong { color: var(--text); }
        .good { color: var(--accent); }
        .bad { color: var(--danger); }
        .code {
            white-space: pre-wrap;
            word-break: break-word;
            font-family: Consolas, monospace;
            background: rgba(0,0,0,.24);
            border-radius: 14px;
            padding: 14px;
            border: 1px solid var(--line);
        }
        .footer-note {
            margin-top: 18px;
            color: var(--muted);
            font-size: .88rem;
            line-height: 1.5;
        }
        .modal-shell {
            position: fixed;
            inset: 0;
            background: rgba(3,8,15,.78);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 999;
        }
        .modal-shell.is-open { display: flex; }
        .modal-card {
            width: min(760px, 100%);
            max-height: 85vh;
            overflow-y: auto;
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(18,33,49,.99), rgba(8,16,25,.99));
            border: 1px solid rgba(255,255,255,.10);
            padding: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
        }
        .modal-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 16px;
        }
        .modal-close {
            width: auto;
            padding: 8px 12px;
            background: rgba(255,255,255,.08);
            color: var(--text);
            border: 1px solid rgba(255,255,255,.10);
        }
        .modal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }
        .modal-stat {
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px;
            padding: 12px;
            background: rgba(255,255,255,.03);
        }
        .modal-stat span {
            display: block;
            color: var(--muted);
            font-size: .82rem;
            margin-bottom: 6px;
        }
        .modal-stat strong {
            display: block;
            font-size: 1.05rem;
        }
        .modal-chart {
            margin: 16px 0 18px;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 18px;
            background: rgba(255,255,255,.03);
            padding: 12px;
        }
        .modal-chart canvas {
            width: 100%;
            height: 220px;
            display: block;
        }
        .modal-form {
            margin-top: 16px;
            display: grid;
            gap: 14px;
        }
    </style>
</head>
<body>
<main class="wrap">
    <section class="hero">
        <h1>Windows Portfolio Scheduler Dashboard</h1>
        <p>This page tracks stock and crypto symbols, attempts to install a 5-minute Windows scheduled task for your home computer, and shows a sideways-scrolling portfolio dashboard. All information shown here is price, simulated portfolio, and educational market whatnot only. It is not live brokerage execution or investment advice.</p>
        <?php if ($message !== ''): ?><div class="notice"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    </section>

    <section class="panel">
        <form method="post" action="">
            <div class="grid">
                <div>
                    <label for="marketType">Market type</label>
                    <select id="marketType" name="market_type">
                        <option value="crypto">Crypto</option>
                        <option value="stock">Stock</option>
                    </select>
                </div>
                <div>
                    <label for="symbol">New symbol request</label>
                    <input id="symbol" name="symbol" type="text" placeholder="BTC, ETH, AAPL, TSLA" required>
                </div>
                <div>
                    <label for="buyMultiplier">Buy multiplier</label>
                    <input id="buyMultiplier" name="buy_multiplier" type="number" min="0.10" max="5.00" step="0.05" value="0.90">
                </div>
                <div>
                    <label for="sellMultiplier">Sell multiplier</label>
                    <input id="sellMultiplier" name="sell_multiplier" type="number" min="0.10" max="5.00" step="0.05" value="0.80">
                </div>
                <div>
                    <label for="trustPercent">Trust percent</label>
                    <input id="trustPercent" name="trust_percent" type="number" min="1" max="100" step="0.5" value="90.00">
                </div>
            </div>
            <button type="submit">Add symbol and install Windows scheduler</button>
            <div class="meta">Each symbol is scheduled for every 5 minutes and carries a forward horizon of 49 five-minute steps.</div>
        </form>
    </section>

    <section class="panel">
        <h2 style="margin-top:0;">Tracked symbols</h2>
        <div class="symbol-list" aria-label="Everything this page should watch">
            <?php foreach ($cards as $card): ?>
                <button
                    type="button"
                    class="symbol-chip"
                    data-open-symbol="<?= htmlspecialchars($card['symbol']) ?>"
                ><?= htmlspecialchars($card['symbol']) ?></button>
            <?php endforeach; ?>
            <?php if (!$cards): ?>
                <div class="meta">No symbols registered yet.</div>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <h2 style="margin-top:0;">Portfolio</h2>
        <div class="scroll-row">
            <?php foreach ($cards as $card): ?>
                <?php
                $price = is_numeric($card['price']) ? '$' . number_format((float)$card['price'], 2) : 'Waiting for price';
                $equity = is_numeric($card['equity']) ? '$' . number_format((float)$card['equity'], 2) : '—';
                $netPnl = is_numeric($card['net_pnl']) ? (float)$card['net_pnl'] : null;
                $pnlLabel = $netPnl === null ? '—' : (($netPnl >= 0 ? '+' : '-') . '$' . number_format(abs($netPnl), 2));
                $pnlClass = $netPnl === null ? '' : ($netPnl >= 0 ? 'good' : 'bad');
                ?>
                <article class="card" data-open-symbol="<?= htmlspecialchars($card['symbol']) ?>">
                    <div class="kicker"><?= htmlspecialchars(strtoupper($card['market_type'])) ?></div>
                    <h2><?= htmlspecialchars($card['symbol']) ?></h2>
                    <div class="price"><?= htmlspecialchars($price) ?></div>
                    <div class="stat">Paper equity <strong><?= htmlspecialchars($equity) ?></strong></div>
                    <div class="stat">Net move <strong class="<?= htmlspecialchars($pnlClass) ?>"><?= htmlspecialchars($pnlLabel) ?></strong></div>
                    <div class="stat">Buy multiplier <strong><?= number_format((float)$card['buy_multiplier'], 2) ?>x avg</strong></div>
                    <div class="stat">Sell multiplier <strong><?= number_format((float)$card['sell_multiplier'], 2) ?>x avg</strong></div>
                    <div class="stat">Trust floor <strong><?= number_format((float)$card['trust_percent'], 2) ?>%</strong></div>
                    <div class="stat">Schedule <strong>Every <?= (int)$card['interval_minutes'] ?> minutes</strong></div>
                    <div class="stat">Forward window <strong><?= (int)$card['future_steps'] ?> x 5-minute steps</strong></div>
                    <div class="stat">Updated <strong><?= htmlspecialchars($card['updated_at'] !== '' ? $card['updated_at'] : 'Not yet') ?></strong></div>
                </article>
            <?php endforeach; ?>
            <?php if (!$cards): ?>
                <article class="card">
                    <div class="kicker">Portfolio</div>
                    <h2>No symbols yet</h2>
                    <div class="stat">Add a stock or crypto above and this dashboard will start building a side-scrolling portfolio.</div>
                </article>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <h2 style="margin-top:0;">Windows Scheduler Status</h2>
        <div class="stat">Last runner status file: <strong><?= htmlspecialchars(trim((string)($status['ran_at'] ?? 'Never'))) ?></strong></div>
        <div class="stat">Tracked symbols: <strong><?= count($cards) ?></strong></div>
        <div class="code"><?= htmlspecialchars(($installResult['cron_line'] ?? $cronLinePreview) . PHP_EOL . PHP_EOL . ($installResult['message'] ?? 'No install attempt in this request.')) ?></div>
        <div class="footer-note">This page tries to install a Windows scheduled task that runs every five minutes. If PHP cannot create the task, the runner command above still shows exactly what should run. All dashboard values are educational price and simulated portfolio information only.</div>
    </section>
</main>
<div id="symbolModal" class="modal-shell" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-top">
            <div>
                <div id="modalKicker" class="kicker">TRACKED SYMBOL</div>
                <h2 id="modalTitle" style="margin:4px 0 0;">Symbol</h2>
            </div>
            <button type="button" class="modal-close" id="closeSymbolModal">Close</button>
        </div>
        <div class="modal-grid">
            <div class="modal-stat"><span>Price</span><strong id="modalPrice">—</strong></div>
            <div class="modal-stat"><span>Paper equity</span><strong id="modalEquity">—</strong></div>
            <div class="modal-stat"><span>Net move</span><strong id="modalNetPnl">—</strong></div>
            <div class="modal-stat"><span>Buy multiplier</span><strong id="modalBuyMultiplier">—</strong></div>
            <div class="modal-stat"><span>Sell multiplier</span><strong id="modalSellMultiplier">—</strong></div>
            <div class="modal-stat"><span>Trust floor</span><strong id="modalTrustPercent">—</strong></div>
            <div class="modal-stat"><span>Schedule</span><strong id="modalSchedule">—</strong></div>
            <div class="modal-stat"><span>Forward window</span><strong id="modalFutureSteps">—</strong></div>
            <div class="modal-stat"><span>Updated</span><strong id="modalUpdated">—</strong></div>
        </div>
        <div class="modal-chart">
            <canvas id="modalChart" width="700" height="220"></canvas>
        </div>
        <form method="post" action="" class="modal-form">
            <input type="hidden" name="market_type" id="modalMarketType" value="crypto">
            <div class="grid">
                <div>
                    <label for="modalSymbolInput">Symbol</label>
                    <input id="modalSymbolInput" name="symbol" type="text" readonly>
                </div>
                <div>
                    <label for="modalBuyMultiplierInput">Buy multiplier</label>
                    <input id="modalBuyMultiplierInput" name="buy_multiplier" type="number" min="0.10" max="5.00" step="0.05">
                </div>
                <div>
                    <label for="modalSellMultiplierInput">Sell multiplier</label>
                    <input id="modalSellMultiplierInput" name="sell_multiplier" type="number" min="0.10" max="5.00" step="0.05">
                </div>
                <div>
                    <label for="modalTrustPercentInput">Trust percent</label>
                    <input id="modalTrustPercentInput" name="trust_percent" type="number" min="1" max="100" step="0.5">
                </div>
                <div>
                    <label for="modalIntervalInput">Interval minutes</label>
                    <input id="modalIntervalInput" name="interval_minutes" type="number" min="5" max="60" step="5">
                </div>
                <div>
                    <label for="modalFutureInput">Forward steps</label>
                    <input id="modalFutureInput" name="future_steps" type="number" min="1" max="49" step="1">
                </div>
            </div>
            <button type="submit">Save symbol dynamics</button>
        </form>
        <div class="footer-note" style="margin-top:16px;">This modal is a scrollable details view for the tracked symbol. All values are price, simulated portfolio, and educational market whatnot only.</div>
    </div>
</div>
<script>
const trackedSymbols = <?= json_encode(array_values($cards), JSON_UNESCAPED_SLASHES) ?>;
const trackedMap = Object.fromEntries(trackedSymbols.map(card => [card.symbol, card]));
const modalShell = document.getElementById('symbolModal');
const modalTitle = document.getElementById('modalTitle');
const modalKicker = document.getElementById('modalKicker');
const modalPrice = document.getElementById('modalPrice');
const modalEquity = document.getElementById('modalEquity');
const modalNetPnl = document.getElementById('modalNetPnl');
const modalBuyMultiplier = document.getElementById('modalBuyMultiplier');
const modalSellMultiplier = document.getElementById('modalSellMultiplier');
const modalTrustPercent = document.getElementById('modalTrustPercent');
const modalSchedule = document.getElementById('modalSchedule');
const modalFutureSteps = document.getElementById('modalFutureSteps');
const modalUpdated = document.getElementById('modalUpdated');
const modalMarketType = document.getElementById('modalMarketType');
const modalSymbolInput = document.getElementById('modalSymbolInput');
const modalBuyMultiplierInput = document.getElementById('modalBuyMultiplierInput');
const modalSellMultiplierInput = document.getElementById('modalSellMultiplierInput');
const modalTrustPercentInput = document.getElementById('modalTrustPercentInput');
const modalIntervalInput = document.getElementById('modalIntervalInput');
const modalFutureInput = document.getElementById('modalFutureInput');
const modalChart = document.getElementById('modalChart');
const closeSymbolModal = document.getElementById('closeSymbolModal');
const marketTypeInput = document.getElementById('marketType');
const symbolInput = document.getElementById('symbol');
const buyMultiplierInput = document.getElementById('buyMultiplier');
const sellMultiplierInput = document.getElementById('sellMultiplier');
const trustPercentInput = document.getElementById('trustPercent');

function dashboardSymbolPreset(marketType, symbol) {
    const normalizedMarket = String(marketType || '').toLowerCase();
    const normalizedSymbol = String(symbol || '').trim().toUpperCase();
    if (normalizedMarket === 'crypto' && (normalizedSymbol === 'BTC' || normalizedSymbol === 'BTC-USD')) {
        return { buy_multiplier: 0.90, sell_multiplier: 0.80, trust_percent: 90.0 };
    }
    return { buy_multiplier: 1.10, sell_multiplier: 1.00, trust_percent: 75.0 };
}

function syncDashboardPreset() {
    if (!marketTypeInput || !symbolInput || !buyMultiplierInput || !sellMultiplierInput || !trustPercentInput) return;
    const preset = dashboardSymbolPreset(marketTypeInput.value, symbolInput.value);
    buyMultiplierInput.value = Number(preset.buy_multiplier).toFixed(2);
    sellMultiplierInput.value = Number(preset.sell_multiplier).toFixed(2);
    trustPercentInput.value = Number(preset.trust_percent).toFixed(2);
}

function moneyOrDash(value) {
    return typeof value === 'number' && Number.isFinite(value) ? '$' + value.toFixed(2) : '—';
}

function pnlText(value) {
    if (typeof value !== 'number' || !Number.isFinite(value)) return '—';
    return (value >= 0 ? '+' : '-') + '$' + Math.abs(value).toFixed(2);
}

function drawModalChart(points) {
    if (!modalChart) return;
    const ctx = modalChart.getContext('2d');
    if (!ctx) return;
    const width = modalChart.width;
    const height = modalChart.height;
    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#0b1724';
    ctx.fillRect(0, 0, width, height);
    if (!Array.isArray(points) || points.length < 2) {
        ctx.fillStyle = '#9eb0c3';
        ctx.font = '16px Georgia';
        ctx.fillText('No chart data yet', 20, 34);
        return;
    }
    const prices = points.map(point => Number(point.close || 0)).filter(value => Number.isFinite(value) && value > 0);
    if (prices.length < 2) {
        ctx.fillStyle = '#9eb0c3';
        ctx.font = '16px Georgia';
        ctx.fillText('No chart data yet', 20, 34);
        return;
    }
    const min = Math.min(...prices);
    const max = Math.max(...prices);
    const span = Math.max(0.000001, max - min);
    const pad = 18;
    ctx.strokeStyle = 'rgba(255,255,255,0.08)';
    ctx.lineWidth = 1;
    for (let i = 0; i < 4; i++) {
        const y = pad + ((height - pad * 2) / 3) * i;
        ctx.beginPath();
        ctx.moveTo(pad, y);
        ctx.lineTo(width - pad, y);
        ctx.stroke();
    }
    ctx.strokeStyle = prices[prices.length - 1] >= prices[0] ? '#7ff1b8' : '#ff7d7d';
    ctx.lineWidth = 2.5;
    ctx.beginPath();
    prices.forEach((price, index) => {
        const x = pad + ((width - pad * 2) * index / (prices.length - 1));
        const y = height - pad - (((price - min) / span) * (height - pad * 2));
        if (index === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
    });
    ctx.stroke();
    ctx.fillStyle = '#9eb0c3';
    ctx.font = '13px Georgia';
    ctx.fillText('$' + max.toFixed(2), pad, 14);
    ctx.fillText('$' + min.toFixed(2), pad, height - 6);
}

function openSymbolModal(symbol) {
    const card = trackedMap[symbol];
    if (!card) return;
    modalKicker.textContent = String(card.market_type || '').toUpperCase();
    modalTitle.textContent = String(card.symbol || 'Symbol');
    modalPrice.textContent = moneyOrDash(card.price);
    modalEquity.textContent = moneyOrDash(card.equity);
    modalNetPnl.textContent = pnlText(card.net_pnl);
    modalNetPnl.className = (card.net_pnl ?? 0) >= 0 ? 'good' : 'bad';
    modalBuyMultiplier.textContent = Number(card.buy_multiplier || 0).toFixed(2) + 'x avg';
    modalSellMultiplier.textContent = Number(card.sell_multiplier || 0).toFixed(2) + 'x avg';
    modalTrustPercent.textContent = Number(card.trust_percent || 75).toFixed(2) + '%';
    modalSchedule.textContent = 'Every ' + Number(card.interval_minutes || 5) + ' minutes';
    modalFutureSteps.textContent = Number(card.future_steps || 49) + ' x 5-minute steps';
    modalUpdated.textContent = String(card.updated_at || 'Not yet');
    modalMarketType.value = String(card.market_type || 'crypto');
    modalSymbolInput.value = String(card.symbol || '');
    modalBuyMultiplierInput.value = Number(card.buy_multiplier || 1.10).toFixed(2);
    modalSellMultiplierInput.value = Number(card.sell_multiplier || 1.00).toFixed(2);
    modalTrustPercentInput.value = Number(card.trust_percent || 75).toFixed(2);
    modalIntervalInput.value = Number(card.interval_minutes || 5);
    modalFutureInput.value = Number(card.future_steps || 49);
    drawModalChart(Array.isArray(card.chart_points) ? card.chart_points : []);
    modalShell.classList.add('is-open');
    modalShell.setAttribute('aria-hidden', 'false');
}

function closeModal() {
    modalShell.classList.remove('is-open');
    modalShell.setAttribute('aria-hidden', 'true');
}

document.querySelectorAll('[data-open-symbol]').forEach(node => {
    node.addEventListener('click', () => openSymbolModal(node.getAttribute('data-open-symbol')));
});
closeSymbolModal.addEventListener('click', closeModal);
modalShell.addEventListener('click', (event) => {
    if (event.target === modalShell) closeModal();
});
if (marketTypeInput && symbolInput) {
    marketTypeInput.addEventListener('change', syncDashboardPreset);
    symbolInput.addEventListener('input', syncDashboardPreset);
    syncDashboardPreset();
}
</script>
</body>
</html>
