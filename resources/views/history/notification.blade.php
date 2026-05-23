<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-xl font-extrabold">Riwayat Notifikasi</div>
            <div class="text-[#999] text-sm">Log pengiriman WhatsApp</div>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Jenis</th>
                            <th>Penerima</th>
                            <th>Pesan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $n)
                        <tr>
                            <td class="text-xs">{{ $n['time'] }}</td>
                            <td class="text-xs font-medium">{{ $n['type'] }}</td>
                            <td class="text-xs">{{ $n['phone'] }}</td>
                            <td class="text-xs text-[#999] max-w-[200px] truncate">{{ $n['message'] }}</td>
                            <td>
                                <span class="badge badge-{{ $n['status'] === 'sent' ? 'sent' : 'failed' }}">
                                    {{ $n['status'] === 'sent' ? 'Terkirim' : 'Gagal' }}
                                </span>
                            </td>
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
