<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;

trait UsesMysqlTestDatabase
{
    private static bool $testDatabasePrepared = false;

    protected function useMysqlTestDatabase(): void
    {
        $env = parse_ini_file(base_path('.env'), false, INI_SCANNER_RAW);

        if (! extension_loaded('pdo_mysql')) {
            self::markTestSkipped('pdo_mysql extension not available for MySQL-backed tests.');
        }

        if (! is_array($env)) {
            self::fail('Unable to read database configuration from .env file.');
        }

        $database = (string) ($env['DB_DATABASE'] ?? 'laravel');
        $testDatabase = (string) ($env['DB_TEST_DATABASE'] ?? sprintf('%s_test', $database));
        $databaseUrl = isset($env['DB_URL']) && is_string($env['DB_URL']) ? $env['DB_URL'] : null;

        config()->set('database.default', 'mysql');
        config()->set('database.connections.mysql.url', $databaseUrl);
        config()->set('database.connections.mysql.host', $env['DB_HOST'] ?? '127.0.0.1');
        config()->set('database.connections.mysql.port', $env['DB_PORT'] ?? '3306');
        config()->set('database.connections.mysql.database', $database);
        config()->set('database.connections.mysql.username', $env['DB_USERNAME'] ?? 'root');
        config()->set('database.connections.mysql.password', $env['DB_PASSWORD'] ?? '');
        config()->set('database.connections.mysql.charset', 'utf8mb4');
        config()->set('database.connections.mysql.collation', 'utf8mb4_unicode_ci');

        DB::purge('mysql');
        DB::reconnect('mysql');
        DB::statement(sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            str_replace('`', '``', $testDatabase),
        ));

        config()->set(
            'database.connections.mysql.url',
            $databaseUrl !== null ? $this->replaceDatabaseInUrl($databaseUrl, $testDatabase) : null,
        );
        config()->set('database.connections.mysql.database', $testDatabase);

        DB::purge('mysql');
        DB::reconnect('mysql');

        if (! self::$testDatabasePrepared) {
            Artisan::call('migrate:fresh', [
                '--database' => 'mysql',
                '--force' => true,
            ]);

            self::$testDatabasePrepared = true;
        }

        DB::connection('mysql')->beginTransaction();
    }

    protected function rollbackMysqlTestDatabase(): void
    {
        if (DB::connection('mysql')->transactionLevel() > 0) {
            DB::connection('mysql')->rollBack();
        }
    }

    private function replaceDatabaseInUrl(string $databaseUrl, string $database): string
    {
        $parts = parse_url($databaseUrl);

        if ($parts === false) {
            throw new RuntimeException('Unable to parse DB_URL for test database setup.');
        }

        parse_str($parts['query'] ?? '', $query);

        $credentials = '';

        if (isset($parts['user'])) {
            $credentials = rawurlencode($parts['user']);

            if (isset($parts['pass'])) {
                $credentials .= ':'.rawurlencode($parts['pass']);
            }

            $credentials .= '@';
        }

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $queryString = $query === [] ? '' : '?'.http_build_query($query);

        return sprintf(
            '%s://%s%s%s/%s%s',
            $parts['scheme'] ?? 'mysql',
            $credentials,
            $parts['host'] ?? '127.0.0.1',
            $port,
            ltrim($database, '/'),
            $queryString,
        );
    }
}
