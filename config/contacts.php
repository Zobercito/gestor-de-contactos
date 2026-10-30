<?php

return [
    'import' => [
        'queue_threshold' => (int) env('IMPORT_QUEUE_THRESHOLD', 5000),
        'max_rows' => (int) env('IMPORT_MAX_ROWS', 50000),
        'max_file_kb' => (int) env('IMPORT_MAX_FILE_KB', 10240),
        'preview_rows' => 12,
        'disk' => 'local',
        'directory' => 'imports',
    ],

    'export' => [
        'max_rows' => (int) env('EXPORT_MAX_ROWS', 20000),
    ],

    'sample_csv' => database_path('seeders/data/contactos-ejemplo.csv'),
];
