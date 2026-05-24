<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Manajemen Pengguna</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Kelola akun admin dan petani</div>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">
        @if (session('status') === 'user-created')
            <div class="card p-4 text-sm text-[color:var(--color-brand)]">Pengguna baru berhasil dibuat.</div>
        @endif

        @if (session('status') === 'user-updated')
            <div class="card p-4 text-sm text-[color:var(--color-brand)]">Data pengguna berhasil diperbarui.</div>
        @endif

        @if (session('status') === 'user-deleted')
            <div class="card p-4 text-sm text-[color:var(--color-brand)]">Pengguna berhasil dihapus.</div>
        @endif

        @if ($errors->has('user'))
            <div class="card p-4 text-sm text-[color:var(--color-negative)]">{{ $errors->first('user') }}</div>
        @endif

        <div class="card">
            <div class="card-header">Tambah Pengguna</div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf

                <div>
                    <label class="form-label" for="name">Nama</label>
                    <input id="name" name="name" type="text" class="form-input" value="{{ old('name') }}" required>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <label class="form-label" for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-input" value="{{ old('email') }}" required>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label class="form-label" for="phone_number">No. WhatsApp</label>
                    <input id="phone_number" name="phone_number" type="text" class="form-input" value="{{ old('phone_number') }}">
                    <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                </div>

                <div>
                    <label class="form-label" for="role">Role</label>
                    <select id="role" name="role" class="form-input" required>
                        <option value="petani" @selected(old('role', 'petani') === 'petani')>Petani</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div>
                    <label class="form-label" for="password">Password</label>
                    <input id="password" name="password" type="password" class="form-input" required>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-input" required>
                </div>

                <div class="md:col-span-2 flex justify-end">
                    <button class="btn-primary" type="submit">Simpan Pengguna</button>
                </div>
            </form>
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
                                <td class="text-right">
                                    @php($editFormId = 'edit-user-'.$user->id)
                                    <details class="inline-block text-left">
                                        <summary class="btn-outline btn-sm cursor-pointer list-none inline-flex">Edit</summary>
                                        <div class="mt-3 w-[22rem] max-w-[80vw] rounded-2xl border border-[color:var(--color-bg-elevated)] bg-[color:var(--color-bg-card-2)] p-4 shadow-dialog">
                                            <form id="{{ $editFormId }}" method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-3">
                                                @csrf
                                                @method('PUT')

                                                <div>
                                                    <label class="form-label">Nama</label>
                                                    <input name="name" type="text" class="form-input" value="{{ $user->name }}" required>
                                                </div>

                                                <div>
                                                    <label class="form-label">Email</label>
                                                    <input name="email" type="email" class="form-input" value="{{ $user->email }}" required>
                                                </div>

                                                <div>
                                                    <label class="form-label">No. WhatsApp</label>
                                                    <input name="phone_number" type="text" class="form-input" value="{{ $user->phone_number }}">
                                                </div>

                                                <div>
                                                    <label class="form-label">Role</label>
                                                    <select name="role" class="form-input" required>
                                                        <option value="petani" @selected($user->role === 'petani')>Petani</option>
                                                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="form-label">Password Baru</label>
                                                    <input name="password" type="password" class="form-input">
                                                </div>

                                                <div>
                                                    <label class="form-label">Konfirmasi Password Baru</label>
                                                    <input name="password_confirmation" type="password" class="form-input">
                                                </div>

                                            </form>

                                                <div class="flex items-center justify-between gap-3 pt-2">
                                                    <button class="btn-primary btn-sm" type="submit" form="{{ $editFormId }}">Simpan</button>
                                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn-danger btn-sm" type="submit">Hapus</button>
                                                    </form>
                                                </div>
                                        </div>
                                    </details>
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
</x-app-layout>
