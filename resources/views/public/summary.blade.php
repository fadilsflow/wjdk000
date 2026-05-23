<x-public-layout>
    <div class="max-w-2xl mx-auto p-4 space-y-4">

        {{-- Header info --}}
        <div class="text-center mb-6 mt-4">
            <div class="inline-flex items-center gap-3 mb-2">
                <span class="w-10 h-10 rounded-full bg-[color:var(--color-brand)] grid place-items-center text-black font-black text-base">S</span>
                <span class="text-xl font-extrabold text-[color:var(--color-text)]">Monitoring Lahan</span>
            </div>
            <div class="text-sm text-[color:var(--color-text-muted)]">Smart Sprayer IoT — Bawang Merah Brebes</div>
            <div class="text-xs text-[color:var(--color-text-muted)] mt-2">
                <span class="uppercase tracking-wider font-bold">Diperbarui</span> · {{ $sensor['recorded_at'] }}
            </div>
        </div>

        {{-- Condition --}}
        @php
            $c = $sensor['condition_status'];
            $pillClass = match($c) {
                'kritis' => 'status-pill-kritis',
                'waspada' => 'status-pill-waspada',
                default => 'status-pill-normal',
            };
        @endphp
        <div class="card p-8 text-center">
            <div class="text-xs uppercase tracking-wider font-bold text-[color:var(--color-text-muted)] mb-4">Status Lingkungan</div>
            <span class="status-pill {{ $pillClass }} text-base px-6 py-3">{{ $c }}</span>
        </div>

        {{-- Sensor cards --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="card p-5 text-center">
                <div class="text-xs uppercase tracking-wider font-bold text-[color:var(--color-text-muted)]">Suhu Udara</div>
                <div class="text-4xl font-extrabold mt-2 text-[color:var(--color-text)]">{{ $sensor['temperature'] }}<span class="text-xl text-[color:var(--color-text-muted)]">°C</span></div>
            </div>
            <div class="card p-5 text-center">
                <div class="text-xs uppercase tracking-wider font-bold text-[color:var(--color-text-muted)]">Kelemb. Udara</div>
                <div class="text-4xl font-extrabold mt-2 text-[color:var(--color-text)]">{{ $sensor['air_humidity'] }}<span class="text-xl text-[color:var(--color-text-muted)]">%</span></div>
            </div>
            <div class="card p-5 text-center">
                <div class="text-xs uppercase tracking-wider font-bold text-[color:var(--color-text-muted)]">Kelemb. Tanah</div>
                <div class="text-4xl font-extrabold mt-2 text-[color:var(--color-text)]">{{ $sensor['soil_moisture'] }}<span class="text-xl text-[color:var(--color-text-muted)]">%</span></div>
            </div>
            <div class="card p-5 text-center flex flex-col justify-center">
                <div class="text-xs uppercase tracking-wider font-bold text-[color:var(--color-text-muted)]">Hujan</div>
                <div class="text-xl font-extrabold mt-2" style="color: var(--color-info)">
                    {{ ($sensor['rain_status'] ?? '') === 'rain' ? 'Hujan' : 'Cerah' }}
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-[color:var(--color-text-muted)] pt-4">
            Data bersifat informatif. Untuk kontrol alat, silakan login.
        </div>
    </div>
</x-public-layout>
