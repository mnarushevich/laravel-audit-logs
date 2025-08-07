# Laravel Audit Logs - Tests

This directory contains comprehensive tests for the Laravel Audit Logs package using the Pest testing framework.

## Test Structure

```
tests/
├── Pest.php                           # Pest configuration
├── TestCase.php                       # Base test case
├── Models/                            # Test models
│   ├── TestModel.php                  # Standard ID model for testing
│   └── TestUuidModel.php              # UUID model for testing
└── Unit/                              # Unit tests
    ├── AuditLogsServiceProviderTest.php
    ├── Models/
    │   └── AuditLogTest.php
    └── Traits/
        └── HasAuditLogsTest.php

```

## Running Tests

### Prerequisites

Install the testing dependencies:

```bash
composer install
```

### Run All Tests

```bash
composer test
```

Or run Pest directly:

```bash
vendor/bin/pest
```

### Run Specific Test Files

```bash
vendor/bin/pest tests/Unit/AuditLogsServiceProviderTest.php
vendor/bin/pest tests/Unit/Models/AuditLogTest.php
vendor/bin/pest tests/Unit/Traits/HasAuditLogsTest.php
```

### Run Tests with Coverage

```bash
vendor/bin/pest --coverage
```

### Run Tests in Parallel

```bash
vendor/bin/pest --parallel
```

## Test Coverage

The tests cover:

### Service Provider (`AuditLogsServiceProviderTest.php`)

- Service provider registration
- Config merging
- Publishing config and migration files

### AuditLog Model (`AuditLogTest.php`)

- Model attributes and fillable fields
- Soft deletes functionality
- Array casting for old_values and new_values
- Creating and managing audit logs

### HasAuditLogs Trait (`HasAuditLogsTest.php`)

- Model creation auditing
- Model update auditing
- Model deletion auditing
- UUID model support
- Field exclusion (hidden and configured fields)
- User authentication capture
- IP address and user agent capture
- Configuration-based enabling/disabling
- Edge cases and error handling

## Test Models

### TestModel

A standard Eloquent model with integer ID that uses the `HasAuditLogs` trait.

### TestUuidModel

An Eloquent model with UUID primary key that uses the `HasAuditLogs` trait to test UUID functionality.

## Database Setup

Tests use an in-memory SQLite database that is recreated for each test. The required tables are created automatically in the `TestCase::setUpDatabase()` method.
