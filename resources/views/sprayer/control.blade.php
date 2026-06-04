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
    @endphp

    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Kontrol Sprayer</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">{{ $device['name'] }} — Kontrol manual & mode otomatis</div>
        </div>
        <span class="ml-auto status-pill {{ $pillClass }}">{{ $cs }}</span>
    </x-slot>

    <div class=" p-6 space-y-6">

        {{-- Rain warning --}}
        @if(($sensor['rain_status'] ?? '') === 'rain')
            <div class="alert alert-info">
                <span class="font-bold">Hujan terdeteksi.</span>
                Penyemprotan otomatis tidak akan dijalankan selama kondisi hujan.
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Mode (1/3) --}}
            <div class="card lg:col-span-1">
                <div class="card-header">Mode Operasi</div>
                <div class="p-5 space-y-3" x-data="{ loading: false }">
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
                <div class="card-header">
                    Pompa Sprayer
                    <span class="ml-auto text-xs text-[color:var(--color-text-muted)]">Aksi dikirim ke perangkat</span>
                </div>
                <div class="p-8 text-center">
                    <div class="btn-circle mx-auto {{ $on ? 'is-active' : '' }}"
                         style="width:9rem;height:9rem;"
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
                         style="color: {{ $on ? 'var(--color-brand)' : 'var(--color-text-near)' }};">
                        {{ $sensor['sprayer_status'] }}
                    </div>
                    <div class="text-xs text-[color:var(--color-text-muted)] mt-1">
                        {{ $on ? 'Sprayer sedang aktif' : 'Sprayer tidak aktif' }}
                    </div>

                    <div class="flex gap-3 justify-center mt-6 flex-wrap" x-data="{ loading: false }">
                        <form method="POST" action="{{ route('sprayer.status.update') }}" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="status" value="on">
                            <button type="submit" class="btn-primary" :disabled="loading || {{ ($on || $isAuto) ? 'true' : 'false' }}">
                                <span x-show="!loading">Nyalakan</span>
                                <span x-show="loading" x-cloak>…</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('sprayer.status.update') }}" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="status" value="off">
                            <button type="submit" class="btn-secondary" :disabled="loading || {{ (!$on || $isAuto) ? 'true' : 'false' }}">
                                <span x-show="!loading">Matikan</span>
                                <span x-show="loading" x-cloak>…</span>
                            </button>
                        </form>
                    </div>

                    <p class="text-xs text-[color:var(--color-text-muted)] mt-6 max-w-md mx-auto leading-relaxed">
                        Pada mode <span class="font-bold">otomatis</span>, kontrol manual dikunci. Ubah ke mode manual untuk menyalakan atau mematikan sprayer dari web.
                    </p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Data Raw ESP32
                <span class="ml-auto text-xs text-[color:var(--color-text-muted)]">Debug sensor perangkat</span>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-xl bg-[color:var(--color-bg-elevated)] p-4">
                    <div class="text-[11px] uppercase tracking-widest font-bold text-[color:var(--color-text-muted)]">Soil Raw</div>
                    <div class="mt-2 text-2xl font-black font-mono text-[color:var(--color-text)]">{{ $soilRaw ?? '-' }}</div>
                    <div class="mt-1 text-xs text-[color:var(--color-text-muted)]">ADC tanah 0–4095</div>
                </div>
                <div class="rounded-xl bg-[color:var(--color-bg-elevated)] p-4">
                    <div class="text-[11px] uppercase tracking-widest font-bold text-[color:var(--color-text-muted)]">Rain Raw</div>
                    <div class="mt-2 text-2xl font-black font-mono text-[color:var(--color-text)]">{{ $rainRaw ?? '-' }}</div>
                    <div class="mt-1 text-xs text-[color:var(--color-text-muted)]">ADC hujan 0–4095</div>
                </div>
                <div class="rounded-xl bg-[color:var(--color-bg-elevated)] p-4">
                    <div class="text-[11px] uppercase tracking-widest font-bold text-[color:var(--color-text-muted)]">Sumber Data</div>
                    <div class="mt-2 text-lg font-black uppercase" style="color: {{ $isSimulation ? 'var(--color-warning)' : 'var(--color-brand)' }};">{{ $isSimulation ? 'Simulasi' : 'Hardware real' }}</div>
                    <div class="mt-1 text-xs text-[color:var(--color-text-muted)]">Dikirim dari ESP32</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
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
</x-app-layout>
