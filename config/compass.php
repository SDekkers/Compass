<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | API Title & Version
    |--------------------------------------------------------------------------
    */
    'title' => env('COMPASS_TITLE', config('app.name', 'API') . ' Documentation'),
    'version' => env('COMPASS_VERSION', '1.0.0'),
    'description' => env('COMPASS_DESCRIPTION', ''),

    /*
    |--------------------------------------------------------------------------
    | Output
    |--------------------------------------------------------------------------
    */
    'output' => [
        'path' => storage_path('app/compass'),
        'yaml' => true,
        'json' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Servers
    |--------------------------------------------------------------------------
    */
    'servers' => [
        [
            'url' => env('APP_URL', 'http://localhost'),
            'description' => 'Default',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Filtering
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'prefixes' => ['api'],
        'exclude' => [],
        'exclude_patterns' => [
            'telescope/*',
            'horizon/*',
            '_debugbar/*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Grouping
    |--------------------------------------------------------------------------
    | Auto-detects groups from controller namespace.
    | Pattern: App\Modules\{Group}\Controllers\{Subgroup}\Controller
    */
    'grouping' => [
        'enabled' => true,
        'pattern' => 'App\\Modules\\{group}\\',
        'overrides' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Swagger UI
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'enabled' => env('COMPASS_UI_ENABLED', true),
        'path' => 'docs',
        'middleware' => [],
    ],
];
