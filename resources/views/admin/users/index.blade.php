<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Manajemen Pengguna</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Kelola akun admin dan petani</div>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="flex justify-end mb-4">
            <button class="btn-primary" onclick="alert('Tambah Pengguna (backend)')">+ Tambah Pengguna</button>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>No. WhatsApp</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="font-semibold">{{ $user['name'] }}</td>
                            <td class="text-xs text-[color:var(--color-text-muted)]">{{ $user['email'] }}</td>
                            <td>
                                <span class="badge {{ $user['role'] === 'admin' ? 'badge-automatic' : 'badge-manual' }}">
                                    {{ ucfirst($user['role']) }}
                                </span>
                            </td>
                            <td class="text-xs">{{ $user['phone'] }}</td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    <button class="btn-outline btn-sm" onclick="alert('Edit user (backend)')">Edit</button>
                                    <button class="btn-danger btn-sm" onclick="if(confirm('Hapus user ini?')) alert('Hapus (backend)')">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-pagination :currentPage="1" :lastPage="1" />
        </div>
    </div>
</x-app-layout>
