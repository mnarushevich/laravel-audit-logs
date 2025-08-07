<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MNarushevich\AuditLogs\Traits\HasAuditLogs;

class TestModel extends Model
{
    use HasAuditLogs, SoftDeletes;

    protected $table = 'test_models';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
