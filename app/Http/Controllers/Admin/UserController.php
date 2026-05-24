<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagementService,
    ) {}

    public function index(): View
    {
        return view('admin.users.index', [
            'users' => $this->userManagementService->paginateUsers(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userManagementService->createUser($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'user-created');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userManagementService->updateUser($user, $request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'user-updated');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->userManagementService->deleteUser($user, (int) auth()->id());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'user-deleted');
    }
}
