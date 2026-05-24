<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\UsesMysqlTestDatabase;
use Tests\TestCase;

final class SprintTwoAuthUserManagementTest extends TestCase
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

    public function test_valid_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'admin-login@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_invalid_user_login_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'petani-login@example.com',
            'password' => 'password',
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'petani-login@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_create_user_from_management_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/users', [
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
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'role' => 'petani',
        ]);

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}", [
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

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete("/admin/users/{$admin->id}")
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }
}
