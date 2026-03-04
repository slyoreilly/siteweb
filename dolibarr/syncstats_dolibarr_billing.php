#!/usr/bin/env php
<?php

declare(strict_types=1);

const DEFAULT_CONFIG_FILE = __DIR__ . '/syncstats_dolibarr_billing.config.php';

main($argv);

function main(array $argv): void
{
    $options = getopt('', [
        'periode_debut:',
        'periode_fin:',
        'mode::',
        'limite_tiers::',
        'tiers_id::',
        'dry_run::',
        'verbose::',
        'config::',
        // Compatibilité ancienne version
        'period_start::',
        'period_end::',
    ]);

    $periodeDebut = $options['periode_debut'] ?? $options['period_start'] ?? null;
    $periodeFin = $options['periode_fin'] ?? $options['period_end'] ?? null;
    $mode = strtolower((string)($options['mode'] ?? 'brouillon'));
    $limiteTiers = isset($options['limite_tiers']) ? (int)$options['limite_tiers'] : null;
    $tiersId = isset($options['tiers_id']) ? (int)$options['tiers_id'] : null;
    $forceDryRun = parseBoolOption($options, 'dry_run', false);
    $verbose = parseBoolOption($options, 'verbose', false);
    setVerbose($verbose);

    if (!isValidDate($periodeDebut) || !isValidDate($periodeFin)) {
        usage($argv[0]);
        exit(1);
    }

    if ($periodeDebut > $periodeFin) {
        logError('periode_debut doit être <= periode_fin.');
        exit(1);
    }

    if (!in_array($mode, ['brouillon', 'valider'], true)) {
        logError('mode doit être "brouillon" ou "valider".');
        exit(1);
    }

    $config = loadConfig((string)($options['config'] ?? DEFAULT_CONFIG_FILE));
    $statsConn = createStatsConnection($config);
    $config = hydrateDolibarrConfigFromEnv($config);
    $dolibarr = new DolibarrClient($config['dolibarr']['base_url'], $config['dolibarr']['api_key']);

    logInfo(sprintf('Paramètres: periode=%s -> %s, mode=%s, limite_tiers=%s, tiers_id=%s, dry_run_forcé=%s',
        $periodeDebut,
        $periodeFin,
        $mode,
        $limiteTiers === null ? 'none' : (string)$limiteTiers,
        $tiersId === null ? 'none' : (string)$tiersId,
        $forceDryRun ? 'oui' : 'non'
    ));

    if ($mode === 'brouillon') {
        logInfo('Mode brouillon: les factures créées ne seront PAS validées.');
    }

    $tiers = fetchAllThirdParties($dolibarr, (int)$config['dolibarr']['page_size']);
    logDebug('Chargement tiers terminé.');
    logInfo(sprintf('Tiers récupérés depuis Dolibarr: %d', count($tiers)));

    $processed = 0;
    foreach ($tiers as $tier) {
        $currentTierId = (int)($tier['id'] ?? $tier['rowid'] ?? 0);
        if ($tiersId !== null && $tiersId > 0 && $currentTierId !== $tiersId) {
            continue;
        }
        if ($limiteTiers !== null && $limiteTiers > 0 && $processed >= $limiteTiers) {
            logInfo("Limite atteinte ({$limiteTiers}), arrêt de la boucle.");
            break;
        }

        try {
            processTier($tier, $statsConn, $dolibarr, $periodeDebut, $periodeFin, $mode, $config, $forceDryRun);
        } catch (Throwable $e) {
            logError('Erreur non gérée sur un tiers: ' . $e->getMessage());
        }

        $processed++;
    }

    logInfo('Exécution terminée.');
}

function usage(string $scriptName): void
{
    fwrite(STDERR, "Usage: php {$scriptName} --periode_debut=YYYY-MM-DD --periode_fin=YYYY-MM-DD [--mode=brouillon|valider] [--limite_tiers=N] [--tiers_id=ID] [--dry_run=0|1] [--verbose=1] [--config=/path/file.php]\n");
}

function loadConfig(string $configFile): array
{
    $config = [
        'mysql' => [
            'use_defenvvar' => true,
            'defenvvar_path' => __DIR__ . '/../scriptsphp/defenvvar.php',
        ],
        'dolibarr' => [
            'base_url' => getenv('DOLIBARR_BASE_URL') ?: '',
            'api_key' => getenv('DOLIBARR_API_KEY') ?: '',
            'page_size' => (int)(getenv('DOLIBARR_PAGE_SIZE') ?: 100),
        ],
        'billing' => [
            'signature_prefix' => 'SYNCSTATS|FACTURATION',
            'default_price_per_match' => getenv('SYNCSTATS_DEFAULT_PRICE_PER_MATCH') !== false
                ? (float)getenv('SYNCSTATS_DEFAULT_PRICE_PER_MATCH')
                : null,
            'dry_run' => filter_var(getenv('SYNCSTATS_DRY_RUN') ?: false, FILTER_VALIDATE_BOOL),
            'league_names' => [],
        ],
    ];

    if (is_file($configFile)) {
        $fileConfig = require $configFile;
        if (!is_array($fileConfig)) {
            throw new RuntimeException("Config invalide: {$configFile}");
        }
        $config = array_replace_recursive($config, $fileConfig);
    }

    if (empty($config['mysql']['use_defenvvar'])) {
        throw new RuntimeException('mysql.use_defenvvar=true est requis.');
    }

    return $config;
}

function createStatsConnection(array $config): mysqli
{
    $defenvvarPath = (string)$config['mysql']['defenvvar_path'];
    if (!is_file($defenvvarPath)) {
        throw new RuntimeException("defenvvar.php introuvable: {$defenvvarPath}");
    }

    require_once $defenvvarPath;
    global $conn;

    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new RuntimeException('defenvvar.php doit initialiser $conn (mysqli).');
    }

    return $conn;
}

function hydrateDolibarrConfigFromEnv(array $config): array
{
    $config['dolibarr']['base_url'] = rtrim((string)$config['dolibarr']['base_url'], '/');
    $config['dolibarr']['api_key'] = (string)$config['dolibarr']['api_key'];
    $config['dolibarr']['page_size'] = max(1, (int)$config['dolibarr']['page_size']);

    if ($config['dolibarr']['base_url'] === '' || $config['dolibarr']['api_key'] === '') {
        throw new RuntimeException('Configuration Dolibarr incomplète (base_url/api_key).');
    }

    return $config;
}

function fetchAllThirdParties(DolibarrClient $client, int $pageSize): array
{
    $all = [];
    $page = 0;

    do {
        $data = $client->request('GET', '/thirdparties', [
            'sortfield' => 't.rowid',
            'sortorder' => 'ASC',
            'limit' => $pageSize,
            'page' => $page,
        ]);

        if (!is_array($data)) {
            throw new RuntimeException('Réponse /thirdparties inattendue.');
        }

        $chunk = array_values(array_filter($data, 'is_array'));
        $all = array_merge($all, $chunk);
        $page++;
    } while (count($chunk) === $pageSize);

    return $all;
}

function processTier(array $tier, mysqli $statsConn, DolibarrClient $dolibarr, string $periodeDebut, string $periodeFin, string $mode, array $config, bool $forceDryRun = false): void
{
    $tierId = (int)($tier['id'] ?? $tier['rowid'] ?? 0);
    $tierName = (string)($tier['name'] ?? $tier['nom'] ?? ('#' . $tierId));

    if ($tierId <= 0) {
        logInfo('SKIP tiers sans id.');
        return;
    }

    logInfo("Traitement tiers {$tierId} - {$tierName}");

    if (!isBillableCustomer($tier)) {
        logInfo('SKIP non-client.');
        return;
    }

    $arrayOptions = is_array($tier['array_options'] ?? null) ? $tier['array_options'] : [];
    logDebug('array_options keys: ' . implode(',', array_keys($arrayOptions)));
    $active = (string)($arrayOptions['options_facturation_syncstats_active'] ?? '0');

    if ($active !== '1') {
        logInfo('SKIP facturation SyncStats désactivée (options_facturation_syncstats_active != 1).');
        return;
    }

    $leagueCsv = (string)($arrayOptions['options_ligues_syncstats'] ?? '');
    $leagueIds = parseLeagueIds($leagueCsv);

    if ($leagueIds === []) {
        logInfo('SKIP pas de ligues SyncStats.');
        return;
    }

    $price = resolvePricePerMatch($arrayOptions, $config);
    if ($price === null) {
        logError('SKIP prix_match_ht absent/non numérique et aucun fallback configuré.');
        return;
    }

    logInfo('Ligues SyncStats: ' . implode(',', $leagueIds));

    $signature = buildSignature((string)$config['billing']['signature_prefix'], $periodeDebut, $periodeFin);
    logInfo('Signature: ' . $signature);

    try {
        $existing = findExistingInvoicesBySignature($dolibarr, $tierId, $signature);
    } catch (Throwable $e) {
        logError("Anti-doublon ERREUR (tiers {$tierId}): {$e->getMessage()}");
        return;
    }

    $hasDuplicate = false;
    if (count($existing) === 1) {
        $inv = $existing[0];
        $hasDuplicate = true;
        logInfo(sprintf(
            'Anti-doublon: trouvé (id facture %s, ref %s)',
            (string)($inv['id'] ?? $inv['rowid'] ?? '?'),
            (string)($inv['ref'] ?? 'n/a')
        ));
    } elseif (count($existing) > 1) {
        logError('ALERTE doublons détectés côté Dolibarr.');
        if (!$forceDryRun) {
            logError('SKIP pour ne pas aggraver.');
            return;
        }
        $hasDuplicate = true;
    } else {
        logInfo('Anti-doublon: non trouvé');
    }

    try {
        $matchesByLeague = countBillableMatchesByLeague($statsConn, $leagueIds, $periodeDebut, $periodeFin, $config['billing']['league_names']);
    } catch (Throwable $e) {
        logError("Erreur calcul matchs (tiers {$tierId}): {$e->getMessage()}");
        return;
    }

    $nbMatchsTotal = array_sum(array_column($matchesByLeague, 'matches'));
    if ($nbMatchsTotal <= 0) {
        logInfo('SKIP aucun match facturable pour ce tiers.');
        return;
    }

    $notePublic = "Facturation SyncStats du {$periodeDebut} au {$periodeFin}";

    $estimatedAmount = round($nbMatchsTotal * $price, 2);
    logInfo(sprintf('Nb matchs facturables: %d', $nbMatchsTotal));
    logInfo(sprintf('Montant estimé HT: %.2f', $estimatedAmount));

    $effectiveDryRun = $forceDryRun || !empty($config['billing']['dry_run']);
    if ($effectiveDryRun) {
        logInfo('[DRY RUN] Aucune création de facture (simulation uniquement).');
        return;
    }

    if ($hasDuplicate) {
        logInfo('Anti-doublon: trouvé => SKIP création');
        return;
    }

    logInfo('Anti-doublon: non trouvé => création...');

    try {
        $invoiceId = createInvoice($dolibarr, $tierId, $periodeFin, $signature, $notePublic);
        logInfo("Facture créée: {$invoiceId}");

        $description = buildInvoiceDescription($periodeDebut, $periodeFin, $matchesByLeague);
        addInvoiceLine($dolibarr, $invoiceId, $description, $nbMatchsTotal, $price);
        logInfo('Ligne ajoutée: OK');
    } catch (Throwable $e) {
        logError("Création facture/ligne ERREUR (tiers {$tierId}): {$e->getMessage()}");
        return;
    }

    if ($mode === 'valider') {
        try {
            validateInvoice($dolibarr, $invoiceId);
            logInfo('Validation: OK');
        } catch (Throwable $e) {
            logError('Validation: ERREUR - ' . $e->getMessage());
        }
    } else {
        logInfo('Validation: SKIP (mode brouillon, facture laissée non validée).');
    }
}

function isBillableCustomer(array $tier): bool
{
    $client = $tier['client'] ?? 0;
    return (int)$client > 0;
}

function parseLeagueIds(string $csv): array
{
    if (trim($csv) === '') {
        return [];
    }

    $ids = [];
    foreach (explode(',', $csv) as $value) {
        $value = trim($value);
        if ($value !== '' && ctype_digit($value)) {
            $ids[] = (int)$value;
        }
    }

    return array_values(array_unique($ids));
}

function resolvePricePerMatch(array $arrayOptions, array $config): ?float
{
    $raw = trim((string)($arrayOptions['options_prix_match_ht'] ?? ''));
    if ($raw !== '' && is_numeric($raw)) {
        return (float)$raw;
    }

    $fallback = $config['billing']['default_price_per_match'] ?? null;
    if ($fallback !== null && is_numeric((string)$fallback)) {
        return (float)$fallback;
    }

    return null;
}

function buildSignature(string $prefix, string $periodeDebut, string $periodeFin): string
{
    return sprintf('%s|%s|%s', $prefix, $periodeDebut, $periodeFin);
}

function findExistingInvoicesBySignature(DolibarrClient $dolibarr, int $tierId, string $signature): array
{
    $escapedSignature = str_replace("'", "''", $signature);
    $sqlfilters = sprintf("(fk_soc:=:%d) and (ref_client:=:'%s')", $tierId, $escapedSignature);

    $data = $dolibarr->request('GET', '/invoices', [
        'sqlfilters' => $sqlfilters,
        'sortfield' => 't.rowid',
        'sortorder' => 'DESC',
        'limit' => 100,
    ]);

    if (!is_array($data)) {
        return [];
    }

    return array_values(array_filter($data, 'is_array'));
}

function countBillableMatchesByLeague(mysqli $statsConn, array $leagueIds, string $periodeDebut, string $periodeFin, array $leagueNames): array
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

    $stmt = mysqli_prepare($statsConn, $sql);
    if ($stmt === false) {
        throw new RuntimeException('MySQL prepare failed: ' . mysqli_error($statsConn));
    }

    $params = array_merge($leagueIds, [$periodeDebut, $periodeFin]);
    $types = str_repeat('i', count($leagueIds)) . 'ss';
    bindParams($stmt, $types, $params);

    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('MySQL execute failed: ' . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        throw new RuntimeException('MySQL result retrieval failed: ' . mysqli_stmt_error($stmt));
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $leagueId = (int)$row['league_id'];
        $rows[] = [
            'league_id' => $leagueId,
            'league_name' => $leagueNames[$leagueId] ?? "Ligue {$leagueId}",
            'matches' => (int)$row['matches'],
        ];
    }

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return $rows;
}

function bindParams(mysqli_stmt $stmt, string $types, array $params): void
{
    $refs = [];
    foreach ($params as $k => $v) {
        $refs[$k] = &$params[$k];
    }

    array_unshift($refs, $types);
    if (!call_user_func_array([$stmt, 'bind_param'], $refs)) {
        throw new RuntimeException('MySQL bind_param failed: ' . mysqli_stmt_error($stmt));
    }
}

function createInvoice(DolibarrClient $dolibarr, int $tierId, string $periodeFin, string $signature, string $notePublic): int
{
    $payload = [
        'socid' => $tierId,
        'date' => $periodeFin,
        'ref_client' => $signature,
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

    throw new RuntimeException('Impossible de déterminer id_facture.');
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

function validateInvoice(DolibarrClient $dolibarr, int $invoiceId): void
{
    $dolibarr->request('POST', "/invoices/{$invoiceId}/validate");
}

function buildInvoiceDescription(string $periodeDebut, string $periodeFin, array $matchesByLeague): string
{
    $lines = [
        'Captation vidéo SyncStats',
        "Période : {$periodeDebut} → {$periodeFin}",
        '',
    ];

    foreach ($matchesByLeague as $item) {
        $lines[] = sprintf('%s (ID %d) : %d matchs', $item['league_name'], $item['league_id'], $item['matches']);
    }

    return implode("\n", $lines);
}


function parseBoolOption(array $options, string $key, bool $default): bool
{
    if (!array_key_exists($key, $options)) {
        return $default;
    }

    $raw = $options[$key];
    if ($raw === false || $raw === '') {
        return true;
    }

    $parsed = filter_var((string)$raw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    return $parsed ?? $default;
}

function setVerbose(bool $enabled): void
{
    $GLOBALS['SYNCSTATS_VERBOSE'] = $enabled;
}

function isVerbose(): bool
{
    return !empty($GLOBALS['SYNCSTATS_VERBOSE']);
}

function logDebug(string $message): void
{
    if (!isVerbose()) {
        return;
    }
    echo '[' . date('Y-m-d H:i:s') . "] DEBUG {$message}
";
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
