<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Konfigurasi Alat & Threshold</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Pengaturan perangkat IoT dan batas sensor</div>
        </div>
    </x-slot>

    <div
        x-data="{
            showCreate: false,
            showEdit: false,
            editDevice: { id: null, name: '', location: '' },
            openEdit(device) { this.editDevice = { ...device }; this.showEdit = true; },
            async copyApiKey(apiKey) {
                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(apiKey);
                    } else {
                        const textarea = document.createElement('textarea');
                        textarea.value = apiKey;
                        textarea.style.position = 'fixed';
                        textarea.style.opacity = '0';
                        document.body.appendChild(textarea);
                        textarea.focus();
                        textarea.select();
                        document.execCommand('copy');
                        textarea.remove();
                    }

                    window.dispatchEvent(new CustomEvent('toast', { detail: { msg: 'API key perangkat berhasil disalin.', type: 'success' } }));
                } catch (error) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { msg: 'Gagal menyalin API key.', type: 'error' } }));
                }
            }
        }"
        @keydown.escape.window="showCreate = false; showEdit = false"
    >

    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Device list --}}
        <div class="card">
            <div class="card-header">
                Perangkat IoT
                <button @click="showCreate = true" class="ml-auto btn-primary btn-sm">+ Tambah</button>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Lokasi</th>
                            <th>API Key</th>
                            <th>Mode</th>
                            <th>Sprayer</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $d)
                            <tr>
                                <td class="font-semibold">{{ $d['name'] }}</td>
                                <td class="text-xs text-[color:var(--color-text-muted)]">{{ $d['location'] }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-mono text-[color:var(--color-text-muted)]">{{ substr($d['api_key'], 0, 8) }}…</span>
                                        <button
                                            type="button"
                                            class="btn-outline btn-sm !px-2 !py-1 text-[11px]"
                                            title="Salin API key lengkap"
                                            @click="copyApiKey({{ Js::from($d['api_key']) }})"
                                        >Copy</button>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $d['mode'] === 'automatic' ? 'automatic' : 'manual' }}">
                                        {{ ucfirst($d['mode']) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $d['sprayer_status'] === 'on' ? 'on' : 'off' }}">
                                        {{ strtoupper($d['sprayer_status']) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <button
                                        class="btn-outline btn-sm"
                                        @click="openEdit({
                                            id: {{ $d['id'] }},
                                            name: {{ Js::from($d['name']) }},
                                            location: {{ Js::from($d['location']) }}
                                        })"
                                    >Edit</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-sm text-[color:var(--color-text-muted)] py-6">
                                    Belum ada perangkat IoT terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Threshold settings --}}
        <div class="card">
            <div class="card-header">Threshold Sensor</div>
            <form
                method="POST"
                action="{{ route('admin.threshold.update') }}"
                class="p-6 space-y-4"
                x-data="{ loading: false }" @submit="loading = true"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="device_id" value="{{ $thresholds['device_id'] }}">

                <div>
                    <label class="form-label">Min. Kelembapan Tanah (%)</label>
                    <input
                        type="number"
                        name="min_soil_moisture"
                        step="0.1"
                        min="0"
                        max="100"
                        class="form-input"
                        value="{{ old('min_soil_moisture', $thresholds['min_soil_moisture']) }}"
                        required
                    >
                    <x-input-error :messages="$errors->get('min_soil_moisture')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Maks. Suhu (°C)</label>
                    <input
                        type="number"
                        name="max_temperature"
                        step="0.1"
                        min="0"
                        max="100"
                        class="form-input"
                        value="{{ old('max_temperature', $thresholds['max_temperature']) }}"
                        required
                    >
                    <x-input-error :messages="$errors->get('max_temperature')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Min. Kelembapan Udara (%)</label>
                    <input
                        type="number"
                        name="min_air_humidity"
                        step="0.1"
                        min="0"
                        max="100"
                        class="form-input"
                        value="{{ old('min_air_humidity', $thresholds['min_air_humidity']) }}"
                        required
                    >
                    <x-input-error :messages="$errors->get('min_air_humidity')" class="mt-1" />
                </div>

                @if($thresholds['device_id'] === null)
                    <p class="text-xs text-[color:var(--color-negative)]">Belum ada perangkat terdaftar. Daftarkan perangkat terlebih dahulu.</p>
                @else
                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="btn-primary" :disabled="loading">
                            <span x-show="!loading">Simpan Threshold</span>
                            <span x-show="loading" x-cloak>Menyimpan…</span>
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Create Device Modal --}}
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
            class="relative z-10 w-full max-w-md bg-[color:var(--color-bg-card-2)] rounded-2xl shadow-dialog border border-[color:var(--color-bg-elevated)] p-6"
        >
            <div class="flex items-center justify-between mb-5">
                <div class="text-lg font-extrabold text-[color:var(--color-text)]">Tambah Perangkat</div>
                <button @click="showCreate = false" class="text-[color:var(--color-text-muted)] hover:text-[color:var(--color-text)] transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.devices.store') }}" class="space-y-4"
                  x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <div>
                    <label class="form-label">Nama Perangkat</label>
                    <input name="name" type="text" class="form-input" placeholder="Smart Sprayer Brebes" required autofocus>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Lokasi</label>
                    <input name="location" type="text" class="form-input" placeholder="Brebes, Jawa Tengah" required>
                    <x-input-error :messages="$errors->get('location')" class="mt-1" />
                </div>
                <p class="text-xs text-[color:var(--color-text-muted)]">
                    API Key dibuat otomatis. Threshold default: tanah 40%, suhu 35°C, udara 60%.
                </p>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreate = false" class="btn-outline">Batal</button>
                    <button type="submit" class="btn-primary" :disabled="loading">
                        <span x-show="!loading">Tambah Perangkat</span>
                        <span x-show="loading" x-cloak>Menyimpan…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Device Modal --}}
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
                <div class="text-lg font-extrabold text-[color:var(--color-text)]">Edit Perangkat</div>
                <button @click="showEdit = false" class="text-[color:var(--color-text-muted)] hover:text-[color:var(--color-text)] transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form
                method="POST"
                :action="`{{ url('admin/devices') }}/${editDevice.id}`"
                class="space-y-4"
                x-data="{ loading: false }" @submit="loading = true"
            >
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label">Nama Perangkat</label>
                    <input name="name" type="text" class="form-input" :value="editDevice.name" required>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <label class="form-label">Lokasi</label>
                    <input name="location" type="text" class="form-input" :value="editDevice.location" required>
                    <x-input-error :messages="$errors->get('location')" class="mt-1" />
                </div>
                <p class="text-xs text-[color:var(--color-text-muted)]">API Key tidak dapat diubah dari sini.</p>

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

    </div>{{-- end x-data --}}
</x-app-layout>
