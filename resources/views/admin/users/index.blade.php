<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-xl font-extrabold">Manajemen Pengguna</div>
            <div class="text-[#999] text-sm">Kelola akun admin dan petani</div>
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="font-medium">{{ $user['name'] }}</td>
                            <td class="text-xs">{{ $user['email'] }}</td>
                            <td>
                                <span class="badge {{ $user['role'] === 'admin' ? 'badge-automatic' : 'badge-manual' }}">
                                    {{ ucfirst($user['role']) }}
                                </span>
                            </td>
                            <td class="text-xs">{{ $user['phone'] }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn-primary text-xs py-1 px-2" onclick="alert('Edit user (backend)')">Edit</button>
                                    <button class="btn-danger text-xs py-1 px-2" onclick="if(confirm('Hapus user ini?')) alert('Hapus (backend)')">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
