<x-public-layout>
    <div class="max-w-2xl mx-auto p-4 space-y-4">

        {{-- Header info --}}
        <div class="text-center mb-6">
            <div class="text-2xl font-extrabold text-[#43c766]">🌱 Monitoring Lahan</div>
            <div class="text-sm text-[#999] mt-1">Smart Sprayer IoT — Bawang Merah Brebes</div>
            <div class="text-xs text-[#666] mt-1">Data diperbarui: {{ $sensor['recorded_at'] }}</div>
        </div>

        {{-- Condition --}}
        @php $c = $sensor['condition_status']; @endphp
        <div class="card p-6 text-center">
            <div class="text-sm text-[#999]">Status Lingkungan</div>
            <div class="text-4xl font-black mt-2
                {{ $c === 'kritis' ? 'text-red-400' : ($c === 'waspada' ? 'text-yellow-400' : 'text-green-400') }}">
                {{ strtoupper($c) }}
            </div>
        </div>

        {{-- Sensor cards --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="card p-4 text-center">
                <div class="text-xs text-[#999]">Suhu Udara</div>
                <div class="text-3xl font-extrabold mt-1">{{ $sensor['temperature'] }}°C</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-xs text-[#999]">Kelemb. Udara</div>
                <div class="text-3xl font-extrabold mt-1">{{ $sensor['air_humidity'] }}%</div>
            </div>
            <div class="card p-4 text-center">
                <div class="text-xs text-[#999]">Kelemb. Tanah</div>
                <div class="text-3xl font-extrabold mt-1">{{ $sensor['soil_moisture'] }}%</div>
            </div>
            <div class="card p-4 text-center flex flex-col justify-center">
                <div class="text-xs text-[#999]">Hujan</div>
                <div class="text-lg font-extrabold mt-1 text-blue-400">
                    {{ ($sensor['rain_status'] ?? '') === 'rain' ? '🌧️ Hujan' : '☀️ Tidak Hujan' }}
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-[#666] pt-4">
            Data bersifat informatif. Untuk kontrol alat, silakan login.
        </div>
    </div>
</x-public-layout>
