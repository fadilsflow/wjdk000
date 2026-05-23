<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-xl font-extrabold">Dashboard</div>
            <div class="text-[#999] text-sm">{{ $device['name'] }} — Monitoring kondisi real-time</div>
        </div>
        @php
            $cs = $sensor['condition_status'] ?? 'normal';
            $pillClass = match($cs) {
                'kritis' => 'status-pill-kritis',
                'waspada' => 'status-pill-waspada',
                default => 'status-pill-normal',
            };
        @endphp
        <span class="ml-auto {{ $pillClass }}">{{ strtoupper($cs) }}</span>
    </x-slot>

    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- 1. Soil Moisture Gauge --}}
        <div class="card">
            <div class="card-header">Kelembapan Tanah</div>
            <div class="p-4 flex flex-col items-center">
                <canvas id="soilGauge" width="180" height="180" class="mx-auto"></canvas>
                <div class="text-center text-sm mt-2">
                    <div class="text-3xl font-extrabold text-white" id="soilValue">{{ $sensor['soil_moisture'] }}%</div>
                    <div class="text-[#999] text-xs mt-1">
                        {{ $sensor['soil_moisture'] < 40 ? 'Tanah kering' : 'Tanah lembab' }}
                        • threshold min. 40%
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Condition Status --}}
        <div class="card">
            <div class="card-header">Status Kondisi Lingkungan</div>
            <div class="p-4">
                <div class="rounded-xl p-5 text-center border"
                     style="background: {{ $cs === 'kritis' ? 'rgba(239,68,68,0.12)' : ($cs === 'waspada' ? 'rgba(244,183,64,0.12)' : 'rgba(67,199,102,0.12)') }};
                            border-color: {{ $cs === 'kritis' ? 'rgba(239,68,68,0.28)' : ($cs === 'waspada' ? 'rgba(244,183,64,0.28)' : 'rgba(67,199,102,0.28)') }};">
                    <div class="text-[#999] text-sm">Status saat ini</div>
                    <div class="text-4xl font-black mt-1
                        {{ $cs === 'kritis' ? 'text-red-400' : ($cs === 'waspada' ? 'text-yellow-400' : 'text-green-400') }}">
                        {{ strtoupper($cs) }}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div class="bg-[#303634] rounded-lg p-3 text-center">
                        <div class="text-[#999] text-xs">Mode</div>
                        <div class="text-base font-extrabold text-white uppercase">{{ $device['mode'] }}</div>
                    </div>
                    <div class="bg-[#303634] rounded-lg p-3 text-center">
                        <div class="text-[#999] text-xs">Sprayer</div>
                        <div class="text-base font-extrabold"
                             style="color: {{ $sensor['sprayer_status'] === 'on' ? '#43c766' : '#696f6c' }}">
                            {{ strtoupper($sensor['sprayer_status']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Temperature --}}
        <div class="card">
            <div class="card-header">Suhu Udara</div>
            <div class="p-4 flex flex-col items-center justify-center h-[180px] text-center">
                <div class="text-[#999] text-sm">Sensor BME280</div>
                <div class="text-5xl font-extrabold mt-2 {{ $sensor['temperature'] > 32 ? 'text-yellow-400' : 'text-green-400' }}">
                    {{ $sensor['temperature'] }}°C
                </div>
                <div class="text-[#999] text-xs mt-2">Max threshold: 32°C</div>
            </div>
        </div>

        {{-- 4. Humidity --}}
        <div class="card">
            <div class="card-header">Kelembapan Udara</div>
            <div class="p-4 flex flex-col items-center justify-center h-[180px] text-center">
                <div class="text-[#999] text-sm">Sensor BME280</div>
                <div class="text-5xl font-extrabold mt-2 text-green-400">{{ $sensor['air_humidity'] }}%</div>
                <div class="text-[#999] text-xs mt-2">Normal untuk monitoring</div>
            </div>
        </div>

        {{-- 5. Chart (span 2 cols desktop) --}}
        <div class="card sm:col-span-2">
            <div class="card-header">
                Grafik Sensor Real-Time
                <span class="ml-auto text-[#999] text-xs">60 menit terakhir</span>
            </div>
            <div class="p-4">
                <canvas id="sensorChart" height="200" class="w-full"></canvas>
            </div>
        </div>

        {{-- 6. Control Panel (span 2 cols desktop) — functional --}}
        <div class="card sm:col-span-2">
            <div class="card-header">
                Kontrol Penyemprotan
                <span class="ml-auto text-[#999] text-xs">Aksi akan dikirim ke perangkat</span>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-[#2b2b2b] rounded-lg p-5 text-center">
                    <div class="text-[#999] text-sm mb-2">Mode</div>
                    <div class="w-[80px] h-[44px] mx-auto rounded-full relative transition-colors cursor-pointer
                        {{ $device['mode'] === 'automatic' ? 'bg-[#43c766]' : 'bg-[#626866]' }}">
                        <div class="w-[32px] h-[32px] bg-white rounded-full absolute top-[6px]
                            {{ $device['mode'] === 'automatic' ? 'right-[6px]' : 'left-[6px]' }}">
                        </div>
                    </div>
                    <div class="text-lg font-extrabold mt-2 {{ $device['mode'] === 'automatic' ? 'text-[#43c766]' : 'text-[#c0c6c2]' }}">
                        {{ strtoupper($device['mode']) }}
                    </div>
                    <button class="btn-secondary text-xs py-1 px-3 mt-2" onclick="alert('Ganti mode (backend)')">
                        {{ $device['mode'] === 'automatic' ? 'Manual' : 'Otomatis' }}
                    </button>
                </div>
                <div class="bg-[#2b2b2b] rounded-lg p-5 text-center">
                    <div class="text-[#999] text-sm mb-2">Pompa Sprayer</div>
                    <div class="w-[80px] h-[44px] mx-auto rounded-full relative transition-colors
                        {{ $sensor['sprayer_status'] === 'on' ? 'bg-[#43c766]' : 'bg-[#626866]' }}">
                        <div class="w-[32px] h-[32px] bg-white rounded-full absolute top-[6px]
                            {{ $sensor['sprayer_status'] === 'on' ? 'right-[6px]' : 'left-[6px]' }}">
                        </div>
                    </div>
                    <div class="text-lg font-extrabold mt-2 {{ $sensor['sprayer_status'] === 'on' ? 'text-[#43c766]' : 'text-[#c0c6c2]' }}">
                        {{ strtoupper($sensor['sprayer_status']) }}
                    </div>
                    <div class="flex gap-2 mt-2 justify-center">
                        <button class="btn-primary text-xs py-1 px-3 {{ $sensor['sprayer_status'] === 'on' ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}"
                                onclick="alert('Nyalakan sprayer (backend)')">
                            Nyalakan
                        </button>
                        <button class="btn-secondary text-xs py-1 px-3 {{ $sensor['sprayer_status'] === 'off' ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}"
                                onclick="alert('Matikan sprayer (backend)')">
                            Matikan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. Recent Activity (span 2 cols) --}}
        <div class="card sm:col-span-2">
            <div class="card-header">
                Riwayat Aktivitas Terbaru
                <a href="{{ route('history.sensor') }}" class="ml-auto text-[#43c766] text-xs">Lihat semua →</a>
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
                            <td class="text-[#999] text-xs">Tanah kering, tidak hujan</td>
                        </tr>
                        <tr>
                            <td class="text-xs">10:00</td>
                            <td>Sprayer</td>
                            <td><span class="badge badge-on">ON</span></td>
                            <td class="text-[#999] text-xs">Aktif otomatis</td>
                        </tr>
                        <tr>
                            <td class="text-xs">09:45</td>
                            <td>WhatsApp</td>
                            <td><span class="badge badge-sent">Sent</span></td>
                            <td class="text-[#999] text-xs">Peringatan kondisi kritis</td>
                        </tr>
                        <tr>
                            <td class="text-xs">09:30</td>
                            <td>Sensor</td>
                            <td><span class="badge badge-normal">Normal</span></td>
                            <td class="text-[#999] text-xs">Monitoring berkala</td>
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
                    <div class="text-5xl">🌧️</div>
                    <div class="text-blue-400 text-2xl font-extrabold">Hujan</div>
                    <div class="text-[#999] text-xs">Penyemprotan otomatis tidak dijalankan.</div>
                @else
                    <div class="text-5xl">☁️</div>
                    <div class="text-blue-400 text-2xl font-extrabold">Tidak Hujan</div>
                    <div class="text-[#999] text-xs">Penyemprotan otomatis diizinkan bila tanah kering.</div>
                @endif
            </div>
        </div>

        {{-- 9. Notification Status --}}
        <div class="card">
            <div class="card-header">Notifikasi WhatsApp</div>
            <div class="p-4 flex flex-col items-center justify-center h-[200px] text-center gap-3">
                <div class="text-5xl">💬</div>
                <div class="text-green-400 text-2xl font-extrabold">Terkirim</div>
                <div class="text-[#999] text-xs">
                    Peringatan kritis, sprayer mulai/berhenti, dan hujan terdeteksi disimpan ke log.
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Soil moisture gauge
        try {
            const gaugeCanvas = document.getElementById('soilGauge');
            if (!gaugeCanvas) throw new Error('soilGauge canvas not found');
            const ctx = gaugeCanvas.getContext('2d');
            const sm = {{ $sensor['soil_moisture'] }};
            const angle = (sm / 100) * 180;
            const w = gaugeCanvas.width, h = gaugeCanvas.height;
            const cx = w/2, cy = h * 0.65, r = 68;

            // Background arc
            ctx.beginPath();
            ctx.arc(cx, cy, r, Math.PI, 2 * Math.PI);
            ctx.strokeStyle = '#3b3b3b';
            ctx.lineWidth = 18;
            ctx.lineCap = 'round';
            ctx.stroke();

            // Value arc
            const endAngle = Math.PI + (angle * Math.PI / 180);
            const gradient = ctx.createLinearGradient(0, cy-r, 0, cy+r);
            if (sm < 40) {
                gradient.addColorStop(0, '#f4b740');
                gradient.addColorStop(1, '#ef4444');
            } else {
                gradient.addColorStop(0, '#43c766');
                gradient.addColorStop(1, '#2d9650');
            }
            ctx.beginPath();
            ctx.arc(cx, cy, r, Math.PI, endAngle);
            ctx.strokeStyle = gradient;
            ctx.lineWidth = 18;
            ctx.lineCap = 'round';
            ctx.stroke();
        } catch (e) {
            console.warn('Gauge render skipped:', e.message);
        }

        // Sensor chart
        try {
            const chartCanvas = document.getElementById('sensorChart');
            if (!chartCanvas || typeof Chart === 'undefined') throw new Error('Chart.js or canvas not available');
            new Chart(chartCanvas, {
                type: 'line',
                data: {
                    labels: ['09:00', '09:15', '09:30', '09:45', '10:00'],
                    datasets: [
                        { label: 'Suhu (°C)', data: [30.1, 30.5, 30.8, 31.2, 31.5], borderColor: '#43c766', backgroundColor: 'transparent', tension: 0.3, pointRadius: 3 },
                        { label: 'Kelemb. Udara (%)', data: [74, 73, 72, 71, 70], borderColor: '#e6f5ea', backgroundColor: 'transparent', tension: 0.3, pointRadius: 3 },
                        { label: 'Kelemb. Tanah (%)', data: [48, 45, 42, 38, 35], borderColor: '#f4b740', backgroundColor: 'transparent', tension: 0.3, pointRadius: 3 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { labels: { color: '#999', font: { size: 12 } } }
                    },
                    scales: {
                        x: { ticks: { color: '#999', font: { size: 11 } }, grid: { color: '#3b3b3b' } },
                        y: { beginAtZero: true, max: 100, ticks: { color: '#999', font: { size: 11 } }, grid: { color: '#3b3b3b' } }
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
