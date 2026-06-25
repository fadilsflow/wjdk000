<?php

declare(strict_types=1);

namespace Tests\Feature;

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

    public function test_dashboard_is_accessible_without_login(): void
    {
        $this->get('/dashboard')
            ->assertOk();
    }

    public function test_admin_routes_are_accessible_without_login(): void
    {
        $this->get('/admin/users')
            ->assertOk();

        $this->get('/admin/devices')
            ->assertOk();
    }

    public function test_login_route_is_not_available(): void
    {
        $this->get('/login')
            ->assertNotFound();
    }
}
