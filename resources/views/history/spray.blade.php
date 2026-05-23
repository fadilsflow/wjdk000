<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-xl font-extrabold">Riwayat Penyemprotan</div>
            <div class="text-[#999] text-sm">Log aktivitas sprayer</div>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="card overflow-hidden">
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
                            <td class="text-xs text-[#999]">{{ $log['reason'] }}</td>
                            <td class="text-xs">{{ $log['by'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 flex items-center justify-between border-t border-[#3b3b3b] text-xs text-[#999]">
                <span>Halaman 1 dari 1</span>
                <div class="flex gap-2">
                    <button class="btn-secondary text-xs py-1 px-3" disabled>Sebelumnya</button>
                    <button class="btn-secondary text-xs py-1 px-3" disabled>Selanjutnya</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
