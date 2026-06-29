<x-app-layout>
    @php
        $cs = $sensor['condition_status'] ?? 'normal';
        $soilMoisture = $sensor['soil_moisture'];
        $temperature = $sensor['temperature'];
        $recordedAt = $sensor['recorded_at'] ?? null;
        $soilRaw = $sensor['soil_raw'] ?? null;
        $rainRaw = $sensor['rain_raw'] ?? null;
        $isSimulation = (bool) ($sensor['simulation_mode'] ?? false);
        $minSoilMoisture = $thresholds['min_soil_moisture'] ?? null;
        $maxTemperature = $thresholds['max_temperature'] ?? null;
        $soilThresholdLabel = $minSoilMoisture !== null ? rtrim(rtrim(number_format((float) $minSoilMoisture, 1, '.', ''), '0'), '.') : '-';
        $temperatureThresholdLabel = $maxTemperature !== null ? rtrim(rtrim(number_format((float) $maxTemperature, 1, '.', ''), '0'), '.') : '-';
        $soilValueLabel = $soilMoisture !== null ? rtrim(rtrim(number_format((float) $soilMoisture, 1, '.', ''), '0'), '.').'%' : '-';
        $temperatureLabel = $temperature !== null ? rtrim(rtrim(number_format((float) $temperature, 1, '.', ''), '0'), '.').'°C' : '-';
        $soilRawLabel = $soilRaw !== null ? (string) $soilRaw : '-';
        $rainRawLabel = $rainRaw !== null ? (string) $rainRaw : '-';
        $sourceModeLabel = $isSimulation ? 'Simulasi ESP32' : 'Hardware real';
        $soilConditionLabel = $soilMoisture === null
            ? 'Menunggu data'
            : ($minSoilMoisture !== null && $soilMoisture < $minSoilMoisture ? 'Kering' : 'Cukup Lembab');
        $pillClass = match($cs) {
            'kritis' => 'status-pill-kritis',
            'waspada' => 'status-pill-waspada',
            default => 'status-pill-normal',
        };
    @endphp

    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Dashboard</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">{{ $device['name'] }} — Monitoring kondisi real-time</div>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider"
                  style="background: {{ $isSimulation ? 'rgba(255,164,43,0.14)' : 'rgba(30,215,96,0.14)' }}; color: {{ $isSimulation ? 'var(--color-warning)' : 'var(--color-brand)' }};"
                  data-realtime="source-mode">{{ $sourceModeLabel }}</span>
            <span class="status-pill {{ $pillClass }}" data-realtime="condition-pill">{{ $cs }}</span>
        </div>
    </x-slot>

    <div class="p-4 space-y-3">

        {{-- ROW 1: Key metric tiles --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">

            {{-- Status Kondisi --}}
            <div class="card flex flex-col">
                <div class="card-header py-2">Status Kondisi</div>
                <div class="p-3 flex-1 flex flex-col justify-center gap-2">
                    <div class="rounded-lg py-3 text-center"
                         style="background: {{ $cs === 'kritis' ? 'rgba(243,114,127,0.12)' : ($cs === 'waspada' ? 'rgba(255,164,43,0.12)' : 'rgba(30,215,96,0.12)') }};">
                        <div class="text-3xl font-black uppercase leading-none"
                             style="color: {{ $cs === 'kritis' ? 'var(--color-negative)' : ($cs === 'waspada' ? 'var(--color-warning)' : 'var(--color-brand)') }};"
                             data-realtime="condition-status">
                            {{ $cs }}
                            </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-[color:var(--color-bg-elevated)] rounded-lg px-2 py-2 text-center">
                            <div class="text-[10px] uppercase tracking-wider font-bold text-[color:var(--color-text-muted)]">Mode</div>
                            <div class="text-sm font-extrabold uppercase mt-0.5 text-[color:var(--color-text)]" data-realtime="device-mode">{{ $device['mode'] }}</div>
                        </div>
                        <div class="bg-[color:var(--color-bg-elevated)] rounded-lg px-2 py-2 text-center">
                            <div class="text-[10px] uppercase tracking-wider font-bold text-[color:var(--color-text-muted)]">Sprayer</div>
                            <div class="text-sm font-extrabold uppercase mt-0.5"
                                 style="color: {{ $sensor['sprayer_status'] === 'on' ? 'var(--color-brand)' : 'var(--color-border-light)' }};"
                                 data-realtime="sprayer-status">{{ $sensor['sprayer_status'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kelembapan Tanah (gauge) --}}
            <div class="card flex flex-col">
                <div class="card-header py-2">Kelembapan Tanah</div>
                <div class="p-3 flex-1 flex flex-col items-center justify-center text-center">
                    <canvas id="soilGauge" width="150" height="100" class="mx-auto"></canvas>
                    <div class="text-2xl font-extrabold text-[color:var(--color-text)] -mt-2" id="soilValue">{{ $soilValueLabel }}</div>
                    <div class="text-[color:var(--color-text-muted)] text-[11px] mt-0.5" data-realtime="soil-condition">
                        {{ $soilConditionLabel }} · min. {{ $soilThresholdLabel }}%
                    </div>
                    <div class="text-[10px] text-[color:var(--color-text-muted)] mt-0.5">ADC tanah: <span class="font-mono" data-realtime="soil-raw">{{ $soilRawLabel }}</span></div>
                </div>
            </div>

            {{-- Suhu Udara --}}
            <div class="card flex flex-col">
                <div class="card-header py-2">Suhu Udara</div>
                <div class="p-3 flex-1 flex flex-col items-center justify-center text-center">
                    <div class="text-[color:var(--color-text-muted)] text-[10px] uppercase tracking-wider font-bold">Sensor DHT22</div>
                    <div class="text-4xl font-extrabold mt-1"
                         style="color: {{ $temperature !== null && $maxTemperature !== null && $temperature > $maxTemperature ? 'var(--color-warning)' : 'var(--color-brand)' }};"
                         data-realtime="temperature">{{ $temperatureLabel }}</div>
                    <div class="text-[color:var(--color-text-muted)] text-[11px] mt-1">Max threshold: {{ $temperatureThresholdLabel }}°C</div>
                </div>
            </div>

            {{-- Status Hujan --}}
            @php($isRain = ($sensor['rain_status'] ?? '') === 'rain')
            <div class="card flex flex-col">
                <div class="card-header py-2">Status Hujan</div>
                <div class="p-3 flex-1 flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-full bg-[color:var(--color-bg-elevated)] grid place-items-center mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: {{ $isRain ? 'var(--color-info)' : 'var(--color-text-near)' }}">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isRain ? 'M3 15a4 4 0 014-4h.7A6 6 0 0119 11.5a3.5 3.5 0 010 7H7a4 4 0 01-4-3.5zM8 19l-1 3M12 19l-1 3M16 19l-1 3' : 'M3 15a4 4 0 014-4h.7A6 6 0 0119 11.5a3.5 3.5 0 010 7H7a4 4 0 01-4-3.5z' }}"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-extrabold leading-tight" style="color: {{ $isRain ? 'var(--color-info)' : 'var(--color-text)' }}" data-realtime="rain-title">{{ $isRain ? 'Hujan' : 'Tidak Hujan' }}</div>
                    <div class="text-[color:var(--color-text-muted)] text-[11px] mt-1 leading-snug" data-realtime="rain-description">{{ $isRain ? 'Sprayer otomatis dimatikan.' : 'Sprayer diizinkan bila tanah kering.' }}</div>
                    <div class="text-[10px] text-[color:var(--color-text-muted)] mt-1">ADC hujan: <span class="font-mono" data-realtime="rain-raw">{{ $rainRawLabel }}</span></div>
                </div>
            </div>

        </div>

        {{-- ROW 2: Notification --}}
        <div class="grid grid-cols-1 gap-3">

            {{-- Notification Status --}}
            <div class="card flex flex-col">
                <div class="card-header py-2">Notifikasi WhatsApp</div>
                <div class="p-3 flex-1 flex items-center gap-3">
                    <div class="w-12 h-12 shrink-0 rounded-full bg-[color:var(--color-bg-elevated)] grid place-items-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--color-brand)">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-lg font-extrabold leading-tight" style="color: var(--color-brand)">Aktif</div>
                        <div class="text-[color:var(--color-text-muted)] text-[11px] leading-snug">
                            <span data-realtime="last-update">{{ $recordedAt !== null ? 'Update sensor terakhir '.$recordedAt.'.' : 'Menunggu data sensor pertama masuk.' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 3: Chart + Control Panel --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">

            {{-- Chart --}}
            <div class="card flex flex-col">
                <div class="card-header py-2">
                    Grafik Sensor Real-Time
                    <span class="ml-auto text-[color:var(--color-text-muted)] text-xs font-bold uppercase tracking-wider">60 Menit Terakhir</span>
                </div>
                <div class="p-3 flex-1">
                    <canvas id="sensorChart" height="200" class="w-full"></canvas>
                </div>
            </div>

            {{-- Control Panel --}}
            <div class="card flex flex-col">
                <div class="card-header py-2">
                    Kontrol Penyemprotan
                    <a href="{{ route('sprayer.control') }}" class="ml-auto text-xs font-bold uppercase tracking-wider text-[color:var(--color-brand)] hover:underline">Detail</a>
                </div>
                <div class="p-3 flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">

                    <div class="bg-[color:var(--color-bg-elevated)] rounded-lg p-4 flex flex-col items-center justify-center text-center">
                        <div class="text-[color:var(--color-text-muted)] text-[10px] uppercase tracking-wider font-bold mb-2">Mode</div>
                        <div class="text-base font-extrabold uppercase mb-3"
                             style="color: {{ $device['mode'] === 'automatic' ? 'var(--color-brand)' : 'var(--color-text-near)' }};"
                             data-realtime="device-mode">{{ $device['mode'] }}</div>
                        <form method="POST" action="{{ route('sprayer.mode.update') }}" class="inline" x-data="{ loading: false }" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="mode" value="{{ $device['mode'] === 'automatic' ? 'manual' : 'automatic' }}">
                            <button type="submit" class="btn-outline btn-sm" :disabled="loading">
                                <span x-show="!loading">Ganti ke {{ $device['mode'] === 'automatic' ? 'Manual' : 'Otomatis' }}</span>
                                <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    Loading...
                                </span>
                            </button>
                        </form>
                    </div>

                    <div class="bg-[color:var(--color-bg-elevated)] rounded-lg p-4 flex flex-col items-center justify-center text-center">
                        <div class="text-[color:var(--color-text-muted)] text-[10px] uppercase tracking-wider font-bold mb-2">Pompa Sprayer</div>
                        @if($device['mode'] === 'automatic')
                            <div class="btn-circle btn-circle-lg mx-auto opacity-40 cursor-not-allowed {{ $sensor['sprayer_status'] === 'on' ? 'is-active' : '' }}" aria-disabled="true">
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
                            </div>
                            <p class="text-[11px] text-[color:var(--color-text-muted)] mt-2">Mode otomatis aktif.<br>Ubah ke manual untuk kontrol langsung.</p>
                        @else
                            <form method="POST" action="{{ route('sprayer.status.update') }}" x-data="{ loading: false }" @submit="loading = true">
                                @csrf
                                <input type="hidden" name="status" value="{{ $sensor['sprayer_status'] === 'on' ? 'off' : 'on' }}">
                                <button type="submit"
                                        class="btn-circle btn-circle-lg mx-auto {{ $sensor['sprayer_status'] === 'on' ? 'is-active' : '' }}"
                                        :disabled="loading"
                                        aria-label="Toggle sprayer">
                                    <template x-if="!loading">
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
                                    </template>
                                    <svg x-show="loading" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                        <div class="text-base font-extrabold uppercase mt-2"
                             style="color: {{ $sensor['sprayer_status'] === 'on' ? 'var(--color-brand)' : 'var(--color-text-near)' }};"
                             data-realtime="sprayer-status">{{ $sensor['sprayer_status'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 4: Recent Activity --}}
        <div class="card">
            <div class="card-header py-2">
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
                        @forelse($activities as $activity)
                            <tr>
                                <td class="text-xs">{{ $activity['time'] }}</td>
                                <td>{{ $activity['type'] }}</td>
                                <td><span class="badge badge-{{ $activity['status_key'] }}">{{ $activity['status'] }}</span></td>
                                <td class="text-[color:var(--color-text-muted)] text-xs">{{ $activity['description'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-sm text-[color:var(--color-text-muted)] py-6">
                                    Belum ada data aktivitas perangkat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
    window.smartSprayerDashboardState = window.smartSprayerDashboardState ?? {
        chartInstance: null,
        themeObserver: null,
        pollingInterval: null,
        listenersBound: false,
        sensor: @json($sensor),
        thresholds: @json($thresholds),
        chart: @json($chart),
        latestEndpoint: @json(route('dashboard.latest')),
    };

    function cleanupDashboard() {
        const state = window.smartSprayerDashboardState;

        if (state.chartInstance) {
            state.chartInstance.destroy();
            state.chartInstance = null;
        }

        if (state.themeObserver) {
            state.themeObserver.disconnect();
            state.themeObserver = null;
        }

        if (state.pollingInterval) {
            clearInterval(state.pollingInterval);
            state.pollingInterval = null;
        }
    }

    function initDashboard() {
        const state = window.smartSprayerDashboardState;
        state.sensor = state.sensor ?? @json($sensor);
        state.thresholds = state.thresholds ?? @json($thresholds);
        state.chart = state.chart ?? @json($chart);

        const themeColor = (name, fallback) =>
            (getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback);
        const formatNumber = (value, suffix = '') => {
            if (value === null || value === undefined || value === '') return '-';
            const number = Number(value);
            return Number.isInteger(number) ? `${number}${suffix}` : `${number.toFixed(1).replace(/\.0$/, '')}${suffix}`;
        };
        const setText = (selector, value) => document.querySelectorAll(selector).forEach((el) => { el.textContent = value; });

        function renderGauge() {
            const gaugeCanvas = document.getElementById('soilGauge');
            if (!gaugeCanvas) return;
            const ctx = gaugeCanvas.getContext('2d');
            const sm = Number(state.sensor?.soil_moisture ?? 0);
            const soilThreshold = Number(state.thresholds?.min_soil_moisture ?? 0);
            const angle = (sm / 100) * 180;
            const w = gaugeCanvas.width;
            const h = gaugeCanvas.height;
            const cx = w / 2;
            const cy = h - 10;
            const r = 52;

            ctx.clearRect(0, 0, w, h);

            ctx.beginPath();
            ctx.arc(cx, cy, r, Math.PI, 2 * Math.PI);
            ctx.strokeStyle = themeColor('--color-bg-elevated', '#1f1f1f');
            ctx.lineWidth = 18;
            ctx.lineCap = 'round';
            ctx.stroke();

            const endAngle = Math.PI + (angle * Math.PI / 180);
            ctx.beginPath();
            ctx.arc(cx, cy, r, Math.PI, endAngle);
            ctx.strokeStyle = soilThreshold > 0 && sm < soilThreshold
                ? themeColor('--color-warning', '#ffa42b')
                : themeColor('--color-brand', '#1ed760');
            ctx.lineWidth = 18;
            ctx.lineCap = 'round';
            ctx.stroke();
        }

        function renderChart() {
            const chartCanvas = document.getElementById('sensorChart');
            if (!chartCanvas || typeof Chart === 'undefined') return;

            state.chartInstance?.destroy();
            Chart.getChart(chartCanvas)?.destroy();

            state.chartInstance = new Chart(chartCanvas, {
                type: 'line',
                data: {
                    labels: state.chart?.labels ?? [],
                    datasets: [
                        { label: 'Suhu (°C)', data: state.chart?.temperature ?? [], borderColor: themeColor('--color-brand', '#1ed760'), backgroundColor: 'transparent', tension: 0.3, pointRadius: 3 },
                        { label: 'Kelemb. Tanah (%)', data: state.chart?.soil_moisture ?? [], borderColor: themeColor('--color-warning', '#ffa42b'), backgroundColor: 'transparent', tension: 0.3, pointRadius: 3 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    animation: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: themeColor('--color-text-muted', '#b3b3b3'),
                                font: { size: 12, weight: 600 }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: themeColor('--color-text-muted', '#b3b3b3'), font: { size: 11 } },
                            grid: { color: themeColor('--color-bg-card-2', '#252525') }
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { color: themeColor('--color-text-muted', '#b3b3b3'), font: { size: 11 } },
                            grid: { color: themeColor('--color-bg-card-2', '#252525') }
                        }
                    }
                }
            });
        }

        function updateChart() {
            if (!state.chartInstance) return;
            state.chartInstance.data.labels = state.chart?.labels ?? [];
            state.chartInstance.data.datasets[0].data = state.chart?.temperature ?? [];
            state.chartInstance.data.datasets[1].data = state.chart?.soil_moisture ?? [];
            state.chartInstance.update('none');
        }

        function updateRealtimeView(payload) {
            state.sensor = payload.sensor ?? state.sensor;
            state.thresholds = payload.thresholds ?? state.thresholds;
            state.chart = payload.chart ?? state.chart;

            const sensor = state.sensor ?? {};
            const device = payload.device ?? {};
            const rainStatus = sensor.rain_status === 'rain';
            const sourceMode = sensor.simulation_mode ? 'Simulasi ESP32' : 'Hardware real';
            const soilThreshold = Number(state.thresholds?.min_soil_moisture ?? 0);
            const soilThresholdLabel = soilThreshold > 0 ? String(soilThreshold).replace(/\.0$/, '') : '-';
            const soilMoistureVal = sensor.soil_moisture;
            const soilCondition = (soilMoistureVal === null || soilMoistureVal === undefined)
                ? 'Menunggu data'
                : (soilThreshold > 0 && Number(soilMoistureVal) < soilThreshold ? 'Kering' : 'Cukup Lembab');

            setText('#soilValue', formatNumber(sensor.soil_moisture, '%'));
            setText('[data-realtime="soil-raw"]', sensor.soil_raw ?? '-');
            setText('[data-realtime="rain-raw"]', sensor.rain_raw ?? '-');
            setText('[data-realtime="soil-condition"]', `${soilCondition} · min. ${soilThresholdLabel}%`);
            setText('[data-realtime="source-mode"]', sourceMode);
            setText('[data-realtime="condition-status"]', (sensor.condition_status ?? 'normal').toUpperCase());
            setText('[data-realtime="condition-pill"]', sensor.condition_status ?? 'normal');
            setText('[data-realtime="device-mode"]', device.mode ?? '-');
            setText('[data-realtime="sprayer-status"]', sensor.sprayer_status ?? '-');
            setText('[data-realtime="temperature"]', formatNumber(sensor.temperature, '°C'));
            setText('[data-realtime="rain-title"]', rainStatus ? 'Hujan' : 'Tidak Hujan');
            setText('[data-realtime="rain-description"]', rainStatus ? 'Penyemprotan otomatis tidak dijalankan.' : 'Penyemprotan otomatis diizinkan bila tanah kering.');
            setText('[data-realtime="last-update"]', sensor.recorded_at ? `Update sensor terakhir ${sensor.recorded_at}.` : 'Menunggu data sensor pertama masuk.');

            renderGauge();
            updateChart();
        }

        function pollLatestData() {
            fetch(state.latestEndpoint, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then((response) => response.ok ? response.json() : Promise.reject(new Error(`HTTP ${response.status}`)))
                .then(updateRealtimeView)
                .catch((error) => console.warn('Realtime dashboard polling failed:', error.message));
        }

        try { renderGauge(); } catch (e) { console.warn('Gauge render skipped:', e.message); }
        try { renderChart(); } catch (e) { console.warn('Chart render skipped:', e.message); }

        if (!state.pollingInterval) {
            state.pollingInterval = setInterval(pollLatestData, 2000);
            pollLatestData();
        }

        // Re-render canvases when the theme class on <html> changes.
        state.themeObserver?.disconnect();
        state.themeObserver = new MutationObserver(function () {
            try { renderGauge(); } catch (e) {}
            try { renderChart(); } catch (e) {}
        });
        state.themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    if (! window.smartSprayerDashboardState.listenersBound) {
        document.addEventListener('turbo:load', initDashboard);
        window.addEventListener('turbo:page-ready', initDashboard);
        document.addEventListener('turbo:before-cache', cleanupDashboard);
        window.smartSprayerDashboardState.listenersBound = true;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard, { once: true });
    } else {
        initDashboard();
    }
    </script>
    @endpush
</x-app-layout>
