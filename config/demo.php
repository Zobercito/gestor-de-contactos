<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Demo pública
    |--------------------------------------------------------------------------
    |
    | La demo permite entrar con un clic usando cuentas semilla. No hay
    | registro público y la base de datos se restaura cada noche.
    |
    */

    'enabled' => env('DEMO_MODE', false),

    'password' => env('DEMO_PASSWORD', 'demo1234'),

    'accounts' => [
        'admin' => env('DEMO_ADMIN_EMAIL', 'admin@gestordecontactos.test'),
        'viewer' => env('DEMO_VIEWER_EMAIL', 'lector@gestordecontactos.test'),
        'editor' => env('DEMO_EDITOR_EMAIL', 'editor@gestordecontactos.test'),
    ],

    'reset_at' => env('DEMO_RESET_AT', '03:00'),

    'max_upload_kb' => (int) env('DEMO_MAX_UPLOAD_KB', 2048),

    'max_import_rows' => (int) env('DEMO_MAX_IMPORT_ROWS', 500),

    'banner' => 'Demo pública con datos ficticios. Se reinicia cada 24 horas.',
];
