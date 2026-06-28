<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Konfigurasi Threshold</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Batas sensor untuk perangkat IoT</div>
        </div>
    </x-slot>

    <div
        x-data="{
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
    >

    <div class="p-6 max-w-3xl mx-auto space-y-6">

        @if($device)
            <div class="card p-6">
                <div class="card-header !px-0 !pt-0 !border-0 mb-4">Perangkat IoT</div>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold mb-1">Nama</dt>
                        <dd class="font-semibold">{{ $device['name'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold mb-1">Lokasi</dt>
                        <dd>{{ $device['location'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold mb-1">Mode</dt>
                        <dd>
                            <span class="badge badge-{{ $device['mode'] === 'automatic' ? 'automatic' : 'manual' }}">
                                {{ ucfirst($device['mode']) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold mb-1">Sprayer</dt>
                        <dd>
                            <span class="badge badge-{{ $device['sprayer_status'] === 'on' ? 'on' : 'off' }}">
                                {{ strtoupper($device['sprayer_status']) }}
                            </span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold mb-1">API Key</dt>
                        <dd class="flex items-center gap-2">
                            <span class="text-xs font-mono text-[color:var(--color-text-muted)] break-all">{{ $device['api_key'] }}</span>
                            <button
                                type="button"
                                class="btn-outline btn-sm shrink-0"
                                title="Salin API key"
                                @click="copyApiKey({{ Js::from($device['api_key']) }})"
                            >Copy</button>
                        </dd>
                    </div>
                </dl>
            </div>
        @else
            <div class="card p-6 text-sm text-[color:var(--color-text-muted)]">
                Belum ada perangkat IoT terdaftar di database.
            </div>
        @endif

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
                    <p class="text-xs text-[color:var(--color-negative)]">Belum ada perangkat terdaftar.</p>
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

    </div>
</x-app-layout>
