<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-xl font-extrabold">Konfigurasi Alat & Threshold</div>
            <div class="text-[#999] text-sm">Pengaturan perangkat IoT dan batas sensor</div>
        </div>
    </x-slot>

    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Device list --}}
        <div class="card">
            <div class="card-header">
                Perangkat IoT
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $d)
                        <tr>
                            <td class="font-medium">{{ $d['name'] }}</td>
                            <td class="text-xs">{{ $d['location'] }}</td>
                            <td class="text-xs font-mono">{{ substr($d['api_key'], 0, 8) }}...</td>
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Threshold settings --}}
        <div class="card">
            <div class="card-header">
                Threshold Sensor
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="form-label">Min. Kelembapan Tanah (%)</label>
                    <input type="number" class="form-input" value="{{ $thresholds['min_soil_moisture'] }}">
                </div>
                <div>
                    <label class="form-label">Maks. Suhu (°C)</label>
                    <input type="number" class="form-input" value="{{ $thresholds['max_temperature'] }}">
                </div>
                <div>
                    <label class="form-label">Min. Kelembapan Udara (%)</label>
                    <input type="number" class="form-input" value="{{ $thresholds['min_air_humidity'] }}">
                </div>
                <button class="btn-primary w-full" onclick="alert('Simpan threshold (backend)')">
                    Simpan Threshold
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
