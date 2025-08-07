<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use MNarushevich\AuditLogs\Traits\HasAuditLogs;

class TestUuidModel extends Model
{
    use HasAuditLogs;

    protected $table = 'test_uuid_models';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $primaryKey = 'uuid';

    protected $fillable = [
        'uuid',
        'name',
        'email',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }
}
