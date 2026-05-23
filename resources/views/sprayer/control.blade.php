<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Kontrol Sprayer</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">{{ $device['name'] }} — Kontrol manual & mode otomatis</div>
        </div>
        @php
            $cs = $sensor['condition_status'] ?? 'normal';
            $pillClass = match($cs) {
                'kritis'  => 'status-pill-kritis',
                'waspada' => 'status-pill-waspada',
                default   => 'status-pill-normal',
            };
        @endphp
        <span class="ml-auto status-pill {{ $pillClass }}">{{ $cs }}</span>
    </x-slot>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

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
                <div class="p-5 space-y-3">
                    @php $isAuto = $device['mode'] === 'automatic'; @endphp

                    <button type="button"
                            onclick="alert('Set mode: otomatis (backend)')"
                            class="w-full text-left p-4 rounded-lg border transition-colors
                                   {{ $isAuto ? 'border-[color:var(--color-brand)] bg-[rgba(30,215,96,0.08)]' : 'border-[color:var(--color-bg-elevated)] bg-[color:var(--color-bg-elevated)] hover:bg-[color:var(--color-bg-card-2)]' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $isAuto ? '' : 'opacity-30' }}" style="background: var(--color-brand);"></span>
                            <span class="text-xs uppercase tracking-wider font-bold {{ $isAuto ? 'text-[color:var(--color-brand)]' : 'text-[color:var(--color-text-muted)]' }}">Otomatis</span>
                        </div>
                        <div class="font-extrabold mt-1 text-[color:var(--color-text)]">Berbasis Sensor</div>
                        <p class="text-xs text-[color:var(--color-text-muted)] mt-1.5 leading-relaxed">Sprayer aktif bila tanah kering &amp; tidak hujan, berdasarkan threshold.</p>
                    </button>

                    <button type="button"
                            onclick="alert('Set mode: manual (backend)')"
                            class="w-full text-left p-4 rounded-lg border transition-colors
                                   {{ !$isAuto ? 'border-[color:var(--color-info)] bg-[rgba(83,157,245,0.08)]' : 'border-[color:var(--color-bg-elevated)] bg-[color:var(--color-bg-elevated)] hover:bg-[color:var(--color-bg-card-2)]' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ !$isAuto ? '' : 'opacity-30' }}" style="background: var(--color-info);"></span>
                            <span class="text-xs uppercase tracking-wider font-bold {{ !$isAuto ? 'text-[color:var(--color-info)]' : 'text-[color:var(--color-text-muted)]' }}">Manual</span>
                        </div>
                        <div class="font-extrabold mt-1 text-[color:var(--color-text)]">Kontrol Langsung</div>
                        <p class="text-xs text-[color:var(--color-text-muted)] mt-1.5 leading-relaxed">Petani menyalakan/matikan sprayer langsung dari tombol.</p>
                    </button>
                </div>
            </div>

            {{-- Pompa Sprayer big control (2/3) --}}
            <div class="card lg:col-span-2">
                <div class="card-header">
                    Pompa Sprayer
                    <span class="ml-auto text-xs text-[color:var(--color-text-muted)]">Aksi dikirim ke perangkat</span>
                </div>
                <div class="p-8 text-center">
                    @php $on = $sensor['sprayer_status'] === 'on'; @endphp

                    <button type="button"
                            onclick="alert('Toggle sprayer (backend)')"
                            class="btn-circle mx-auto {{ $on ? 'is-active' : '' }}"
                            style="width:9rem;height:9rem;"
                            aria-label="Toggle sprayer">
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
                    </button>

                    <div class="text-3xl font-black uppercase mt-5"
                         style="color: {{ $on ? 'var(--color-brand)' : 'var(--color-text-near)' }};">
                        {{ $sensor['sprayer_status'] }}
                    </div>
                    <div class="text-xs text-[color:var(--color-text-muted)] mt-1">
                        {{ $on ? 'Sprayer sedang aktif' : 'Sprayer tidak aktif' }}
                    </div>

                    <div class="flex gap-3 justify-center mt-6 flex-wrap">
                        <button class="btn-primary" {{ $on ? 'disabled' : '' }} onclick="alert('Nyalakan sprayer (backend)')">
                            Nyalakan
                        </button>
                        <button class="btn-secondary" {{ !$on ? 'disabled' : '' }} onclick="alert('Matikan sprayer (backend)')">
                            Matikan
                        </button>
                    </div>

                    <p class="text-xs text-[color:var(--color-text-muted)] mt-6 max-w-md mx-auto leading-relaxed">
                        Pada mode <span class="font-bold">otomatis</span>, sistem mengabaikan klik bila tanah cukup lembap atau sedang hujan.
                    </p>
                </div>
            </div>
        </div>

        {{-- Sensor snapshot --}}
        <div class="card">
            <div class="card-header">
                Snapshot Sensor
                <span class="ml-auto text-xs text-[color:var(--color-text-muted)]">{{ $sensor['recorded_at'] }}</span>
            </div>
            <div class="p-5 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-[color:var(--color-bg-elevated)] rounded-lg p-4">
                    <div class="text-[11px] uppercase tracking-wider font-bold text-[color:var(--color-text-muted)]">Suhu</div>
                    <div class="mt-1 text-2xl font-black text-[color:var(--color-text)]">{{ $sensor['temperature'] }}°C</div>
                </div>
                <div class="bg-[color:var(--color-bg-elevated)] rounded-lg p-4">
                    <div class="text-[11px] uppercase tracking-wider font-bold text-[color:var(--color-text-muted)]">Kelemb. Udara</div>
                    <div class="mt-1 text-2xl font-black text-[color:var(--color-text)]">{{ $sensor['air_humidity'] }}%</div>
                </div>
                <div class="bg-[color:var(--color-bg-elevated)] rounded-lg p-4">
                    <div class="text-[11px] uppercase tracking-wider font-bold text-[color:var(--color-text-muted)]">Kelemb. Tanah</div>
                    <div class="mt-1 text-2xl font-black"
                         style="color: {{ $sensor['soil_moisture'] < 40 ? 'var(--color-warning)' : 'var(--color-text)' }};">
                        {{ $sensor['soil_moisture'] }}%
                    </div>
                </div>
                <div class="bg-[color:var(--color-bg-elevated)] rounded-lg p-4">
                    <div class="text-[11px] uppercase tracking-wider font-bold text-[color:var(--color-text-muted)]">Hujan</div>
                    <div class="mt-1 text-2xl font-black"
                         style="color: {{ ($sensor['rain_status'] ?? '') === 'rain' ? 'var(--color-info)' : 'var(--color-text)' }};">
                        {{ ($sensor['rain_status'] ?? '') === 'rain' ? 'Hujan' : 'Cerah' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="card overflow-hidden">
            <div class="card-header">
                Aktivitas Terbaru
                <a href="{{ route('history.spray') }}" class="ml-auto text-xs font-bold uppercase tracking-wider text-[color:var(--color-brand)] hover:underline">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Trigger</th>
                            <th>Status</th>
                            <th>Alasan</th>
                            <th>Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td class="text-xs">{{ $log['time'] }}</td>
                            <td>
                                <span class="badge badge-{{ $log['trigger'] === 'automatic' ? 'automatic' : 'manual' }}">
                                    {{ ucfirst($log['trigger']) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $log['status'] === 'on' ? 'on' : 'off' }}">
                                    {{ strtoupper($log['status']) }}
                                </span>
                            </td>
                            <td class="text-xs text-[color:var(--color-text-muted)]">{{ $log['reason'] }}</td>
                            <td class="text-xs">{{ $log['by'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
