<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class UserManagementService
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    public function paginateUsers(int $perPage = 10): LengthAwarePaginator
    {
        return $this->userRepository->paginateForAdmin($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            return $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'role' => $data['role'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'role' => $data['role'],
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            return $this->userRepository->update($user, $payload);
        });
    }

    public function deleteUser(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $this->userRepository->delete($user);
        });
    }
}
