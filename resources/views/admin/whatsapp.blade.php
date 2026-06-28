<x-app-layout>
    <x-slot name="subbar">
        <div class="max-w-2xl w-full mx-auto px-6">
            <div class="text-2xl font-extrabold leading-tight">Pengaturan WhatsApp</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Konfigurasi gateway dan penerima notifikasi</div>
        </div>
    </x-slot>

    <div class="p-6 max-w-2xl space-y-6 mx-auto">
        {{-- Connection status --}}
        @php
            $status = $settings['connection_status'];
        @endphp
        <div class="card p-4 flex items-center gap-4 flex-wrap">
            @if($status === 'connected')
                <div class="w-2.5 h-2.5 rounded-full bg-[color:var(--color-brand)]"></div>
                <div>
                    <div class="font-extrabold text-[color:var(--color-brand)]">Terhubung</div>
                    <div class="text-xs text-[color:var(--color-text-muted)]">Gateway aktif dan WhatsApp siap mengirim notifikasi</div>
                </div>
                <span class="ml-auto status-pill status-pill-normal text-xs">Aktif</span>
                <form
                    method="POST"
                    action="{{ route('admin.whatsapp.test') }}"
                    class="w-full sm:w-auto"
                    x-data="{ loading: false }"
                    @submit="loading = true"
                >
                    @csrf
                    <button type="submit" class="btn-outline btn-sm w-full sm:w-auto" :disabled="loading">
                        <span x-show="!loading">Kirim Pesan Uji</span>
                        <span x-show="loading" x-cloak>Mengirim…</span>
                    </button>
                </form>
            @elseif($status === 'qr_pending')
                <div class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></div>
                <div>
                    <div class="font-extrabold text-amber-500">Perlu Scan QR Code</div>
                    <div class="text-xs text-[color:var(--color-text-muted)]">Gateway aktif, scan QR Code di bawah halaman ini</div>
                </div>
                <span class="ml-auto status-pill text-xs bg-amber-100 text-amber-800">Menunggu QR</span>
            @elseif($status === 'offline')
                <div class="w-2.5 h-2.5 rounded-full bg-[color:var(--color-negative)]"></div>
                <div>
                    <div class="font-extrabold text-[color:var(--color-negative)]">Server Offline</div>
                    <div class="text-xs text-[color:var(--color-text-muted)]">Gagal terhubung ke WhatsApp Gateway server di port 3000</div>
                </div>
                <span class="ml-auto status-pill status-pill-kritis text-xs">Offline</span>
            @else
                <div class="w-2.5 h-2.5 rounded-full bg-gray-400"></div>
                <div>
                    <div class="font-extrabold text-gray-500">Belum Dikonfigurasi</div>
                    <div class="text-xs text-[color:var(--color-text-muted)]">Konfigurasi token atau URL kosong pada berkas .env</div>
                </div>
                <span class="ml-auto status-pill text-xs bg-gray-100 text-gray-800">Belum Set</span>
            @endif
        </div>

        {{-- QR Code card (tampil hanya saat qr_pending & qr string tersedia) --}}
        @if($status === 'qr_pending' && !empty($settings['qr_code_string']))
            <div class="card p-6 flex flex-col items-center justify-center text-center gap-4">
                <div class="text-base font-bold text-[color:var(--color-text)]">Scan QR Code untuk Menghubungkan</div>
                <div class="text-sm text-[color:var(--color-text-muted)] max-w-sm">
                    Buka WhatsApp &rarr; Menu &rarr; <strong>Perangkat Tertaut</strong> &rarr; <strong>Tautkan Perangkat</strong>, lalu arahkan kamera ke QR di bawah ini:
                </div>
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data={{ urlencode($settings['qr_code_string']) }}"
                    alt="Scan QR Code WhatsApp"
                    class="mx-auto rounded-lg shadow-md bg-white p-2 border border-[color:var(--color-border)]"
                    width="240" height="240"
                >
                <p class="text-xs text-[color:var(--color-text-muted)] animate-pulse">Refresh halaman ini setelah scan untuk mengecek status koneksi.</p>
            </div>
        @endif

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
                    <label class="form-label">
                        Sender Number
                        @if($status === 'connected' && !empty($settings['sender_number']))
                            <span class="ml-2 text-[10px] font-semibold uppercase tracking-wider text-[color:var(--color-brand)] bg-[color:var(--color-brand-subtle,#d1fae5)] px-1.5 py-0.5 rounded">Terdeteksi Otomatis</span>
                        @endif
                    </label>
                    <input
                        type="text"
                        class="form-input"
                        value="{{ $settings['sender_number'] !== '' ? $settings['sender_number'] : ($status === 'connected' ? 'Terhubung (nomor tidak tersedia)' : 'Belum terhubung') }}"
                        readonly
                    >
                    <p class="text-xs text-[color:var(--color-text-muted)] mt-1">Nomor dideteksi otomatis dari akun WhatsApp yang terhubung.</p>
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
