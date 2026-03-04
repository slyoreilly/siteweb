<?php

declare(strict_types=1);

return [
    'mysql' => [
        // Recommandé: réutiliser scriptsphp/defenvvar.php comme le reste du projet.
        'use_defenvvar' => true,
        'defenvvar_path' => __DIR__ . '/../scriptsphp/defenvvar.php',
    ],
    'dolibarr' => [
        // Optionnel si déjà défini dans defenvvar.php via putenv('DOLIBARR_*').
        'base_url' => '',
        'api_key' => '',
        'page_size' => 100,
    ],
    'billing' => [
        'price_per_match' => 12.5,
        'dry_run' => true,
        // Optional map for friendly names in invoice line description.
        'league_names' => [
            12 => 'Ligue AAA',
            15 => 'Ligue BBB',
        ],
    ],
];
