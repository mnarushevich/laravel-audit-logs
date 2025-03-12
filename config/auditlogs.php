<?php

declare(strict_types=1);

return [
    'enabled'   => env('AUDIT_LOGS_ENABLED', true),
    'log_table' => env('AUDIT_LOGS_TABLE', 'AuditLogs'),
    'log_driver' => env('AUDIT_LOGS_TABLE', 'AuditLogs'),
    'dynamodb' => [
        'endpoint' => env('DYNAMODB_ENDPOINT', 'http://localstack:4566'),
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'exclude_fields' => [
        'password',
        'remember_token',
    ],
];