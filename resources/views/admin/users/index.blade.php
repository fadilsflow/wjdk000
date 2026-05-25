<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Manajemen Pengguna</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Kelola akun admin dan petani</div>
        </div>
    </x-slot>

    <div
        x-data="{
            showCreate: {{ $errors->any() && old('_token') ? 'true' : 'false' }},
            showEdit: false,
            showDelete: false,
            editUser: { id: null, name: '', email: '', phone_number: '', role: 'petani' },
            deleteUser: { id: null, name: '' },
            openEdit(user) { this.editUser = { ...user }; this.showEdit = true; },
            openDelete(user) { this.deleteUser = { ...user }; this.showDelete = true; }
        }"
        @keydown.escape.window="showCreate = false; showEdit = false; showDelete = false"
    >

    <div class="p-6 space-y-6">

        <div class="flex items-center justify-between">
            <div class="text-sm text-[color:var(--color-text-muted)]">Total {{ $users->total() }} pengguna</div>
            <button @click="showCreate = true" class="btn-primary">+ Tambah Pengguna</button>
        </div>

        {{-- User Table --}}
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
                        @forelse($users as $user)
                            <tr>
                                <td class="font-semibold">{{ $user->name }}</td>
                                <td class="text-xs text-[color:var(--color-text-muted)]">{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->role === 'admin' ? 'badge-automatic' : 'badge-manual' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="text-xs">{{ $user->phone_number ?: '-' }}</td>
                                <td class="text-right space-x-1">
                                    <button
                                        class="btn-outline btn-sm"
                                        @click="openEdit({
                                            id: {{ $user->id }},
                                            name: {{ Js::from($user->name) }},
                                            email: {{ Js::from($user->email) }},
                                            phone_number: {{ Js::from($user->phone_number ?? '') }},
                                            role: {{ Js::from($user->role) }}
                                        })"
                                    >Edit</button>
                                    <button
                                        class="btn-danger btn-sm"
                                        @click="openDelete({ id: {{ $user->id }}, name: {{ Js::from($user->name) }} })"
                                    >Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-sm text-[color:var(--color-text-muted)] py-6">Belum ada pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 flex items-center justify-between border-t border-[color:var(--color-bg-elevated)] text-xs text-[color:var(--color-text-muted)]">
                <span>Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }}</span>
                <div class="flex gap-2">
                    @if ($users->onFirstPage())
                        <span class="btn-outline btn-sm opacity-50 pointer-events-none">Sebelumnya</span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="btn-outline btn-sm">Sebelumnya</a>
                    @endif
                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="btn-outline btn-sm">Selanjutnya</a>
                    @else
                        <span class="btn-outline btn-sm opacity-50 pointer-events-none">Selanjutnya</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div
        x-show="showCreate"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showCreate = false"></div>
        <div
            x-show="showCreate"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative z-10 w-full max-w-md bg-[color:var(--color-bg-card-2)] rounded-2xl shadow-dialog border border-[color:var(--color-bg-elevated)] p-6 max-h-[90vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between mb-5">
                <div class="text-lg font-extrabold text-[color:var(--color-text)]">Tambah Pengguna</div>
                <button @click="showCreate = false" class="text-[color:var(--color-text-muted)] hover:text-[color:var(--color-text)] transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4"
                  x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="form-label">Nama</label>
                        <input name="name" type="text" class="form-input" value="{{ old('name') }}" required autofocus>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input name="email" type="email" class="form-input" value="{{ old('email') }}" required>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <label class="form-label">No. WhatsApp</label>
                        <input name="phone_number" type="text" class="form-input" value="{{ old('phone_number') }}">
                        <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                    </div>
                    <div>
                        <label class="form-label">Role</label>
                        <select name="role" class="form-input" required>
                            <option value="petani" @selected(old('role', 'petani') === 'petani')>Petani</option>
                            <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>
                    <div>
                        <label class="form-label">Password</label>
                        <input name="password" type="password" class="form-input" required>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <label class="form-label">Konfirmasi Password</label>
                        <input name="password_confirmation" type="password" class="form-input" required>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreate = false" class="btn-outline">Batal</button>
                    <button type="submit" class="btn-primary" :disabled="loading">
                        <span x-show="!loading">Simpan Pengguna</span>
                        <span x-show="loading" x-cloak>Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div
        x-show="showEdit"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showEdit = false"></div>
        <div
            x-show="showEdit"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative z-10 w-full max-w-md bg-[color:var(--color-bg-card-2)] rounded-2xl shadow-dialog border border-[color:var(--color-bg-elevated)] p-6"
        >
            <div class="flex items-center justify-between mb-5">
                <div class="text-lg font-extrabold text-[color:var(--color-text)]">Edit Pengguna</div>
                <button @click="showEdit = false" class="text-[color:var(--color-text-muted)] hover:text-[color:var(--color-text)] transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form
                method="POST"
                :action="`{{ url('admin/users') }}/${editUser.id}`"
                class="space-y-4"
                x-data="{ loading: false }" @submit="loading = true"
            >
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label">Nama</label>
                    <input name="name" type="text" class="form-input" :value="editUser.name" required>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input name="email" type="email" class="form-input" :value="editUser.email" required>
                </div>
                <div>
                    <label class="form-label">No. WhatsApp</label>
                    <input name="phone_number" type="text" class="form-input" :value="editUser.phone_number">
                </div>
                <div>
                    <label class="form-label">Role</label>
                    <select name="role" class="form-input" required>
                        <option value="petani" :selected="editUser.role === 'petani'">Petani</option>
                        <option value="admin" :selected="editUser.role === 'admin'">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Password Baru <span class="text-[color:var(--color-text-muted)] font-normal text-xs">(kosongkan jika tidak diubah)</span></label>
                    <input name="password" type="password" class="form-input">
                </div>
                <div>
                    <label class="form-label">Konfirmasi Password</label>
                    <input name="password_confirmation" type="password" class="form-input">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showEdit = false" class="btn-outline">Batal</button>
                    <button type="submit" class="btn-primary" :disabled="loading">
                        <span x-show="!loading">Simpan</span>
                        <span x-show="loading" x-cloak>Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div
        x-show="showDelete"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDelete = false"></div>
        <div
            x-show="showDelete"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative z-10 w-full max-w-sm bg-[color:var(--color-bg-card-2)] rounded-2xl shadow-dialog border border-[color:var(--color-bg-elevated)] p-6 text-center"
        >
            <div class="w-12 h-12 rounded-full bg-[rgba(243,114,127,0.15)] grid place-items-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--color-negative)">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div class="text-lg font-extrabold text-[color:var(--color-text)] mb-1">Hapus Pengguna?</div>
            <p class="text-sm text-[color:var(--color-text-muted)] mb-6">
                Akun <span class="font-bold text-[color:var(--color-text)]" x-text="deleteUser.name"></span> akan dihapus permanen dan tidak bisa dikembalikan.
            </p>
            <form
                method="POST"
                :action="`{{ url('admin/users') }}/${deleteUser.id}`"
                x-data="{ loading: false }" @submit="loading = true"
                class="flex gap-3"
            >
                @csrf
                @method('DELETE')
                <button type="button" @click="showDelete = false" class="btn-outline flex-1">Batal</button>
                <button type="submit" class="btn-danger flex-1" :disabled="loading">
                    <span x-show="!loading">Hapus</span>
                    <span x-show="loading" x-cloak>Menghapus…</span>
                </button>
            </form>
        </div>
    </div>

    </div>{{-- end x-data wrapper --}}
</x-app-layout>
