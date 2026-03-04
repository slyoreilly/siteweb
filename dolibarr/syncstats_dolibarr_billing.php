#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * SyncStats -> Dolibarr billing bridge.
 *
 * Usage:
 *   php syncstats_dolibarr_billing.php --period_start=YYYY-MM-DD --period_end=YYYY-MM-DD [--config=/path/to/config.php]
 */

const DEFAULT_CONFIG_FILE = __DIR__ . '/syncstats_dolibarr_billing.config.php';

main($argv);

function main(array $argv): void
{
    $options = getopt('', ['period_start:', 'period_end:', 'config::']);

    $periodStart = $options['period_start'] ?? null;
    $periodEnd = $options['period_end'] ?? null;

    if (!isValidDate($periodStart) || !isValidDate($periodEnd)) {
        fwrite(STDERR, "Usage: php {$argv[0]} --period_start=YYYY-MM-DD --period_end=YYYY-MM-DD [--config=/path/to/config.php]\n");
        exit(1);
    }

    if ($periodStart > $periodEnd) {
        fwrite(STDERR, "Error: period_start must be before or equal to period_end.\n");
        exit(1);
    }

    $configFile = $options['config'] ?? DEFAULT_CONFIG_FILE;
    $config = loadConfig($configFile);

    $pdo = createPdo($config['mysql']);
    $dolibarr = new DolibarrClient($config['dolibarr']['base_url'], $config['dolibarr']['api_key']);

    $clients = fetchAllThirdParties($dolibarr, (int)($config['dolibarr']['page_size'] ?? 100));

    logInfo(sprintf('Found %d clients from Dolibarr.', count($clients)));

    foreach ($clients as $client) {
        processClient($client, $pdo, $dolibarr, $periodStart, $periodEnd, $config);
    }

    logInfo('Billing run completed.');
}

function loadConfig(string $configFile): array
{
    $config = [
        'mysql' => [
            'host' => getenv('SYNCSTATS_DB_HOST') ?: '127.0.0.1',
            'port' => (int)(getenv('SYNCSTATS_DB_PORT') ?: 3306),
            'database' => getenv('SYNCSTATS_DB_NAME') ?: '',
            'username' => getenv('SYNCSTATS_DB_USER') ?: '',
            'password' => getenv('SYNCSTATS_DB_PASSWORD') ?: '',
            'charset' => getenv('SYNCSTATS_DB_CHARSET') ?: 'utf8mb4',
        ],
        'dolibarr' => [
            'base_url' => rtrim((string)(getenv('DOLIBARR_BASE_URL') ?: ''), '/'),
            'api_key' => (string)(getenv('DOLIBARR_API_KEY') ?: ''),
            'page_size' => (int)(getenv('DOLIBARR_PAGE_SIZE') ?: 100),
        ],
        'billing' => [
            'price_per_match' => (float)(getenv('SYNCSTATS_PRICE_PER_MATCH') ?: 0),
            'dry_run' => filter_var(getenv('SYNCSTATS_DRY_RUN') ?: false, FILTER_VALIDATE_BOOL),
            'league_names' => [],
        ],
    ];

    if (is_file($configFile)) {
        $fileConfig = require $configFile;
        if (!is_array($fileConfig)) {
            throw new RuntimeException("Config file must return an array: {$configFile}");
        }
        $config = array_replace_recursive($config, $fileConfig);
    }

    if ($config['mysql']['database'] === '' || $config['mysql']['username'] === '') {
        throw new RuntimeException('MySQL configuration is incomplete (database/username required).');
    }

    if ($config['dolibarr']['base_url'] === '' || $config['dolibarr']['api_key'] === '') {
        throw new RuntimeException('Dolibarr configuration is incomplete (base_url/api_key required).');
    }

    return $config;
}

function createPdo(array $mysqlConfig): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $mysqlConfig['host'],
        $mysqlConfig['port'],
        $mysqlConfig['database'],
        $mysqlConfig['charset']
    );

    $pdo = new PDO($dsn, $mysqlConfig['username'], $mysqlConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function fetchAllThirdParties(DolibarrClient $client, int $pageSize): array
{
    $all = [];
    $page = 0;

    do {
        $data = $client->request('GET', '/thirdparties', [
            'limit' => $pageSize,
            'page' => $page,
            'sortfield' => 't.rowid',
            'sortorder' => 'ASC',
        ]);

        if (!is_array($data)) {
            throw new RuntimeException('Unexpected thirdparties response format.');
        }

        $chunk = array_values(array_filter($data, 'is_array'));
        $all = array_merge($all, $chunk);
        $page++;
    } while (count($chunk) === $pageSize);

    return $all;
}

function processClient(array $client, PDO $pdo, DolibarrClient $dolibarr, string $periodStart, string $periodEnd, array $config): void
{
    $clientId = (int)($client['id'] ?? $client['rowid'] ?? 0);
    $clientName = (string)($client['name'] ?? $client['nom'] ?? ('#' . $clientId));

    if ($clientId <= 0) {
        logInfo('Skipping client with missing id.');
        return;
    }

    logInfo("Processing client: {$clientName} (ID {$clientId})");

    $leagueIds = extractLeagueIds($client);
    if ($leagueIds === []) {
        logInfo('No league_ids configured, skipping client.');
        return;
    }

    logInfo('Leagues: ' . implode(',', $leagueIds));

    try {
        $matchesByLeague = countBillableMatchesByLeague($pdo, $leagueIds, $periodStart, $periodEnd, $config['billing']['league_names'] ?? []);
    } catch (Throwable $e) {
        logError("Failed counting matches for client {$clientId}: {$e->getMessage()}");
        return;
    }

    $totalMatches = array_sum(array_column($matchesByLeague, 'matches'));

    if ($totalMatches <= 0) {
        logInfo('No matches for this client.');
        return;
    }

    logInfo('Matches found:');
    foreach ($matchesByLeague as $row) {
        logInfo(sprintf('%s: %d', $row['league_name'], $row['matches']));
    }
    logInfo("Total matches: {$totalMatches}");

    $billingMarker = "SyncStats billing {$periodStart} → {$periodEnd}";

    try {
        if (invoiceAlreadyExists($dolibarr, $clientId, $billingMarker)) {
            logInfo('Invoice already exists for this period, skipping client.');
            return;
        }

        if (!empty($config['billing']['dry_run'])) {
            logInfo('[DRY RUN] Invoice creation skipped.');
            return;
        }

        $invoiceId = createInvoice($dolibarr, $clientId, $periodEnd, $billingMarker);
        $description = buildInvoiceDescription($periodStart, $periodEnd, $matchesByLeague);
        addInvoiceLine($dolibarr, $invoiceId, $description, $totalMatches, (float)$config['billing']['price_per_match']);

        logInfo("Invoice created: {$invoiceId}");
    } catch (Throwable $e) {
        logError("Client {$clientId} failed: {$e->getMessage()}");
    }
}

function extractLeagueIds(array $client): array
{
    $raw = null;

    if (isset($client['league_ids'])) {
        $raw = $client['league_ids'];
    } elseif (isset($client['extrafields']['league_ids'])) {
        $raw = $client['extrafields']['league_ids'];
    } elseif (isset($client['array_options']['options_league_ids'])) {
        $raw = $client['array_options']['options_league_ids'];
    }

    if (!is_string($raw) || trim($raw) === '') {
        return [];
    }

    $ids = array_values(array_unique(array_filter(array_map(
        static function ($value) {
            $value = trim($value);
            return ctype_digit($value) ? (int)$value : null;
        },
        explode(',', $raw)
    ))));

    return $ids;
}

function countBillableMatchesByLeague(PDO $pdo, array $leagueIds, string $periodStart, string $periodEnd, array $leagueNames): array
{
    $placeholders = implode(',', array_fill(0, count($leagueIds), '?'));

    $sql = "
        SELECT
            tm.ligueRef AS league_id,
            COUNT(DISTINCT tm.match_id) AS matches
        FROM TableMatch tm
        INNER JOIN Video v ON tm.match_id = v.nomMatch
        WHERE tm.ligueRef IN ({$placeholders})
          AND tm.date >= ?
          AND tm.date < DATE_ADD(?, INTERVAL 1 DAY)
        GROUP BY tm.ligueRef
        ORDER BY tm.ligueRef ASC
    ";

    $stmt = $pdo->prepare($sql);
    $params = array_merge($leagueIds, [$periodStart, $periodEnd]);
    $stmt->execute($params);

    $results = [];
    while ($row = $stmt->fetch()) {
        $leagueId = (int)$row['league_id'];
        $results[] = [
            'league_id' => $leagueId,
            'league_name' => $leagueNames[$leagueId] ?? "Ligue {$leagueId}",
            'matches' => (int)$row['matches'],
        ];
    }

    return $results;
}

function invoiceAlreadyExists(DolibarrClient $dolibarr, int $clientId, string $marker): bool
{
    $page = 0;
    $limit = 100;

    do {
        $data = $dolibarr->request('GET', '/invoices', [
            'socid' => $clientId,
            'limit' => $limit,
            'page' => $page,
            'sortfield' => 't.rowid',
            'sortorder' => 'DESC',
        ]);

        if (!is_array($data)) {
            break;
        }

        $invoices = array_values(array_filter($data, 'is_array'));
        foreach ($invoices as $invoice) {
            $notePublic = (string)($invoice['note_public'] ?? '');
            if (str_contains($notePublic, $marker)) {
                return true;
            }
        }

        $page++;
    } while (count($invoices) === $limit);

    return false;
}

function createInvoice(DolibarrClient $dolibarr, int $clientId, string $periodEnd, string $notePublic): int
{
    $payload = [
        'socid' => $clientId,
        'date' => $periodEnd,
        'note_public' => $notePublic,
    ];

    $response = $dolibarr->request('POST', '/invoices', [], $payload);

    if (is_int($response)) {
        return $response;
    }
    if (is_string($response) && ctype_digit($response)) {
        return (int)$response;
    }
    if (is_array($response) && isset($response['id'])) {
        return (int)$response['id'];
    }

    throw new RuntimeException('Unable to determine created invoice id.');
}

function addInvoiceLine(DolibarrClient $dolibarr, int $invoiceId, string $description, int $qty, float $subprice): void
{
    $payload = [
        'desc' => $description,
        'qty' => $qty,
        'subprice' => $subprice,
    ];

    $dolibarr->request('POST', "/invoices/{$invoiceId}/lines", [], $payload);
}

function buildInvoiceDescription(string $periodStart, string $periodEnd, array $matchesByLeague): string
{
    $lines = [
        'Captation vidéo SyncStats',
        "Période : {$periodStart} → {$periodEnd}",
        '',
    ];

    foreach ($matchesByLeague as $row) {
        $lines[] = sprintf('%s : %d matchs', $row['league_name'], $row['matches']);
    }

    return implode("\n", $lines);
}

function isValidDate(?string $date): bool
{
    if (!is_string($date)) {
        return false;
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    return $dt !== false && $dt->format('Y-m-d') === $date;
}

function logInfo(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . "] INFO  {$message}\n";
}

function logError(string $message): void
{
    fwrite(STDERR, '[' . date('Y-m-d H:i:s') . "] ERROR {$message}\n");
}

final class DolibarrClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function request(string $method, string $path, array $query = [], ?array $payload = null)
    {
        $url = $this->baseUrl . '/api/index.php' . $path;
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize cURL.');
        }

        $headers = [
            'DOLAPIKEY: ' . $this->apiKey,
            'Accept: application/json',
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
        ]);

        if ($payload !== null) {
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($body === false) {
                throw new RuntimeException('JSON encoding failed for payload.');
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
        }

        $raw = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Dolibarr API cURL error: ' . $curlErr);
        }

        if ($httpCode >= 400) {
            throw new RuntimeException("Dolibarr API HTTP {$httpCode}: {$raw}");
        }

        if ($raw === '' || $raw === 'null') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        if (ctype_digit(trim($raw))) {
            return (int)trim($raw);
        }

        return $raw;
    }
}
