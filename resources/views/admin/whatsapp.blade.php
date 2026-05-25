<x-app-layout>
    <x-slot name="subbar">
        <div class="max-w-2xl w-full mx-auto px-6">
            <div class="text-2xl font-extrabold leading-tight">Pengaturan WhatsApp</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Konfigurasi gateway dan penerima notifikasi</div>
        </div>
    </x-slot>

    <div class="p-6 max-w-2xl space-y-6 mx-auto">
        {{-- Connection status --}}
        @php $connected = $settings['connection_status'] === 'connected'; @endphp
        <div class="card p-4 flex items-center gap-4">
            <div class="w-2.5 h-2.5 rounded-full" style="background: {{ $connected ? 'var(--color-brand)' : 'var(--color-negative)' }};"></div>
            <div>
                <div class="font-extrabold">{{ $connected ? 'Terhubung' : 'Gagal' }}</div>
                <div class="text-xs text-[color:var(--color-text-muted)]">Status koneksi gateway</div>
            </div>
            <span class="ml-auto status-pill {{ $connected ? 'status-pill-normal' : 'status-pill-kritis' }} text-xs">
                {{ $connected ? 'Aktif' : 'Offline' }}
            </span>
        </div>

        {{-- Gateway config --}}
        <div class="card">
            <div class="card-header">Gateway API</div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="form-label">Gateway URL</label>
                    <input type="url" class="form-input" value="{{ $settings['gateway_url'] }}" readonly>
                </div>
                <div>
                    <label class="form-label">API Token</label>
                    <input type="text" class="form-input" value="{{ $settings['gateway_token_masked'] !== '' ? $settings['gateway_token_masked'] : 'Belum dikonfigurasi di .env' }}" readonly>
                </div>
                <div>
                    <label class="form-label">Sender Number</label>
                    <input type="text" class="form-input" value="{{ $settings['sender_number'] !== '' ? $settings['sender_number'] : 'Belum dikonfigurasi di .env' }}" readonly>
                </div>
                <p class="text-xs text-[color:var(--color-text-muted)]">
                    Gateway URL, token, dan sender dibaca dari file <span class="font-bold">.env</span> agar data sensitif tidak disimpan di database.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.whatsapp.update') }}" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            @method('PUT')

            {{-- Recipient --}}
            <div class="card">
                <div class="card-header">Penerima Notifikasi</div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Nomor WhatsApp (dengan kode negara)</label>
                        <input
                            type="text"
                            name="recipient_phone"
                            class="form-input"
                            value="{{ old('recipient_phone', $settings['recipient_phone']) }}"
                        >
                        <p class="text-xs text-[color:var(--color-text-muted)] mt-1">Contoh: +628123456789</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Template Pesan</div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Template Kondisi Kritis</label>
                        <textarea name="critical_condition_template" rows="4" class="form-input">{{ old('critical_condition_template', $settings['critical_condition_template']) }}</textarea>
                    </div>
                    <div>
                        <label class="form-label">Template Sprayer Mulai</label>
                        <textarea name="spray_start_template" rows="4" class="form-input">{{ old('spray_start_template', $settings['spray_start_template']) }}</textarea>
                    </div>
                    <div>
                        <label class="form-label">Template Sprayer Berhenti</label>
                        <textarea name="spray_stop_template" rows="4" class="form-input">{{ old('spray_stop_template', $settings['spray_stop_template']) }}</textarea>
                    </div>
                    <div>
                        <label class="form-label">Template Hujan Terdeteksi</label>
                        <textarea name="rain_detected_template" rows="4" class="form-input">{{ old('rain_detected_template', $settings['rain_detected_template']) }}</textarea>
                    </div>
                    <div class="bg-[color:var(--color-bg-elevated)] rounded-lg p-4">
                        <div class="text-xs uppercase tracking-wider font-bold text-[color:var(--color-text-muted)] mb-2">Variabel Tersedia</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($available_variables as $variable)
                                <span class="badge badge-normal">{{ $variable }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn-primary" type="submit" :disabled="loading">
                <span x-show="!loading">Simpan Pengaturan</span>
                <span x-show="loading" x-cloak>Menyimpan…</span>
            </button>
        </form>

    </div>
</x-app-layout>
