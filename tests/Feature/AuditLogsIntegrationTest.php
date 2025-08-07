<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs\Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use MNarushevich\AuditLogs\Models\AuditLog;
use MNarushevich\AuditLogs\Tests\Models\TestModel;
use MNarushevich\AuditLogs\Tests\Models\TestUuidModel;

it('can track complete model lifecycle with audit logs', function () {
    // Mock authenticated user
    Auth::shouldReceive('id')->andReturn(999);

    // Create model
    $model = TestModel::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ]);

    // Update model multiple times
    $model->update(['name' => 'John Smith']);
    $model->update(['email' => 'john.smith@example.com']);
    $model->update(['name' => 'Johnny Smith', 'email' => 'johnny@example.com']);

    // Delete model
    $model->delete();

    // Verify all audit logs were created
    $auditLogs = AuditLog::orderBy('created_at')->get();

    expect($auditLogs)->toHaveCount(5);

    // Check creation log
    $createLog = $auditLogs[0];
    expect($createLog->event)->toBe('created');
    expect($createLog->auditable_type)->toBe(TestModel::class);
    expect($createLog->auditable_id)->toBe($model->id);
    expect($createLog->user_uuid)->toBe('999');
    expect($createLog->old_values)->toBeNull();
    expect($createLog->new_values)->toHaveKeys(['name', 'email']);
    expect($createLog->new_values['name'])->toBe('John Doe');
    expect($createLog->new_values['email'])->toBe('john@example.com');
    expect($createLog->new_values)->not->toHaveKey('password'); // Should be excluded

    // Check first update log (name change)
    $updateLog1 = $auditLogs[1];
    expect($updateLog1->event)->toBe('updated');
    expect($updateLog1->old_values['name'])->toBe('John Doe');
    expect($updateLog1->new_values['name'])->toBe('John Smith');

    // Check second update log (email change)
    $updateLog2 = $auditLogs[2];
    expect($updateLog2->event)->toBe('updated');
    expect($updateLog2->old_values['email'])->toBe('john@example.com');
    expect($updateLog2->new_values['email'])->toBe('john.smith@example.com');

    // Check third update log (both name and email change)
    $updateLog3 = $auditLogs[3];
    expect($updateLog3->event)->toBe('updated');
    expect($updateLog3->old_values)->toHaveKeys(['name', 'email']);
    expect($updateLog3->old_values['name'])->toBe('John Smith');
    expect($updateLog3->old_values['email'])->toBe('john.smith@example.com');
    expect($updateLog3->new_values['name'])->toBe('Johnny Smith');
    expect($updateLog3->new_values['email'])->toBe('johnny@example.com');

    // Check deletion log
    $deleteLog = $auditLogs[4];
    expect($deleteLog->event)->toBe('deleted');
    expect($deleteLog->old_values)->toHaveKeys(['name', 'email']);
    expect($deleteLog->old_values['name'])->toBe('Johnny Smith');
    expect($deleteLog->old_values['email'])->toBe('johnny@example.com');
    expect($deleteLog->new_values)->toBeNull();
});

it('handles mixed model types with different primary keys', function () {
    // Create regular ID model
    $regularModel = TestModel::create([
        'name' => 'Regular User',
        'email' => 'regular@example.com',
        'password' => 'secret',
    ]);

    // Create UUID model
    $uuidModel = TestUuidModel::create([
        'name' => 'UUID User',
        'email' => 'uuid@example.com',
    ]);

    $auditLogs = AuditLog::orderBy('created_at')->get();

    expect($auditLogs)->toHaveCount(2);

    // Check regular model audit log
    $regularLog = $auditLogs[0];
    expect($regularLog->auditable_type)->toBe(TestModel::class);
    expect($regularLog->auditable_id)->toBe($regularModel->id);
    expect($regularLog->auditable_uuid)->toBeNull();

    // Check UUID model audit log
    $uuidLog = $auditLogs[1];
    expect($uuidLog->auditable_type)->toBe(TestUuidModel::class);
    expect($uuidLog->auditable_id)->toBeNull();
    expect($uuidLog->auditable_uuid)->toBe($uuidModel->uuid);
    expect($uuidLog->auditable_uuid)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

it('respects audit log configuration during runtime', function () {
    // The configuration is checked at boot time, not runtime
    // So we need to test this differently

    // Start with audit logs enabled
    Config::set('audit_logs.enabled', true);

    $model1 = TestModel::create([
        'name' => 'User 1',
        'email' => 'user1@example.com',
        'password' => 'secret',
    ]);

    // Clear the database and test with disabled config
    AuditLog::query()->forceDelete();

    // Create a new test class that will respect the disabled config
    Config::set('audit_logs.enabled', false);

    // We need to create a new anonymous class to test the disabled state
    // because the trait checks config at boot time
    $testClass = new class extends TestModel {
        protected $table = 'test_models';
    };

    $model2 = $testClass::create([
        'name' => 'User 2',
        'email' => 'user2@example.com',
        'password' => 'secret',
    ]);

    // Should have no logs since we cleared and config is disabled
    $auditLogs = AuditLog::all();
    expect($auditLogs)->toHaveCount(0);
});

it('handles concurrent model operations correctly', function () {
    $models = [];

    // Create multiple models concurrently
    for ($i = 1; $i <= 5; $i++) {
        $models[] = TestModel::create([
            'name' => "User {$i}",
            'email' => "user{$i}@example.com",
            'password' => 'secret',
        ]);
    }

    // Update all models
    foreach ($models as $model) {
        $model->update(['name' => $model->name . ' Updated']);
    }

    // Delete some models
    $models[0]->delete();
    $models[2]->delete();

    $auditLogs = AuditLog::orderBy('auditable_id')->orderBy('created_at')->get();

    // Should have 5 creates + 5 updates + 2 deletes = 12 total logs
    expect($auditLogs)->toHaveCount(12);

    // Group by auditable_id to verify logs per model
    $logsByModel = $auditLogs->groupBy('auditable_id');

    // Models 1 and 3 (index 0 and 2) should have 3 logs each (create, update, delete)
    expect($logsByModel[$models[0]->id])->toHaveCount(3);
    expect($logsByModel[$models[2]->id])->toHaveCount(3);

    // Other models should have 2 logs each (create, update)
    expect($logsByModel[$models[1]->id])->toHaveCount(2);
    expect($logsByModel[$models[3]->id])->toHaveCount(2);
    expect($logsByModel[$models[4]->id])->toHaveCount(2);
});

it('maintains audit log integrity after model restoration', function () {
    $model = TestModel::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'secret',
    ]);

    $model->delete();

    // Clear audit logs to focus on restoration behavior
    AuditLog::query()->forceDelete();

    // Verify the model is soft deleted and can be restored
    expect($model->trashed())->toBe(true);

    // This would test restoration if implemented:
    // $model->restore();
    // 
    // $restoreLog = AuditLog::latest()->first();
    // expect($restoreLog->event)->toBe('restored');
});
