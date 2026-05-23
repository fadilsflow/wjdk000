<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-xl font-extrabold">Kontrol Sprayer</div>
            <div class="text-[#999] text-sm">{{ $device['name'] ?? 'Sprayer Lahan A' }}</div>
        </div>
        @php
            $statusClass = match($device['mode'] ?? 'manual') {
                'automatic' => 'status-pill-normal',
                default => 'status-pill-waspada',
            };
            $statusLabel = match($device['mode'] ?? 'manual') {
                'automatic' => 'Mode Otomatis',
                default => 'Mode Manual',
            };
        @endphp
        <span class="ml-auto {{ $statusClass }}">{{ $statusLabel }}</span>
    </x-slot>

    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- LEFT: Kontrol --}}
        <div class="space-y-6">

            {{-- Mode toggle --}}
            <div class="card">
                <div class="card-header">
                    Mode Penyemprotan
                </div>
                <div class="p-6 flex flex-col items-center gap-4">
                    <div class="text-[#999] text-sm">Atur mode penyemprotan</div>
                    <div class="w-[118px] h-[64px] rounded-full relative transition-colors
                        {{ $device['mode'] === 'automatic' ? 'bg-[#43c766]' : 'bg-[#626866]' }}">
                        <div class="w-[47px] h-[47px] bg-white rounded-full absolute top-[8px]
                            {{ $device['mode'] === 'automatic' ? 'right-[9px]' : 'left-[9px]' }}">
                        </div>
                    </div>
                    <div class="text-lg font-extrabold {{ $device['mode'] === 'automatic' ? 'text-[#43c766]' : 'text-[#c0c6c2]' }}">
                        {{ strtoupper($device['mode']) === 'AUTOMATIC' ? 'OTOMATIS' : 'MANUAL' }}
                    </div>
                </div>
            </div>

            {{-- Sprayer control --}}
            <div class="card">
                <div class="card-header">
                    Pompa Sprayer
                </div>
                <div class="p-6 flex flex-col items-center gap-4">
                    <div class="text-[#999] text-sm">Nyalakan atau matikan pompa</div>
                    <div class="w-[118px] h-[64px] rounded-full relative transition-colors
                        {{ $device['sprayer_status'] === 'on' ? 'bg-[#43c766]' : 'bg-[#626866]' }}">
                        <div class="w-[47px] h-[47px] bg-white rounded-full absolute top-[8px]
                            {{ $device['sprayer_status'] === 'on' ? 'right-[9px]' : 'left-[9px]' }}">
                        </div>
                    </div>
                    <div class="text-lg font-extrabold {{ $device['sprayer_status'] === 'on' ? 'text-[#43c766]' : 'text-[#c0c6c2]' }}">
                        {{ strtoupper($device['sprayer_status']) === 'ON' ? 'ON' : 'OFF' }}
                    </div>
                    <div class="flex gap-3">
                        <button class="btn-primary px-6 py-2 {{ $device['sprayer_status'] === 'on' ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}"
                                onclick="alert('Aksi: Nyalakan Sprayer (backend)')">
                            Nyalakan
                        </button>
                        <button class="btn-secondary px-6 py-2 {{ $device['sprayer_status'] === 'off' ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}"
                                onclick="alert('Aksi: Matikan Sprayer (backend)')">
                            Matikan
                        </button>
                    </div>
                </div>
            </div>

            {{-- Warning rain --}}
            @if(($lastSensor['rain_status'] ?? '') === 'rain')
                <div class="alert-warning">
                    ⚠️ Hujan terdeteksi. Penyemprotan otomatis tidak dijalankan.
                </div>
            @else
                <div class="alert-info">
                    ℹ️ Tidak hujan. Penyemprotan diizinkan.
                </div>
            @endif
        </div>

        {{-- RIGHT: Info Kondisi + Log --}}
        <div class="space-y-6">

            {{-- Kondisi saat ini --}}
            <div class="card">
                <div class="card-header">
                    Kondisi Terkini
                </div>
                <div class="p-4 grid grid-cols-2 gap-4">
                    @php $c = $lastSensor['condition_status'] ?? 'normal'; @endphp
                    <div class="bg-[#2b2b2b] rounded-lg p-4 text-center">
                        <div class="text-[#999] text-xs mb-1">Status</div>
                        <div class="text-2xl font-extrabold
                            {{ $c === 'kritis' ? 'text-red-400' : ($c === 'waspada' ? 'text-yellow-400' : 'text-green-400') }}">
                            {{ strtoupper($c) }}
                        </div>
                    </div>
                    <div class="bg-[#2b2b2b] rounded-lg p-4 text-center">
                        <div class="text-[#999] text-xs mb-1">Tanah</div>
                        <div class="text-2xl font-extrabold text-white">{{ $lastSensor['soil_moisture'] ?? '-' }}%</div>
                    </div>
                    <div class="bg-[#2b2b2b] rounded-lg p-4 text-center">
                        <div class="text-[#999] text-xs mb-1">Hujan</div>
                        <div class="text-lg font-extrabold text-blue-400">
                            {{ ($lastSensor['rain_status'] ?? '') === 'rain' ? 'Hujan' : 'Tidak Hujan' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Log terbaru --}}
            <div class="card">
                <div class="card-header">
                    Riwayat Penyemprotan Terbaru
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Trigger</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentLogs as $log)
                            <tr>
                                <td>{{ $log['time'] }}</td>
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
                                <td class="text-[#999] text-xs">{{ $log['reason'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
