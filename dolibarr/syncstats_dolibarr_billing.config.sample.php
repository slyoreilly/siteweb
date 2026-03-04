<?php

declare(strict_types=1);

return [
    'mysql' => [
        // Recommandé ici: réutiliser scriptsphp/defenvvar.php comme le reste du projet.
        'use_defenvvar' => true,
        'defenvvar_path' => __DIR__ . '/../scriptsphp/defenvvar.php',

        // Fallback si use_defenvvar=false:
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'syncstats',
        'username' => 'syncstats_user',
        'password' => 'change_me',
        'charset' => 'utf8mb4',
    ],
    'dolibarr' => [
        'base_url' => 'https://dolibarr.example.com',
        'api_key' => 'YOUR_DOLIBARR_API_KEY',
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
