<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

trait UsesMysqlTestDatabase
{
    protected function useMysqlTestDatabase(): void
    {
        $env = parse_ini_file(base_path('.env'), false, INI_SCANNER_RAW);

        if (! extension_loaded('pdo_mysql')) {
            self::markTestSkipped('pdo_mysql extension not available for MySQL-backed tests.');
        }

        if (! is_array($env)) {
            self::fail('Unable to read database configuration from .env file.');
        }

        config()->set('database.default', 'mysql');
        config()->set('database.connections.mysql.url', $env['DB_URL'] ?? null);
        config()->set('database.connections.mysql.host', $env['DB_HOST'] ?? '127.0.0.1');
        config()->set('database.connections.mysql.port', $env['DB_PORT'] ?? '3306');
        config()->set('database.connections.mysql.database', $env['DB_DATABASE'] ?? 'laravel');
        config()->set('database.connections.mysql.username', $env['DB_USERNAME'] ?? 'root');
        config()->set('database.connections.mysql.password', $env['DB_PASSWORD'] ?? '');
        config()->set('database.connections.mysql.charset', 'utf8mb4');
        config()->set('database.connections.mysql.collation', 'utf8mb4_unicode_ci');

        DB::purge('mysql');
        DB::reconnect('mysql');
        DB::connection('mysql')->beginTransaction();
    }

    protected function rollbackMysqlTestDatabase(): void
    {
        if (DB::connection('mysql')->transactionLevel() > 0) {
            DB::connection('mysql')->rollBack();
        }
    }
}
