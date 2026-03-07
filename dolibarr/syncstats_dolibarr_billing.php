#!/usr/bin/env php
<?php

declare(strict_types=1);


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
        // Compatibilite ancienne version
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
        logError('periode_debut doit etre <= periode_fin.');
        exit(1);
    }

    if (!in_array($mode, ['brouillon', 'valider'], true)) {
        logError('mode doit etre "brouillon" ou "valider".');
        exit(1);
    }

    $config = loadConfig();
    $statsConn = createStatsConnection();
    $config = hydrateDolibarrConfigFromEnv($config);
    $dolibarr = new DolibarrClient($config['dolibarr']['base_url'], $config['dolibarr']['api_key']);

    logInfo(sprintf('Parametres: periode=%s -> %s, mode=%s, limite_tiers=%s, tiers_id=%s, dry_run_force=%s',
        $periodeDebut,
        $periodeFin,
        $mode,
        $limiteTiers === null ? 'none' : (string)$limiteTiers,
        $tiersId === null ? 'none' : (string)$tiersId,
        $forceDryRun ? 'oui' : 'non'
    ));

    if ($mode === 'brouillon') {
        logInfo('Mode brouillon: les factures creees ne seront PAS validees.');
    }

    $tiers = fetchAllThirdParties($dolibarr, (int)$config['dolibarr']['page_size']);
    logDebug('Chargement tiers termine.');
    logInfo(sprintf('Tiers recuperes depuis Dolibarr: %d', count($tiers)));

    $processed = 0;
    foreach ($tiers as $tier) {
        $currentTierId = (int)($tier['id'] ?? $tier['rowid'] ?? 0);
        if ($tiersId !== null && $tiersId > 0 && $currentTierId !== $tiersId) {
            continue;
        }
        if ($limiteTiers !== null && $limiteTiers > 0 && $processed >= $limiteTiers) {
            logInfo("Limite atteinte ({$limiteTiers}), arret de la boucle.");
            break;
        }

        try {
            processTier($tier, $statsConn, $dolibarr, $periodeDebut, $periodeFin, $mode, $config, $forceDryRun);
        } catch (Throwable $e) {
            logError('Erreur non geree sur un tiers: ' . $e->getMessage());
        }

        $processed++;
    }

    logInfo('Execution terminee.');
}

function usage(string $scriptName): void
{
    fwrite(STDERR, "Usage: php {$scriptName} --periode_debut=YYYY-MM-DD --periode_fin=YYYY-MM-DD [--mode=brouillon|valider] [--limite_tiers=N] [--tiers_id=ID] [--dry_run=0|1] [--verbose=1]\n");
}

function loadConfig(): array
{
    $config = [
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

    return $config;
}

function createStatsConnection(): mysqli
{
    $defenvvarPath = __DIR__ . "/../scriptsphp/defenvvar.php";
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
    $baseUrl = trim((string)($config['dolibarr']['base_url'] ?? ''));
    $apiKey = trim((string)($config['dolibarr']['api_key'] ?? ''));
    $pageSize = (int)($config['dolibarr']['page_size'] ?? 0);

    // defenvvar.php peut definir ces variables via putenv().
    if ($baseUrl === '') {
        $envBaseUrl = getenv('DOLIBARR_BASE_URL');
        if ($envBaseUrl !== false) {
            $baseUrl = trim((string)$envBaseUrl);
        }
    }
    if ($apiKey === '') {
        $envApiKey = getenv('DOLIBARR_API_KEY');
        if ($envApiKey !== false) {
            $apiKey = trim((string)$envApiKey);
        }
    }
    if ($pageSize <= 0) {
        $envPageSize = getenv('DOLIBARR_PAGE_SIZE');
        if ($envPageSize !== false) {
            $pageSize = (int)$envPageSize;
        }
    }

    $config['dolibarr']['base_url'] = rtrim($baseUrl, '/');
    $config['dolibarr']['api_key'] = $apiKey;
    $config['dolibarr']['page_size'] = max(1, $pageSize);

    if ($config['dolibarr']['base_url'] === '' || $config['dolibarr']['api_key'] === '') {
        throw new RuntimeException('Configuration Dolibarr incomplete (base_url/api_key).');
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
            throw new RuntimeException('Reponse /thirdparties inattendue.');
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
    $productVideoId = isset($arrayOptions['options_produit_video_id'])
        ? (int)$arrayOptions['options_produit_video_id']
        : null;
    $productKitId = isset($arrayOptions['options_produit_kit_id'])
        ? (int)$arrayOptions['options_produit_kit_id']
        : null;
    if ($productVideoId !== null && $productVideoId <= 0) {
        $productVideoId = null;
    }
    if ($productKitId !== null && $productKitId <= 0) {
        $productKitId = null;
    }

    $nbKitsRaw = trim((string)($arrayOptions['options_nb_kits_syncstats'] ?? $arrayOptions['options_nb_kit_syncstats'] ?? ''));
    $prixKitSemaineRaw = str_replace(',', '.', trim((string)($arrayOptions['options_prix_kit_semaine_ht'] ?? $arrayOptions['options_prix_kits_semaine_ht'] ?? $arrayOptions['options_prix_kit_ht_semaine'] ?? '')));
    $nbKits = ($nbKitsRaw !== '' && is_numeric($nbKitsRaw)) ? (int)$nbKitsRaw : 0;
    $prixKitSemaine = ($prixKitSemaineRaw !== '' && is_numeric($prixKitSemaineRaw)) ? (float)$prixKitSemaineRaw : 0.0;
    $nbMois = countMonthsInPeriod($periodeDebut, $periodeFin);
    $nbSemaines = $nbMois * 4;
    $qtyKit = $nbKits * $nbSemaines;
    $descriptionKit = buildKitInvoiceDescription($periodeDebut, $periodeFin, $nbKits, $nbSemaines);
    $shouldAddKitLine = ($nbKits > 0 && $prixKitSemaine > 0);
    logInfo(sprintf(
        'Extrafields tiers: produit_video_id=%s, produit_kit_id=%s, nb_kits_raw="%s"=>%d, prix_kit_semaine_raw="%s"=>%.2f, nb_mois=%d, nb_semaines=%d, qty_kit=%d, should_add_kit=%s',
        $productVideoId === null ? 'null' : (string)$productVideoId,
        $productKitId === null ? 'null' : (string)$productKitId,
        $nbKitsRaw,
        $nbKits,
        $prixKitSemaineRaw,
        $prixKitSemaine,
        $nbMois,
        $nbSemaines,
        $qtyKit,
        $shouldAddKitLine ? 'yes' : 'no'
    ));
    logDebug('array_options keys: ' . implode(',', array_keys($arrayOptions)));
    $active = (string)($arrayOptions['options_facturation_syncstats_active'] ?? '0');

    if ($active !== '1') {
        logInfo('SKIP facturation SyncStats desactivee (options_facturation_syncstats_active != 1).');
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
        logError('SKIP prix_match_ht absent/non numerique et aucun fallback configure.');
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
            'Anti-doublon: trouve (id facture %s, ref %s)',
            (string)($inv['id'] ?? $inv['rowid'] ?? '?'),
            (string)($inv['ref'] ?? 'n/a')
        ));
    } elseif (count($existing) > 1) {
        logError('ALERTE doublons detectes cote Dolibarr.');
        if (!$forceDryRun) {
            logError('SKIP pour ne pas aggraver.');
            return;
        }
        $hasDuplicate = true;
    } else {
        logInfo('Anti-doublon: non trouve');
    }

    $productVideoVat = null;
    $productVideoLocalTax1 = null;
    $productVideoLocalTax2 = null;
    $productVideoLocalTax1Type = null;
    $productVideoLocalTax2Type = null;
    $productKitVat = null;
    $productKitLocalTax1 = null;
    $productKitLocalTax2 = null;
    $productKitLocalTax1Type = null;
    $productKitLocalTax2Type = null;

    if ($productVideoId !== null) {
        try {
            $videoProduct = fetchProductSummary($dolibarr, $productVideoId);
            $productVideoVat = is_numeric((string)($videoProduct['tva_tx'] ?? '')) ? (float)$videoProduct['tva_tx'] : null;
            $productVideoLocalTax1 = is_numeric((string)($videoProduct['localtax1_tx'] ?? '')) ? (float)$videoProduct['localtax1_tx'] : null;
            $productVideoLocalTax2 = is_numeric((string)($videoProduct['localtax2_tx'] ?? '')) ? (float)$videoProduct['localtax2_tx'] : null;
            $productVideoLocalTax1Type = isset($videoProduct['localtax1_type']) && is_numeric((string)$videoProduct['localtax1_type']) ? (int)$videoProduct['localtax1_type'] : null;
            $productVideoLocalTax2Type = isset($videoProduct['localtax2_type']) && is_numeric((string)$videoProduct['localtax2_type']) ? (int)$videoProduct['localtax2_type'] : null;
            logInfo(sprintf(
                'Produit video #%d: ref=%s, label=%s, tva_tx=%s, localtax1=%s(type=%s), localtax2=%s(type=%s)',
                $productVideoId,
                $videoProduct['ref'],
                $videoProduct['label'],
                $videoProduct['tva_tx'],
                $videoProduct['localtax1_tx'],
                $videoProduct['localtax1_type'] ?? 'null',
                $videoProduct['localtax2_tx'],
                $videoProduct['localtax2_type'] ?? 'null'
            ));
        } catch (Throwable $e) {
            logError('Lecture produit video impossible: ' . $e->getMessage());
        }
    }

    if ($productKitId !== null) {
        try {
            $kitProduct = fetchProductSummary($dolibarr, $productKitId);
            $productKitVat = is_numeric((string)($kitProduct['tva_tx'] ?? '')) ? (float)$kitProduct['tva_tx'] : null;
            $productKitLocalTax1 = is_numeric((string)($kitProduct['localtax1_tx'] ?? '')) ? (float)$kitProduct['localtax1_tx'] : null;
            $productKitLocalTax2 = is_numeric((string)($kitProduct['localtax2_tx'] ?? '')) ? (float)$kitProduct['localtax2_tx'] : null;
            $productKitLocalTax1Type = isset($kitProduct['localtax1_type']) && is_numeric((string)$kitProduct['localtax1_type']) ? (int)$kitProduct['localtax1_type'] : null;
            $productKitLocalTax2Type = isset($kitProduct['localtax2_type']) && is_numeric((string)$kitProduct['localtax2_type']) ? (int)$kitProduct['localtax2_type'] : null;
            logInfo(sprintf(
                'Produit kit #%d: ref=%s, label=%s, tva_tx=%s, localtax1=%s(type=%s), localtax2=%s(type=%s)',
                $productKitId,
                $kitProduct['ref'],
                $kitProduct['label'],
                $kitProduct['tva_tx'],
                $kitProduct['localtax1_tx'],
                $kitProduct['localtax1_type'] ?? 'null',
                $kitProduct['localtax2_tx'],
                $kitProduct['localtax2_type'] ?? 'null'
            ));
        } catch (Throwable $e) {
            logError('Lecture produit kit impossible: ' . $e->getMessage());
        }
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
    $estimatedKitAmount = ($shouldAddKitLine && $qtyKit > 0) ? round($qtyKit * $prixKitSemaine, 2) : 0.0;
    $estimatedTotalHt = round($estimatedAmount + $estimatedKitAmount, 2);
    logInfo(sprintf('Nb matchs facturables: %d', $nbMatchsTotal));
    logInfo(sprintf('Montant estime HT (video): %.2f', $estimatedAmount));
    logInfo(sprintf('Montant estime HT (kit): %.2f', $estimatedKitAmount));
    logInfo(sprintf('Montant estime HT (total): %.2f', $estimatedTotalHt));

    $effectiveDryRun = $forceDryRun || !empty($config['billing']['dry_run']);
    logInfo(sprintf('[SIMULATION] Ligne video prevue: product_id=%s, qty=%d, pu_ht=%.2f', $productVideoId === null ? 'null' : (string)$productVideoId, $nbMatchsTotal, $price));
    if ($shouldAddKitLine && $qtyKit > 0) {
        logInfo(sprintf('[SIMULATION] Ligne kit prevue: product_id=%s, qty=%d, pu_ht=%.2f', $productKitId === null ? 'null' : (string)$productKitId, $qtyKit, $prixKitSemaine));
    } else {
        logInfo(sprintf('[SIMULATION] Ligne kit non prevue: nb_kits=%d, prix_kit_semaine=%.2f, qty_kit=%d', $nbKits, $prixKitSemaine, $qtyKit));
    }

    if ($effectiveDryRun) {
        logInfo('[DRY RUN] Aucune creation de facture (simulation uniquement).');
        return;
    }

    if ($hasDuplicate) {
        logInfo(sprintf('[SIMULATION] Doublon detecte. Ligne video qui serait creee: product_id=%s, qty=%d, pu_ht=%.2f', $productVideoId === null ? 'null' : (string)$productVideoId, $nbMatchsTotal, $price));
        if ($shouldAddKitLine && $qtyKit > 0) {
            logInfo(sprintf('[SIMULATION] Doublon detecte. Ligne kit qui serait creee: product_id=%s, qty=%d, pu_ht=%.2f', $productKitId === null ? 'null' : (string)$productKitId, $qtyKit, $prixKitSemaine));
        } else {
            logInfo(sprintf('[SIMULATION] Doublon detecte. Ligne kit non prevue: nb_kits=%d, prix_kit_semaine=%.2f, qty_kit=%d', $nbKits, $prixKitSemaine, $qtyKit));
        }
        logInfo('Anti-doublon: trouve => SKIP creation');
        return;
    }

    logInfo('Anti-doublon: non trouve => creation...');

    try {
        $invoiceId = createInvoice($dolibarr, $tierId, $periodeFin, $signature, $notePublic);
        logInfo("Facture creee: {$invoiceId}");

        $description = buildInvoiceDescription($periodeDebut, $periodeFin, $matchesByLeague);
        if ($productVideoId === null) {
            logInfo('Aucun produit video configure pour ce tiers, creation de ligne sans fk_product.');
        }

        logInfo(sprintf('Creation ligne video: product_id=%s, qty=%d, pu_ht=%.2f', $productVideoId === null ? 'null' : (string)$productVideoId, $nbMatchsTotal, $price));
        addInvoiceLine($dolibarr, $invoiceId, $description, $nbMatchsTotal, $price, $productVideoId, $productVideoVat, $productVideoLocalTax1, $productVideoLocalTax2, $productVideoLocalTax1Type, $productVideoLocalTax2Type);

        if ($shouldAddKitLine && $qtyKit > 0) {
            if ($productKitId === null) {
                logInfo('Aucun produit kit configure pour ce tiers, creation de ligne sans fk_product.');
            }
            logInfo(sprintf('Creation ligne kit: product_id=%s, qty=%d, pu_ht=%.2f', $productKitId === null ? 'null' : (string)$productKitId, $qtyKit, $prixKitSemaine));
            addInvoiceLine($dolibarr, $invoiceId, $descriptionKit, $qtyKit, $prixKitSemaine, $productKitId, $productKitVat, $productKitLocalTax1, $productKitLocalTax2, $productKitLocalTax1Type, $productKitLocalTax2Type);
            logInfo(sprintf('Ligne kit ajoutee: %d kits x %d semaines (qty=%d) a %.2f/sem.', $nbKits, $nbSemaines, $qtyKit, $prixKitSemaine));
        } else {
            logInfo(sprintf(
                'Ligne kit non creee: nb_kits=%d, prix_kit_semaine=%.2f, qty_kit=%d, condition=%s',
                $nbKits,
                $prixKitSemaine,
                $qtyKit,
                ($shouldAddKitLine && $qtyKit > 0) ? 'ok' : 'ko'
            ));
        }

        logInfo('Ligne ajoutee: OK');
    } catch (Throwable $e) {
        logError("Creation facture/ligne ERREUR (tiers {$tierId}): {$e->getMessage()}");
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
        logInfo('Validation: SKIP (mode brouillon, facture laissee non validee).');
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
            l.Nom_Ligue AS nom_ligue,
            COUNT(DISTINCT tm.match_id) AS matches
        FROM TableMatch tm
        INNER JOIN Video v ON tm.match_id = v.nomMatch
        LEFT JOIN Ligue l ON l.ID_Ligue = tm.ligueRef
        WHERE tm.ligueRef IN ({$placeholders})
          AND tm.date >= ?
          AND tm.date < DATE_ADD(?, INTERVAL 1 DAY)
        GROUP BY tm.ligueRef, l.Nom_Ligue
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
        $leagueName = trim((string)($row['nom_ligue'] ?? ''));
        if ($leagueName === '') {
            $leagueName = $leagueNames[$leagueId] ?? "Ligue {$leagueId}";
        }

        $rows[] = [
            'league_id' => $leagueId,
            'league_name' => $leagueName,
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

    throw new RuntimeException('Impossible de determiner id_facture.');
}

function fetchProductSummary(DolibarrClient $dolibarr, int $productId): array
{
    $data = $dolibarr->request('GET', '/products/' . $productId);
    if (!is_array($data)) {
        throw new RuntimeException('Unexpected product response.');
    }

    $ref = (string)($data['ref'] ?? 'n/a');
    $label = (string)($data['label'] ?? $data['name'] ?? 'n/a');

    return [
        'ref' => $ref,
        'label' => $label,
        'tva_tx' => (string)($data['tva_tx'] ?? '0'),
        'localtax1_tx' => (string)($data['localtax1_tx'] ?? '0'),
        'localtax2_tx' => (string)($data['localtax2_tx'] ?? '0'),
        'localtax1_type' => isset($data['localtax1_type']) ? (string)$data['localtax1_type'] : null,
        'localtax2_type' => isset($data['localtax2_type']) ? (string)$data['localtax2_type'] : null,
    ];
}

function addInvoiceLine(
    DolibarrClient $dolibarr,
    int $invoiceId,
    string $description,
    int $qty,
    float $subprice,
    ?int $productId = null,
    ?float $productVat = null,
    ?float $productLocalTax1 = null,
    ?float $productLocalTax2 = null,
    ?int $productLocalTax1Type = null,
    ?int $productLocalTax2Type = null
): void {
    if ($productId !== null) {
        $payload = [
            'fk_product' => $productId,
            'qty' => $qty,
            'subprice' => $subprice,
            'desc' => $description,
        ];

        if ($productVat !== null) {
            $payload['tva_tx'] = $productVat;
        }
        if ($productLocalTax1 !== null) {
            $payload['localtax1_tx'] = $productLocalTax1;
        }
        if ($productLocalTax2 !== null) {
            $payload['localtax2_tx'] = $productLocalTax2;
        }
        if ($productLocalTax1Type !== null) {
            $payload['localtax1_type'] = $productLocalTax1Type;
        }
        if ($productLocalTax2Type !== null) {
            $payload['localtax2_type'] = $productLocalTax2Type;
        }
    } else {
        $payload = [
            'desc' => $description,
            'qty' => $qty,
            'subprice' => $subprice,
            'tva_tx' => 0,
        ];
    }

    $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
        $jsonFlags |= JSON_INVALID_UTF8_SUBSTITUTE;
    }
    $payloadDebug = json_encode($payload, $jsonFlags);
    logInfo(sprintf('Payload ligne facture (invoice=%d): %s', $invoiceId, $payloadDebug === false ? 'json_encode_error' : $payloadDebug));

    $dolibarr->request('POST', "/invoices/{$invoiceId}/lines", [], $payload);
}

function validateInvoice(DolibarrClient $dolibarr, int $invoiceId): void
{
    $dolibarr->request('POST', "/invoices/{$invoiceId}/validate");
}

function buildInvoiceDescription(string $periodeDebut, string $periodeFin, array $matchesByLeague): string
{
    $lines = [
        'Captation video SyncStats',
        "Periode : {$periodeDebut} -> {$periodeFin}",
        '',
    ];

    foreach ($matchesByLeague as $item) {
        $lines[] = sprintf('%s : %d matchs', $item['league_name'], $item['matches']);
    }

    return implode("\n", $lines);
}
function countMonthsInPeriod(string $periodeDebut, string $periodeFin): int
{
    $start = DateTimeImmutable::createFromFormat('Y-m-d', $periodeDebut);
    $end = DateTimeImmutable::createFromFormat('Y-m-d', $periodeFin);

    if (!$start || !$end) {
        return 1;
    }

    $months = ((int)$end->format('Y') * 12 + (int)$end->format('n'))
        - ((int)$start->format('Y') * 12 + (int)$start->format('n'))
        + 1;

    return max(1, $months);
}

function buildKitInvoiceDescription(string $periodeDebut, string $periodeFin, int $nbKits, int $nbSemaines): string
{
    return implode("\n", [
        'Location kits SyncStats',
        "Periode : {$periodeDebut} -> {$periodeFin}",
        '',
        sprintf('%d kits x %d semaines', $nbKits, $nbSemaines),
    ]);
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
            $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
            if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
                $jsonFlags |= JSON_INVALID_UTF8_SUBSTITUTE;
            }
            $body = json_encode($payload, $jsonFlags);
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





