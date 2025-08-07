<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs\Tests\Unit;

use Illuminate\Support\Facades\Config;
use MNarushevich\AuditLogs\AuditLogsServiceProvider;

it('registers the service provider', function () {
    expect($this->app->getProviders(AuditLogsServiceProvider::class))
        ->toHaveCount(1);
});

it('merges config from package config file', function () {
    expect(Config::get('audit_logs.enabled'))->toBe(true);
    expect(Config::get('audit_logs.exclude_fields'))->toEqual([
        'password',
        'remember_token',
    ]);
});

it('can override config values', function () {
    Config::set('audit_logs.enabled', false);
    expect(Config::get('audit_logs.enabled'))->toBe(false);
});

it('publishes config file', function () {
    $provider = new AuditLogsServiceProvider($this->app);
    $provider->boot();

    // Get published paths
    $publishedPaths = AuditLogsServiceProvider::pathsToPublish(AuditLogsServiceProvider::class, 'config');

    expect($publishedPaths)->not->toBeEmpty();
    expect(array_keys($publishedPaths)[0])->toEndWith('config/audit_logs.php');
    expect(array_values($publishedPaths)[0])->toEndWith('config/audit_logs.php');
});

it('publishes migration files', function () {
    $provider = new AuditLogsServiceProvider($this->app);
    $provider->boot();

    // Get published migration paths
    $publishedPaths = AuditLogsServiceProvider::pathsToPublish(AuditLogsServiceProvider::class, 'migrations');

    expect($publishedPaths)->not->toBeEmpty();
    expect(array_keys($publishedPaths)[0])->toEndWith('database/migrations');
    expect(array_values($publishedPaths)[0])->toEndWith('database/migrations');
});
