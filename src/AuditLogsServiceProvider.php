<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AuditLogsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/auditlogs.php', 'auditlogs');
        $this->mergeConfigFrom(__DIR__.'/../config/auditlogs.php', 'auditlogs');

        $this->app->singleton('auditlogs', function () {
            return new AuditLogs();
        });

        $this->app->singleton(DynamoDbClient::class,  function () {
            return new DynamoDbClient([
                'version' => 'latest',
                'region' => Config::get('auditlogs.dynamodb.region'),
                'credentials' => [
                    'key' => Config::get('auditlogs.dynamodb.key'),
                    'secret' => Config::get('auditlogs.dynamodb.secret'),
                ],
                'endpoint' => Config::get('auditlogs.dynamodb.endpoint'),
            ]);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/auditlogs.php' => config_path('auditlogs.php'),
        ]);
    }
}