<?php

declare(strict_types=1);

return [
    'mysql' => [
        'use_defenvvar' => true,
        'defenvvar_path' => __DIR__ . '/../scriptsphp/defenvvar.php',
    ],
    'dolibarr' => [
        // Peut aussi venir de DOLIBARR_BASE_URL / DOLIBARR_API_KEY.
        'base_url' => 'https://dolibarr.sm.syncstats.ca',
        'api_key' => 'CHANGE_DOLIBARR_API_KEY',
        'page_size' => 100,
    ],
    'billing' => [
        'signature_prefix' => 'SYNCSTATS|FACTURATION',
        'default_price_per_match' => 12.5,
        'dry_run' => true,
        'league_names' => [
            12 => 'Ligue AAA',
            15 => 'Ligue BBB',
        ],
    ],
];
