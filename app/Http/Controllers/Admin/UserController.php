<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = [
            ['id' => 1, 'name' => 'Admin Utama', 'email' => 'admin@example.com', 'role' => 'admin', 'phone' => '+628123456789'],
            ['id' => 2, 'name' => 'Petani A', 'email' => 'petani@example.com', 'role' => 'petani', 'phone' => '+628987654321'],
            ['id' => 3, 'name' => 'Petani B', 'email' => 'petani2@example.com', 'role' => 'petani', 'phone' => '+628555123456'],
            ['id' => 4, 'name' => 'Petani C', 'email' => 'petani3@example.com', 'role' => 'petani', 'phone' => '+628777888999'],
        ];

        return view('admin.users.index', compact('users'));
    }
}
