<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
    use SoftDeletes;

    protected $table = 'audit_logs';
}
