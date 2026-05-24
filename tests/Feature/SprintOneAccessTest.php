<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SprintOneAccessTest extends TestCase
{
    public function test_public_home_page_is_accessible(): void
    {
        $this->get('/')
            ->assertOk();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function test_admin_routes_forbid_petani_users(): void
    {
        $user = User::factory()->make([
            'role' => 'petani',
        ]);

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_routes_allow_admin_users(): void
    {
        $user = User::factory()->admin()->make();

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_registration_creates_petani_user(): void
    {
        $this->configureMysqlTestConnection();

        User::query()->where('email', 'petani-baru@example.com')->delete();

        $response = $this->post('/register', [
            'name' => 'Petani Baru',
            'email' => 'petani-baru@example.com',
            'phone_number' => '+628123456789',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'petani-baru@example.com',
            'role' => 'petani',
            'phone_number' => '+628123456789',
        ]);

        User::query()->where('email', 'petani-baru@example.com')->delete();
    }

    private function configureMysqlTestConnection(): void
    {
        if (! extension_loaded('pdo_mysql')) {
            self::markTestSkipped('pdo_mysql extension not available for registration test.');
        }

        $env = parse_ini_file(base_path('.env'), false, INI_SCANNER_RAW);

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
    }
}
