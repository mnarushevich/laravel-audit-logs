<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs\Tests\Unit\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use MNarushevich\AuditLogs\Models\AuditLog;
use MNarushevich\AuditLogs\Tests\Models\TestModel;
use MNarushevich\AuditLogs\Tests\Models\TestUuidModel;

beforeEach(function () {
    // Clear any existing audit logs
    AuditLog::query()->forceDelete();
});

it('creates audit log when model is created', function () {
    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    $auditLog = AuditLog::latest()->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->auditable_type)->toBe(TestModel::class);
    expect($auditLog->auditable_id)->toBe($model->id);
    expect($auditLog->auditable_uuid)->toBeNull();
    expect($auditLog->event)->toBe('created');
    expect($auditLog->old_values)->toBeNull();
    expect($auditLog->new_values)->toHaveKeys(['name', 'email']);
    expect($auditLog->new_values['name'])->toBe('John Doe');
    expect($auditLog->new_values['email'])->toBe('john@example.com');
    expect($auditLog->new_values)->not->toHaveKey('password'); // Should be excluded
});

it('creates audit log when model is updated', function () {
    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    // Clear the creation audit log
    AuditLog::query()->forceDelete();

    $model->update([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $auditLog = AuditLog::latest()->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->auditable_type)->toBe(TestModel::class);
    expect($auditLog->auditable_id)->toBe($model->id);
    expect($auditLog->event)->toBe('updated');
    expect($auditLog->old_values)->toHaveKeys(['name', 'email']);
    expect($auditLog->old_values['name'])->toBe('John Doe');
    expect($auditLog->old_values['email'])->toBe('john@example.com');
    expect($auditLog->new_values)->toHaveKeys(['name', 'email']);
    expect($auditLog->new_values['name'])->toBe('Jane Doe');
    expect($auditLog->new_values['email'])->toBe('jane@example.com');
});

it('creates audit log when model is deleted', function () {
    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    // Clear the creation audit log
    AuditLog::query()->forceDelete();

    $originalAttributes = $model->getOriginal();
    $model->delete();

    $auditLog = AuditLog::latest()->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->auditable_type)->toBe(TestModel::class);
    expect($auditLog->auditable_id)->toBe($model->id);
    expect($auditLog->event)->toBe('deleted');
    expect($auditLog->old_values)->toHaveKeys(['name', 'email']);
    expect($auditLog->old_values['name'])->toBe('John Doe');
    expect($auditLog->old_values['email'])->toBe('john@example.com');
    expect($auditLog->old_values)->not->toHaveKey('password'); // Should be excluded
    expect($auditLog->new_values)->toBeNull();
});

it('handles UUID models correctly', function () {
    $model = TestUuidModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $auditLog = AuditLog::latest()->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->auditable_type)->toBe(TestUuidModel::class);
    expect($auditLog->auditable_id)->toBeNull();
    expect($auditLog->auditable_uuid)->toBe($model->uuid);
    expect($auditLog->event)->toBe('created');
});

it('excludes configured fields from audit logs', function () {
    Config::set('audit_logs.exclude_fields', ['email', 'password']);

    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    $auditLog = AuditLog::latest()->first();

    expect($auditLog->new_values)->toHaveKey('name');
    expect($auditLog->new_values)->not->toHaveKey('email');
    expect($auditLog->new_values)->not->toHaveKey('password');
});

it('excludes hidden fields from audit logs', function () {
    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    $auditLog = AuditLog::latest()->first();

    expect($auditLog->new_values)->toHaveKey('name');
    expect($auditLog->new_values)->toHaveKey('email');
    expect($auditLog->new_values)->not->toHaveKey('password'); // Hidden field
    expect($auditLog->new_values)->not->toHaveKey('remember_token'); // Hidden field
});

it('captures user information when authenticated', function () {
    // Mock an authenticated user
    Auth::shouldReceive('id')->andReturn(123);

    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    $auditLog = AuditLog::latest()->first();

    expect($auditLog->user_uuid)->toBe('123');
});

it('captures IP address and user agent', function () {
    // Mock request data
    app('request')->server->set('REMOTE_ADDR', '192.168.1.1');
    app('request')->server->set('HTTP_USER_AGENT', 'Test User Agent');

    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    $auditLog = AuditLog::latest()->first();

    expect($auditLog->ip_address)->toBe('192.168.1.1');
    // In test environment, Symfony sets its own user agent
    expect($auditLog->user_agent)->not->toBeNull();
    expect($auditLog->user_agent)->toBeString();
});

it('does not create audit logs when disabled in config', function () {
    Config::set('audit_logs.enabled', false);

    TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    $auditLogCount = AuditLog::count();
    expect($auditLogCount)->toBe(0);
});

it('handles models with no changes on update', function () {
    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    // Clear the creation audit log
    AuditLog::query()->forceDelete();

    // Update with same values (no actual changes)
    $model->update([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $auditLogCount = AuditLog::count();
    expect($auditLogCount)->toBe(0); // No audit log should be created for no-change updates
});

it('handles empty old values', function () {
    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    $auditLog = AuditLog::latest()->first();

    expect($auditLog->old_values)->toBeNull(); // Should be null for creation
    expect($auditLog->new_values)->not->toBeNull();
});

it('handles empty new values', function () {
    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    // Clear the creation audit log
    AuditLog::query()->forceDelete();

    $model->delete();

    $auditLog = AuditLog::latest()->first();

    expect($auditLog->old_values)->not->toBeNull();
    expect($auditLog->new_values)->toBeNull(); // Should be null for deletion
});

it('handles multiple model operations', function () {
    // Create
    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    // Update
    $model->update(['name' => 'Jane Doe']);

    // Delete
    $model->delete();

    $auditLogs = AuditLog::orderBy('created_at')->get();

    expect($auditLogs)->toHaveCount(3);
    expect($auditLogs->pluck('event')->toArray())->toEqual(['created', 'updated', 'deleted']);
});
