<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Riwayat Notifikasi</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Log pengiriman WhatsApp</div>
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
                        @forelse($notifications as $n)
                            <tr>
                                <td class="text-xs">{{ $n['time'] }}</td>
                                <td class="text-xs font-semibold">{{ $n['type_label'] }}</td>
                                <td class="text-xs">{{ $n['phone'] }}</td>
                                <td class="text-xs text-[color:var(--color-text-muted)] max-w-[200px] truncate">{{ $n['message'] }}</td>
                                <td>
                                    <span class="badge badge-{{ $n['status'] === 'sent' ? 'sent' : 'failed' }}">
                                        {{ $n['status'] === 'sent' ? 'Terkirim' : 'Gagal' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-sm text-[color:var(--color-text-muted)] py-6">
                                    Belum ada log notifikasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <x-pagination
                :currentPage="$pagination['current_page']"
                :lastPage="$pagination['last_page']"
                :previousPageUrl="$pagination['previous_page_url']"
                :nextPageUrl="$pagination['next_page_url']"
            />
        </div>
    </div>
</x-app-layout>
