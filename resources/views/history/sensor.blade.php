<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Riwayat Sensor</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Data pembacaan sensor lingkungan</div>
        </div>
    </x-slot>

    <div class="p-6">

        {{-- Filter --}}
        <div class="card mb-6">
            <div class="p-4 flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">Dari</label>
                    <input type="date" class="form-input w-40" value="2026-05-20">
                </div>
                <div>
                    <label class="form-label">Sampai</label>
                    <input type="date" class="form-input w-40" value="2026-05-20">
                </div>
                <button class="btn-primary" onclick="alert('Filter (backend)')">Filter</button>
            </div>
        </div>

        {{-- Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Suhu</th>
                            <th>Kelemb. Udara</th>
                            <th>Kelemb. Tanah</th>
                            <th>Hujan</th>
                            <th>Sprayer</th>
                            <th>Kondisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($readings as $r)
                        <tr>
                            <td class="text-xs">{{ $r['time'] }}</td>
                            <td>{{ $r['temp'] }}°C</td>
                            <td>{{ $r['hum'] }}%</td>
                            <td>{{ $r['soil'] }}%</td>
                            <td>
                                <span class="badge {{ $r['rain'] === 'rain' ? 'badge-off' : 'badge-on' }}">
                                    {{ $r['rain'] === 'rain' ? 'Hujan' : 'Tdk Hujan' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $r['sprayer'] === 'on' ? 'badge-on' : 'badge-off' }}">
                                    {{ strtoupper($r['sprayer']) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $r['condition'] }}">
                                    {{ ucfirst($r['condition']) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :currentPage="1" :lastPage="1" />
        </div>
    </div>
</x-app-layout>
