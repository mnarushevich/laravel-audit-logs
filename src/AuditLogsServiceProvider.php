<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs;

use Illuminate\Support\ServiceProvider;

class AuditLogsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/audit_logs.php', 'audit_logs');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/audit_logs.php' => config_path('audit_logs.php'),
        ], 'config');
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'migrations');
    }
}