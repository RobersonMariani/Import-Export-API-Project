<?php

declare(strict_types=1);

return [

    'import' => [
        'enabled' => env('FEATURE_IMPORT_ENABLED', true),
        'max_file_size' => env('IMPORT_MAX_FILE_SIZE', 52428800),
        'chunk_size' => env('IMPORT_CHUNK_SIZE', 1000),
        'max_concurrent' => env('IMPORT_MAX_CONCURRENT', 5),
    ],

    'export' => [
        'enabled' => env('FEATURE_EXPORT_ENABLED', true),
        'compression' => env('FEATURE_EXPORT_COMPRESSION', true),
        'url_expiry_minutes' => env('EXPORT_URL_EXPIRY_MINUTES', 60),
        'max_concurrent' => env('EXPORT_MAX_CONCURRENT', 3),
    ],

    'metrics' => [
        'enabled' => env('FEATURE_METRICS_ENABLED', true),
    ],

    'cqrs_cache' => [
        'enabled' => env('FEATURE_CQRS_CACHE_ENABLED', true),
        'ttl_seconds' => env('CQRS_CACHE_TTL', 30),
    ],

];
