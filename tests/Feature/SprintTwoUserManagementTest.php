<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintTwoUserManagementTest extends TestCase
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

    public function test_admin_can_create_user_from_management_page(): void
    {
        $this->post('/admin/users', [
            'name' => 'Petani Baru',
            'email' => 'petani-managed@example.com',
            'phone_number' => '+628111111111',
            'role' => 'petani',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])
            ->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'email' => 'petani-managed@example.com',
            'role' => 'petani',
            'phone_number' => '+628111111111',
        ]);
    }

    public function test_admin_can_update_managed_user(): void
    {
        $user = User::factory()->create([
            'role' => 'petani',
        ]);

        $this->put("/admin/users/{$user->id}", [
            'name' => 'Petani Update',
            'email' => $user->email,
            'phone_number' => '+628222222222',
            'role' => 'admin',
            'password' => '',
            'password_confirmation' => '',
        ])
            ->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Petani Update',
            'role' => 'admin',
            'phone_number' => '+628222222222',
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create();

        $this->delete("/admin/users/{$user->id}")
            ->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
