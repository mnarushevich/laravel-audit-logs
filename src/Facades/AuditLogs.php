<?php

declare(strict_types=1);

namespace Mnarushevich\AuditLogs\Facades;

use Illuminate\Support\Facades\Facade;

class AuditLogs extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'auditlogs';
    }
}
