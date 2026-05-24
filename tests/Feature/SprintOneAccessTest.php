<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintOneAccessTest extends TestCase
{
    use UsesMysqlTestDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useMysqlTestDatabase();
    }

    protected function tearDown(): void
    {
        $this->rollbackMysqlTestDatabase();
        parent::tearDown();
    }

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
        $user = User::factory()->create([
            'role' => 'petani',
        ]);

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_routes_allow_admin_users(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_register_route_is_not_available_to_public_users(): void
    {
        $this->get('/register')
            ->assertNotFound();
    }
}
