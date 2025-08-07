<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs\Tests\Unit\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use MNarushevich\AuditLogs\Models\AuditLog;

it('uses soft deletes', function () {
    $model = new AuditLog();
    expect(in_array(SoftDeletes::class, class_uses($model), true))->toBe(true);
});

it('has correct table name', function () {
    $model = new AuditLog();
    expect($model->getTable())->toBe('audit_logs');
});

it('has correct fillable attributes', function () {
    $model = new AuditLog();
    $expected = [
        'auditable_type',
        'auditable_id',
        'auditable_uuid',
        'user_id',
        'user_uuid',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    expect($model->getFillable())->toEqual($expected);
});

it('casts old_values and new_values to array', function () {
    $model = new AuditLog();
    $casts = $model->getCasts();

    expect($casts['old_values'])->toBe('array');
    expect($casts['new_values'])->toBe('array');
});

it('can create audit log with all attributes', function () {
    $auditLog = AuditLog::create([
        'auditable_type' => 'App\\Models\\User',
        'auditable_id' => 1,
        'auditable_uuid' => 'uuid-123',
        'user_id' => 1,
        'user_uuid' => 'user-uuid-123',
        'event' => 'created',
        'old_values' => ['name' => 'old name'],
        'new_values' => ['name' => 'new name'],
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Agent',
    ]);

    expect($auditLog)->toBeInstanceOf(AuditLog::class);
    expect($auditLog->auditable_type)->toBe('App\\Models\\User');
    expect($auditLog->auditable_id)->toBe(1);
    expect($auditLog->auditable_uuid)->toBe('uuid-123');
    expect($auditLog->user_id)->toBe(1);
    expect($auditLog->user_uuid)->toBe('user-uuid-123');
    expect($auditLog->event)->toBe('created');
    expect($auditLog->old_values)->toEqual(['name' => 'old name']);
    expect($auditLog->new_values)->toEqual(['name' => 'new name']);
    expect($auditLog->ip_address)->toBe('127.0.0.1');
    expect($auditLog->user_agent)->toBe('Test Agent');
});

it('can handle null values for optional fields', function () {
    $auditLog = AuditLog::create([
        'auditable_type' => 'App\\Models\\User',
        'event' => 'created',
    ]);

    expect($auditLog->auditable_id)->toBeNull();
    expect($auditLog->auditable_uuid)->toBeNull();
    expect($auditLog->user_id)->toBeNull();
    expect($auditLog->user_uuid)->toBeNull();
    expect($auditLog->old_values)->toBeNull();
    expect($auditLog->new_values)->toBeNull();
    expect($auditLog->ip_address)->toBeNull();
    expect($auditLog->user_agent)->toBeNull();
});

it('can soft delete audit log', function () {
    $auditLog = AuditLog::create([
        'auditable_type' => 'App\\Models\\User',
        'event' => 'created',
    ]);

    $auditLog->delete();

    expect($auditLog->trashed())->toBe(true);
    expect(AuditLog::withTrashed()->find($auditLog->id))->not->toBeNull();
    expect(AuditLog::find($auditLog->id))->toBeNull();
});

it('can restore soft deleted audit log', function () {
    $auditLog = AuditLog::create([
        'auditable_type' => 'App\\Models\\User',
        'event' => 'created',
    ]);

    $auditLog->delete();
    $auditLog->restore();

    expect($auditLog->trashed())->toBe(false);
    expect(AuditLog::find($auditLog->id))->not->toBeNull();
});
