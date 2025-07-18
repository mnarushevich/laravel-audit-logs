<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MNarushevich\AuditLogs\AuditLogsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'MNarushevich\\AuditLogs\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            AuditLogsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Set audit logs config
        config()->set('audit_logs.enabled', true);
        config()->set('audit_logs.exclude_fields', [
            'password',
            'remember_token',
        ]);
    }

    protected function setUpDatabase(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('auditable_uuid')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_uuid')->nullable();
            $table->string('event');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['auditable_type', 'auditable_uuid']);
        });

        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('test_uuid_models', function (Blueprint $table) {
            $table->string('uuid')->primary();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
    }
}
