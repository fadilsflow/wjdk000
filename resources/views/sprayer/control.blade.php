<x-app-layout>
    @php
        $cs = $sensor['condition_status'] ?? 'normal';
        $pillClass = match($cs) {
            'kritis' => 'status-pill-kritis',
            'waspada' => 'status-pill-waspada',
            default => 'status-pill-normal',
        };
        $isAuto = $device['mode'] === 'automatic';
        $on = $sensor['sprayer_status'] === 'on';
        $soilRaw = $sensor['soil_raw'] ?? null;
        $rainRaw = $sensor['rain_raw'] ?? null;
        $isSimulation = (bool) ($sensor['simulation_mode'] ?? false);
        $minSoilMoisture = $thresholds['min_soil_moisture'] ?? null;
        $soilMoisture = $sensor['soil_moisture'] ?? null;
        $soilConditionLabel = $soilMoisture === null
            ? 'Menunggu data'
            : ($minSoilMoisture !== null && $soilMoisture < $minSoilMoisture ? 'Kering' : 'Cukup Lembab');
        $rainConditionLabel = ($sensor['rain_status'] ?? '') === 'rain' ? 'Hujan' : 'Tidak Hujan';
    @endphp

    {{-- Cegah Turbo menyajikan snapshot lama: halaman ini wajib selalu menampilkan status sprayer terbaru. --}}
    <x-slot name="head">
        <meta name="turbo-cache-control" content="no-cache">
    </x-slot>

    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Kontrol Sprayer</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">{{ $device['name'] }} — Kontrol manual & mode otomatis</div>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider"
                  style="background: {{ $isSimulation ? 'rgba(255,164,43,0.14)' : 'rgba(30,215,96,0.14)' }}; color: {{ $isSimulation ? 'var(--color-warning)' : 'var(--color-brand)' }};"
                  data-realtime="source-mode">{{ $isSimulation ? 'Simulasi ESP32' : 'Hardware real' }}</span>
            <span class="status-pill {{ $pillClass }}" data-realtime="condition-pill">{{ $cs }}</span>
        </div>
    </x-slot>

    <div class="p-4 space-y-3">

        {{-- Rain warning --}}
        <div class="alert alert-info" data-realtime="rain-warning" @class(['hidden' => ($sensor['rain_status'] ?? '') !== 'rain'])>
            <span class="font-bold">Hujan terdeteksi.</span>
            Penyemprotan otomatis tidak akan dijalankan selama kondisi hujan.
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3" data-sprayer-root>

            {{-- Mode (1/3) --}}
            <div class="card lg:col-span-1">
                <div class="card-header py-2">Mode Operasi</div>
                <div class="p-4 space-y-3" x-data="{ loading: false }">
                    <form method="POST" action="{{ route('sprayer.mode.update') }}" @submit="loading = true">
                        @csrf
                        <input type="hidden" name="mode" value="automatic">
                        <button type="submit" :disabled="loading"
                                class="w-full text-left p-4 rounded-lg border transition-colors
                                       {{ $isAuto ? 'border-[color:var(--color-brand)] bg-[rgba(30,215,96,0.08)]' : 'border-[color:var(--color-bg-elevated)] bg-[color:var(--color-bg-elevated)] hover:bg-[color:var(--color-bg-card-2)]' }}">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $isAuto ? '' : 'opacity-30' }}" style="background: var(--color-brand);"></span>
                                <span class="text-xs uppercase tracking-wider font-bold {{ $isAuto ? 'text-[color:var(--color-brand)]' : 'text-[color:var(--color-text-muted)]' }}">Otomatis</span>
                            </div>
                            <div class="font-extrabold mt-1 text-[color:var(--color-text)]">Berbasis Sensor</div>
                            <p class="text-xs text-[color:var(--color-text-muted)] mt-1.5 leading-relaxed">Sprayer aktif bila tanah kering &amp; tidak hujan, berdasarkan threshold.</p>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('sprayer.mode.update') }}" @submit="loading = true">
                        @csrf
                        <input type="hidden" name="mode" value="manual">
                        <button type="submit" :disabled="loading"
                                class="w-full text-left p-4 rounded-lg border transition-colors
                                       {{ !$isAuto ? 'border-[color:var(--color-info)] bg-[rgba(83,157,245,0.08)]' : 'border-[color:var(--color-bg-elevated)] bg-[color:var(--color-bg-elevated)] hover:bg-[color:var(--color-bg-card-2)]' }}">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ !$isAuto ? '' : 'opacity-30' }}" style="background: var(--color-info);"></span>
                                <span class="text-xs uppercase tracking-wider font-bold {{ !$isAuto ? 'text-[color:var(--color-info)]' : 'text-[color:var(--color-text-muted)]' }}">Manual</span>
                            </div>
                            <div class="font-extrabold mt-1 text-[color:var(--color-text)]">Kontrol Langsung</div>
                            <p class="text-xs text-[color:var(--color-text-muted)] mt-1.5 leading-relaxed">Petani menyalakan/matikan sprayer langsung dari tombol.</p>
                        </button>
                    </form>

                    <p x-show="loading" x-cloak class="text-xs text-center text-[color:var(--color-text-muted)] animate-pulse">Mengubah mode…</p>
                </div>
            </div>

            {{-- Pompa Sprayer big control (2/3) --}}
            <div class="card lg:col-span-2">
                <div class="card-header py-2">
                    Pompa Sprayer
                    <span class="ml-auto text-xs text-[color:var(--color-text-muted)]">Aksi dikirim ke perangkat</span>
                </div>
                <div class="p-6 text-center">
                    <div class="btn-circle mx-auto {{ $on ? 'is-active' : '' }}"
                         style="width:9rem;height:9rem;"
                         data-realtime="pump-icon"
                         data-on="{{ $on ? '1' : '0' }}"
                         aria-label="Status sprayer">
                        @if($on)
                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                                <rect x="6" y="5" width="4" height="14" rx="1"/>
                                <rect x="14" y="5" width="4" height="14" rx="1"/>
                            </svg>
                        @else
                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        @endif
                    </div>

                    <div class="text-3xl font-black uppercase mt-5"
                         style="color: {{ $on ? 'var(--color-brand)' : 'var(--color-text-near)' }};"
                         data-realtime="sprayer-status">
                        {{ $sensor['sprayer_status'] }}
                    </div>
                    <div class="text-xs text-[color:var(--color-text-muted)] mt-1" data-realtime="sprayer-caption">
                        {{ $on ? 'Sprayer sedang aktif' : 'Sprayer tidak aktif' }}
                    </div>

                    <div class="flex gap-3 justify-center mt-6 flex-wrap" x-data="{ loading: false }">
                        <form method="POST" action="{{ route('sprayer.status.update') }}" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="status" value="on">
                            <button type="submit" class="btn-primary" data-realtime="btn-on" :disabled="loading || {{ ($on || $isAuto) ? 'true' : 'false' }}">
                                <span x-show="!loading">Nyalakan</span>
                                <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    Loading...
                                </span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('sprayer.status.update') }}" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="status" value="off">
                            <button type="submit" class="btn-secondary" data-realtime="btn-off" :disabled="loading || {{ (!$on || $isAuto) ? 'true' : 'false' }}">
                                <span x-show="!loading">Matikan</span>
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

                    <p class="text-xs text-[color:var(--color-text-muted)] mt-6 max-w-md mx-auto leading-relaxed">
                        Pada mode <span class="font-bold">otomatis</span>, kontrol manual dikunci. Ubah ke mode manual untuk menyalakan atau mematikan sprayer dari web.
                    </p>
                </div>
            </div>
        </div>

        {{-- Kondisi sensor (gaya konsisten dengan dashboard) --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">

            {{-- Kondisi Tanah --}}
            <div class="card flex flex-col">
                <div class="card-header py-2">Kondisi Tanah</div>
                <div class="p-3 flex-1 flex items-center gap-3">
                    @php($soilDry = $soilConditionLabel === 'Kering')
                    <div class="w-12 h-12 shrink-0 rounded-full bg-[color:var(--color-bg-elevated)] grid place-items-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: {{ $soilDry ? 'var(--color-warning)' : 'var(--color-brand)' }}">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3C12 3 5 11 5 15a7 7 0 0014 0c0-4-7-12-7-12z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-lg font-extrabold leading-tight" style="color: {{ $soilDry ? 'var(--color-warning)' : 'var(--color-text)' }}" data-realtime="soil-condition">{{ $soilConditionLabel }}</div>
                        <div class="text-[color:var(--color-text-muted)] text-[11px] leading-snug">Threshold min. {{ $minSoilMoisture !== null ? rtrim(rtrim(number_format((float) $minSoilMoisture, 1, '.', ''), '0'), '.') : '-' }}%</div>
                        <div class="mt-1 text-[10px] font-bold text-[color:var(--color-text-muted)]">ADC tanah: <span class="font-mono text-[color:var(--color-text)]" data-realtime="soil-raw">{{ $soilRaw ?? '-' }}</span></div>
                    </div>
                </div>
            </div>

            {{-- Status Hujan --}}
            <div class="card flex flex-col">
                <div class="card-header py-2">Status Hujan</div>
                <div class="p-3 flex-1 flex items-center gap-3">
                    @php($isRain = ($sensor['rain_status'] ?? '') === 'rain')
                    <div class="w-12 h-12 shrink-0 rounded-full bg-[color:var(--color-bg-elevated)] grid place-items-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: {{ $isRain ? 'var(--color-info)' : 'var(--color-text-near)' }}">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isRain ? 'M3 15a4 4 0 014-4h.7A6 6 0 0119 11.5a3.5 3.5 0 010 7H7a4 4 0 01-4-3.5zM8 19l-1 3M12 19l-1 3M16 19l-1 3' : 'M3 15a4 4 0 014-4h.7A6 6 0 0119 11.5a3.5 3.5 0 010 7H7a4 4 0 01-4-3.5z' }}"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-lg font-extrabold leading-tight" style="color: {{ $isRain ? 'var(--color-info)' : 'var(--color-text)' }}" data-realtime="rain-condition">{{ $rainConditionLabel }}</div>
                        <div class="text-[color:var(--color-text-muted)] text-[11px] leading-snug">{{ $isRain ? 'Penyemprotan otomatis tidak dijalankan.' : 'Penyemprotan diizinkan bila tanah kering.' }}</div>
                        <div class="mt-1 text-[10px] font-bold text-[color:var(--color-text-muted)]">ADC hujan: <span class="font-mono text-[color:var(--color-text)]" data-realtime="rain-raw">{{ $rainRaw ?? '-' }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-2">
                Log Penyemprotan
                <span class="ml-auto text-xs text-[color:var(--color-text-muted)]">10 aktivitas terakhir</span>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Trigger</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-xs">{{ $log['time'] }}</td>
                                <td class="uppercase">{{ $log['trigger'] }}</td>
                                <td><span class="badge badge-{{ strtolower($log['status']) }}">{{ $log['status'] }}</span></td>
                                <td class="text-[color:var(--color-text-muted)] text-xs">{{ $log['reason'] }}</td>
                                <td class="text-xs">{{ $log['by'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-sm text-[color:var(--color-text-muted)] py-6">
                                    Belum ada log penyemprotan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    window.sprayerControlState = window.sprayerControlState ?? {
        pollingInterval: null,
        listenersBound: false,
        latestEndpoint: @json(route('sprayer.latest')),
    };

    function cleanupSprayer() {
        const state = window.sprayerControlState;
        if (state.pollingInterval) {
            clearInterval(state.pollingInterval);
            state.pollingInterval = null;
        }
    }

    function initSprayer() {
        if (! document.querySelector('[data-sprayer-root]')) return;
        const state = window.sprayerControlState;

        const setText = (sel, val) => document.querySelectorAll(sel).forEach((el) => { el.textContent = val; });
        const brand = 'var(--color-brand)';
        const nearText = 'var(--color-text-near)';

        const onIcon = '<svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>';
        const offIcon = '<svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>';

        function applyData(payload) {
            const sensor = payload.sensor ?? {};
            const device = payload.device ?? {};
            const thresholds = payload.thresholds ?? {};

            const status = sensor.sprayer_status ?? 'off';
            const isOn = status === 'on';
            const isAuto = (device.mode ?? 'automatic') === 'automatic';
            const isRain = sensor.rain_status === 'rain';
            const cond = sensor.condition_status ?? 'normal';
            const isSim = !!sensor.simulation_mode;

            const minSoil = Number(thresholds.min_soil_moisture ?? 0);
            const soil = sensor.soil_moisture;
            const soilCondition = (soil === null || soil === undefined)
                ? 'Menunggu data'
                : (minSoil > 0 && Number(soil) < minSoil ? 'Kering' : 'Cukup Lembab');

            // Condition pill
            document.querySelectorAll('[data-realtime="condition-pill"]').forEach((el) => {
                el.textContent = cond;
                el.classList.remove('status-pill-normal', 'status-pill-waspada', 'status-pill-kritis');
                el.classList.add(cond === 'kritis' ? 'status-pill-kritis' : (cond === 'waspada' ? 'status-pill-waspada' : 'status-pill-normal'));
            });

            // Source badge
            document.querySelectorAll('[data-realtime="source-mode"]').forEach((el) => {
                el.textContent = isSim ? 'Simulasi ESP32' : 'Hardware real';
                el.style.background = isSim ? 'rgba(255,164,43,0.14)' : 'rgba(30,215,96,0.14)';
                el.style.color = isSim ? 'var(--color-warning)' : brand;
            });

            // Rain warning
            document.querySelectorAll('[data-realtime="rain-warning"]').forEach((el) => {
                el.classList.toggle('hidden', !isRain);
            });

            // Detail sensor
            document.querySelectorAll('[data-realtime="soil-condition"]').forEach((el) => {
                el.textContent = soilCondition;
                el.style.color = soilCondition === 'Kering' ? 'var(--color-warning)' : 'var(--color-text)';
            });
            document.querySelectorAll('[data-realtime="rain-condition"]').forEach((el) => {
                el.textContent = isRain ? 'Hujan' : 'Tidak Hujan';
                el.style.color = isRain ? 'var(--color-info)' : 'var(--color-text)';
            });
            setText('[data-realtime="soil-raw"]', sensor.soil_raw ?? '-');
            setText('[data-realtime="rain-raw"]', sensor.rain_raw ?? '-');

            // Sprayer status text + caption + color
            document.querySelectorAll('[data-realtime="sprayer-status"]').forEach((el) => {
                el.textContent = status;
                el.style.color = isOn ? brand : nearText;
            });
            setText('[data-realtime="sprayer-caption"]', isOn ? 'Sprayer sedang aktif' : 'Sprayer tidak aktif');

            // Pump icon
            document.querySelectorAll('[data-realtime="pump-icon"]').forEach((el) => {
                if (el.dataset.on !== (isOn ? '1' : '0')) {
                    el.dataset.on = isOn ? '1' : '0';
                    el.innerHTML = isOn ? onIcon : offIcon;
                }
                el.classList.toggle('is-active', isOn);
            });

            // Button availability (respect mode + current status). Alpine still overrides while loading.
            document.querySelectorAll('[data-realtime="btn-on"]').forEach((el) => {
                el.disabled = isAuto || isOn;
            });
            document.querySelectorAll('[data-realtime="btn-off"]').forEach((el) => {
                el.disabled = isAuto || !isOn;
            });
        }

        function poll() {
            fetch(state.latestEndpoint, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then((r) => r.ok ? r.json() : Promise.reject(new Error('HTTP ' + r.status)))
                .then(applyData)
                .catch((e) => console.warn('Sprayer polling failed:', e.message));
        }

        if (!state.pollingInterval) {
            state.pollingInterval = setInterval(poll, 2000);
            poll();
        }
    }

    if (! window.sprayerControlState.listenersBound) {
        document.addEventListener('turbo:load', initSprayer);
        window.addEventListener('turbo:page-ready', initSprayer);
        document.addEventListener('turbo:before-cache', cleanupSprayer);
        window.sprayerControlState.listenersBound = true;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSprayer, { once: true });
    } else {
        initSprayer();
    }
    </script>
    @endpush
</x-app-layout>
