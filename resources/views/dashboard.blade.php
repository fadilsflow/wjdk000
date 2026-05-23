<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Dashboard</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">{{ $device['name'] }} — Monitoring kondisi real-time</div>
        </div>
        @php
            $cs = $sensor['condition_status'] ?? 'normal';
            $pillClass = match($cs) {
                'kritis' => 'status-pill-kritis',
                'waspada' => 'status-pill-waspada',
                default => 'status-pill-normal',
            };
        @endphp
        <span class="ml-auto status-pill {{ $pillClass }}">{{ $cs }}</span>
    </x-slot>

    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- 1. Soil Moisture Gauge --}}
        <div class="card">
            <div class="card-header">Kelembapan Tanah</div>
            <div class="p-4 flex flex-col items-center justify-center min-h-[260px] text-center">
                <canvas id="soilGauge" width="150" height="150" class="mx-auto"></canvas>

                <div class="text-center text-sm mt-2">
                    <div class="text-3xl font-extrabold text-[color:var(--color-text)]" id="soilValue">
                        {{ $sensor['soil_moisture'] }}%
                    </div>
                    <div class="text-[color:var(--color-text-muted)] text-xs mt-1">
                        {{ $sensor['soil_moisture'] < 40 ? 'Tanah kering' : 'Tanah lembab' }}
                        · threshold min. 40%
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Condition Status --}}
        <div class="card">
            <div class="card-header">Status Kondisi</div>
            <div class="p-4 flex flex-col justify-center min-h-[260px]">
                <div class="rounded-xl p-5 text-center"
                     style="background: {{ $cs === 'kritis' ? 'rgba(243,114,127,0.12)' : ($cs === 'waspada' ? 'rgba(255,164,43,0.12)' : 'rgba(30,215,96,0.12)') }};">
                    <div class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold">Saat ini</div>
                    <div class="text-4xl font-black mt-2 uppercase"
                         style="color: {{ $cs === 'kritis' ? 'var(--color-negative)' : ($cs === 'waspada' ? 'var(--color-warning)' : 'var(--color-brand)') }};">
                        {{ $cs }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div class="bg-[color:var(--color-bg-elevated)] rounded-lg p-3 text-center">
                        <div class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold">Mode</div>
                        <div class="text-base font-extrabold text-[color:var(--color-text)] uppercase mt-1">
                            {{ $device['mode'] }}
                        </div>
                    </div>

                    <div class="bg-[color:var(--color-bg-elevated)] rounded-lg p-3 text-center">
                        <div class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold">Sprayer</div>
                        <div class="text-base font-extrabold uppercase mt-1"
                             style="color: {{ $sensor['sprayer_status'] === 'on' ? 'var(--color-brand)' : 'var(--color-border-light)' }};">
                            {{ $sensor['sprayer_status'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Temperature --}}
        <div class="card flex flex-col">
            <div class="card-header shrink-0">Suhu Udara</div>
            <div class="p-4 flex flex-col items-center justify-center flex-1 text-center">
                <div class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold">Sensor BME280</div>
                <div class="text-5xl font-extrabold mt-2"
                     style="color: {{ $sensor['temperature'] > 32 ? 'var(--color-warning)' : 'var(--color-brand)' }};">
                    {{ $sensor['temperature'] }}°C
                </div>
                <div class="text-[color:var(--color-text-muted)] text-xs mt-2">Max threshold: 32°C</div>
            </div>
        </div>

        {{-- 4. Humidity --}}
        <div class="card flex flex-col">
            <div class="card-header shrink-0">Kelembapan Udara</div>
            <div class="p-4 flex flex-col items-center justify-center flex-1 text-center">
                <div class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold">Sensor BME280</div>
                <div class="text-5xl font-extrabold mt-2 text-[color:var(--color-brand)]">
                    {{ $sensor['air_humidity'] }}%
                </div>
                <div class="text-[color:var(--color-text-muted)] text-xs mt-2">Normal untuk monitoring</div>
            </div>
        </div>

        {{-- 5. Chart --}}
        <div class="card sm:col-span-2">
            <div class="card-header">
                Grafik Sensor Real-Time
                <span class="ml-auto text-[color:var(--color-text-muted)] text-xs font-bold uppercase tracking-wider">60 Menit Terakhir</span>
            </div>
            <div class="p-4">
                <canvas id="sensorChart" height="200" class="w-full"></canvas>
            </div>
        </div>

        {{-- 6. Control Panel --}}
        <div class="card sm:col-span-2">
            <div class="card-header">
                Kontrol Penyemprotan
                <span class="ml-auto text-[color:var(--color-text-muted)] text-xs">Aksi dikirim ke perangkat</span>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">

                <div class="bg-[color:var(--color-bg-elevated)] rounded-lg p-5 text-center">
                    <div class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold mb-3">Mode</div>
                    <div class="text-lg font-extrabold uppercase mb-3"
                         style="color: {{ $device['mode'] === 'automatic' ? 'var(--color-brand)' : 'var(--color-text-near)' }};">
                        {{ $device['mode'] }}
                    </div>
                    <button class="btn-outline btn-sm" onclick="alert('Ganti mode (backend)')">
                        Ganti ke {{ $device['mode'] === 'automatic' ? 'Manual' : 'Otomatis' }}
                    </button>
                </div>

                <div class="bg-[color:var(--color-bg-elevated)] rounded-lg p-5 text-center">
                    <div class="text-[color:var(--color-text-muted)] text-xs uppercase tracking-wider font-bold mb-3">Pompa Sprayer</div>
                    <button class="btn-circle btn-circle-lg mx-auto {{ $sensor['sprayer_status'] === 'on' ? 'is-active' : '' }}"
                            onclick="alert('Toggle sprayer (backend)')"
                            aria-label="Toggle sprayer">
                        @if($sensor['sprayer_status'] === 'on')
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                                <rect x="6" y="5" width="4" height="14" rx="1"/>
                                <rect x="14" y="5" width="4" height="14" rx="1"/>
                            </svg>
                        @else
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        @endif
                    </button>
                    <div class="text-lg font-extrabold uppercase mt-3"
                         style="color: {{ $sensor['sprayer_status'] === 'on' ? 'var(--color-brand)' : 'var(--color-text-near)' }};">
                        {{ $sensor['sprayer_status'] }}
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. Recent Activity --}}
        <div class="card sm:col-span-2">
            <div class="card-header">
                Riwayat Aktivitas
                <a href="{{ route('history.sensor') }}" class="ml-auto text-xs font-bold uppercase tracking-wider text-[color:var(--color-brand)] hover:underline">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-xs">{{ $sensor['recorded_at'] }}</td>
                            <td>Sensor</td>
                            <td><span class="badge badge-{{ $cs }}">{{ ucfirst($cs) }}</span></td>
                            <td class="text-[color:var(--color-text-muted)] text-xs">Tanah kering, tidak hujan</td>
                        </tr>
                        <tr>
                            <td class="text-xs">10:00</td>
                            <td>Sprayer</td>
                            <td><span class="badge badge-on">ON</span></td>
                            <td class="text-[color:var(--color-text-muted)] text-xs">Aktif otomatis</td>
                        </tr>
                        <tr>
                            <td class="text-xs">09:45</td>
                            <td>WhatsApp</td>
                            <td><span class="badge badge-sent">Sent</span></td>
                            <td class="text-[color:var(--color-text-muted)] text-xs">Peringatan kondisi kritis</td>
                        </tr>
                        <tr>
                            <td class="text-xs">09:30</td>
                            <td>Sensor</td>
                            <td><span class="badge badge-normal">Normal</span></td>
                            <td class="text-[color:var(--color-text-muted)] text-xs">Monitoring berkala</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 8. Rain Status --}}
        <div class="card">
            <div class="card-header">Status Hujan</div>
            <div class="p-4 flex flex-col items-center justify-center h-[200px] text-center gap-3">
                @if(($sensor['rain_status'] ?? '') === 'rain')
                    <div class="w-14 h-14 rounded-full bg-[color:var(--color-bg-elevated)] grid place-items-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--color-info)">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 014-4h.7A6 6 0 0119 11.5a3.5 3.5 0 010 7H7a4 4 0 01-4-3.5zM8 19l-1 3M12 19l-1 3M16 19l-1 3"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-extrabold" style="color: var(--color-info)">Hujan</div>
                    <div class="text-[color:var(--color-text-muted)] text-xs">Penyemprotan otomatis tidak dijalankan.</div>
                @else
                    <div class="w-14 h-14 rounded-full bg-[color:var(--color-bg-elevated)] grid place-items-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--color-text-near)">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 014-4h.7A6 6 0 0119 11.5a3.5 3.5 0 010 7H7a4 4 0 01-4-3.5z"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-extrabold text-[color:var(--color-text)]">Tidak Hujan</div>
                    <div class="text-[color:var(--color-text-muted)] text-xs">Penyemprotan otomatis diizinkan bila tanah kering.</div>
                @endif
            </div>
        </div>

        {{-- 9. Notification Status --}}
        <div class="card">
            <div class="card-header">Notifikasi WhatsApp</div>
            <div class="p-4 flex flex-col items-center justify-center h-[200px] text-center gap-3">
                <div class="w-14 h-14 rounded-full bg-[color:var(--color-bg-elevated)] grid place-items-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--color-brand)">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-2xl font-extrabold" style="color: var(--color-brand)">Aktif</div>
                <div class="text-[color:var(--color-text-muted)] text-xs">
                    Peringatan kritis dan aktivitas sprayer disimpan ke log.
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        try {
            const gaugeCanvas = document.getElementById('soilGauge');
            if (!gaugeCanvas) throw new Error('soilGauge canvas not found');

            const ctx = gaugeCanvas.getContext('2d');
            const sm = {{ $sensor['soil_moisture'] }};
            const angle = (sm / 100) * 180;
            const w = gaugeCanvas.width;
            const h = gaugeCanvas.height;
            const cx = w / 2;
            const cy = h * 0.68;
            const r = 55;

            ctx.beginPath();
            ctx.arc(cx, cy, r, Math.PI, 2 * Math.PI);
            ctx.strokeStyle = '#1f1f1f';
            ctx.lineWidth = 18;
            ctx.lineCap = 'round';
            ctx.stroke();

            const endAngle = Math.PI + (angle * Math.PI / 180);
            const strokeColor = sm < 40 ? '#ffa42b' : '#1ed760';

            ctx.beginPath();
            ctx.arc(cx, cy, r, Math.PI, endAngle);
            ctx.strokeStyle = strokeColor;
            ctx.lineWidth = 18;
            ctx.lineCap = 'round';
            ctx.stroke();
        } catch (e) {
            console.warn('Gauge render skipped:', e.message);
        }

        try {
            const chartCanvas = document.getElementById('sensorChart');
            if (!chartCanvas || typeof Chart === 'undefined') throw new Error('Chart.js or canvas not available');

            new Chart(chartCanvas, {
                type: 'line',
                data: {
                    labels: ['09:00', '09:15', '09:30', '09:45', '10:00'],
                    datasets: [
                        { label: 'Suhu (°C)', data: [30.1, 30.5, 30.8, 31.2, 31.5], borderColor: '#1ed760', backgroundColor: 'transparent', tension: 0.3, pointRadius: 3 },
                        { label: 'Kelemb. Udara (%)', data: [74, 73, 72, 71, 70], borderColor: '#539df5', backgroundColor: 'transparent', tension: 0.3, pointRadius: 3 },
                        { label: 'Kelemb. Tanah (%)', data: [48, 45, 42, 38, 35], borderColor: '#ffa42b', backgroundColor: 'transparent', tension: 0.3, pointRadius: 3 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#b3b3b3',
                                font: { size: 12, weight: 600 }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#b3b3b3', font: { size: 11 } },
                            grid: { color: '#252525' }
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { color: '#b3b3b3', font: { size: 11 } },
                            grid: { color: '#252525' }
                        }
                    }
                }
            });
        } catch (e) {
            console.warn('Chart render skipped:', e.message);
        }
    });
    </script>
    @endpush
</x-app-layout>