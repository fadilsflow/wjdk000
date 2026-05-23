<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-xl font-extrabold">Pengaturan WhatsApp</div>
            <div class="text-[#999] text-sm">Konfigurasi gateway dan penerima notifikasi</div>
        </div>
    </x-slot>

    <div class="p-6 max-w-2xl space-y-6">

        {{-- Connection status --}}
        <div class="card p-4 flex items-center gap-4">
            <div class="w-3 h-3 rounded-full {{ $settings['connection_status'] === 'connected' ? 'bg-[#43c766]' : 'bg-red-500' }}"></div>
            <div>
                <div class="font-bold">{{ $settings['connection_status'] === 'connected' ? 'Terhubung' : 'Gagal' }}</div>
                <div class="text-xs text-[#999]">Status koneksi gateway</div>
            </div>
        </div>

        {{-- Gateway config --}}
        <div class="card">
            <div class="card-header">
                Gateway API
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="form-label">Gateway URL</label>
                    <input type="url" class="form-input" value="{{ $settings['gateway_url'] }}">
                </div>
                <div>
                    <label class="form-label">API Token</label>
                    <input type="password" class="form-input" value="{{ $settings['api_token'] }}">
                </div>
                <button class="btn-primary" onclick="alert('Simpan gateway (backend)')">Simpan</button>
            </div>
        </div>

        {{-- Recipient --}}
        <div class="card">
            <div class="card-header">
                Penerima Notifikasi
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="form-label">Nomor WhatsApp (dengan kode negara)</label>
                    <input type="text" class="form-input" value="{{ $settings['recipient_phone'] }}">
                    <p class="text-xs text-[#666] mt-1">Contoh: +628123456789</p>
                </div>
                <button class="btn-primary" onclick="alert('Simpan penerima (backend)')">Simpan</button>
            </div>
        </div>

        {{-- Test --}}
        <div class="card">
            <div class="card-header">
                Uji Kirim
            </div>
            <div class="p-6">
                <p class="text-sm text-[#999] mb-4">Kirim pesan uji coba untuk memverifikasi konfigurasi gateway.</p>
                <button class="btn-primary" onclick="alert('Test WA (backend)')">
                    Kirim Notifikasi Uji Coba
                </button>
            </div>
        </div>

    </div>
</x-app-layout>
