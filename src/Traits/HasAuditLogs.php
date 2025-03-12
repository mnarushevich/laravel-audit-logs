<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use MNarushevich\AuditLogs\Models\AuditLog;

trait HasAuditLogs
{
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Model $model) {
            self::logChange($model, 'created', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            self::logChange($model, 'updated', $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function (Model $user) {
            self::logChange($user, 'deleted', $user->getOriginal());
        });
    }

    protected static function logChange(
        Model $model,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        if ($oldValues) {
            $oldValues = self::getModelData($model, $oldValues);
        }
        if ($newValues) {
            $newValues = self::getModelData($model, $newValues);
        }

        $auditableId = $model->getKey();
        $auditableUUID = null;
        if ($model->getKeyName() === 'uuid') {
            $auditableUUID = $model->uuid ?? null;
            $auditableId = null;
        }

        AuditLog::create([
            'auditable_type' => get_class($model),
            'auditable_uuid' => $auditableUUID,
            'auditable_id'   => $auditableId,
            'user_uuid'      => Auth::id(),
            'event'          => $event,
            'old_values'     => $oldValues ?? null,
            'new_values'     => $newValues ?? null,
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
        ]);
    }

    private static function getModelData(Model $model, array $data): Collection
    {
        return collect($data)->except(array_merge(Config::get('auditlogs.exclude_fields'), $model->getHidden()));
    }
}
