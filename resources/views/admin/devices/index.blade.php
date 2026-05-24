<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Konfigurasi Alat & Threshold</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Pengaturan perangkat IoT dan batas sensor</div>
        </div>
    </x-slot>

    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Device list --}}
        <div class="card">
            <div class="card-header">Perangkat IoT</div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Lokasi</th>
                            <th>API Key</th>
                            <th>Mode</th>
                            <th>Sprayer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $d)
                            <tr>
                                <td class="font-semibold">{{ $d['name'] }}</td>
                                <td class="text-xs text-[color:var(--color-text-muted)]">{{ $d['location'] }}</td>
                                <td class="text-xs font-mono text-[color:var(--color-text-muted)]">{{ substr($d['api_key'], 0, 8) }}...</td>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-sm text-[color:var(--color-text-muted)] py-6">
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
            <div class="p-6 space-y-4">
                <div>
                    <label class="form-label">Min. Kelembapan Tanah (%)</label>
                    <input type="number" class="form-input" value="{{ $thresholds['min_soil_moisture'] }}" readonly>
                </div>
                <div>
                    <label class="form-label">Maks. Suhu (°C)</label>
                    <input type="number" class="form-input" value="{{ $thresholds['max_temperature'] }}" readonly>
                </div>
                <div>
                    <label class="form-label">Min. Kelembapan Udara (%)</label>
                    <input type="number" class="form-input" value="{{ $thresholds['min_air_humidity'] }}" readonly>
                </div>
                <p class="text-xs text-[color:var(--color-text-muted)]">
                    Menampilkan threshold perangkat pertama yang terdaftar. Update backend threshold belum masuk scope perbaikan ini.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
