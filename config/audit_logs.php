<?php

declare(strict_types=1);

return [
    'enabled'   => env('AUDIT_LOGS_ENABLED', true),
    'exclude_fields' => [
        'password',
        'remember_token',
    ],
];