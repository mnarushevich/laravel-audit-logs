<?php

declare(strict_types=1);

return [
    'enabled'   => env('AUDIT_LOGS_ENABLED', true),
    'log_table' => env('AUDIT_LOGS_TABLE', 'AuditLogs'),
];