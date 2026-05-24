<x-app-layout>
    <x-slot name="subbar">
        <div>
            <div class="text-2xl font-extrabold leading-tight">Riwayat Penyemprotan</div>
            <div class="text-[color:var(--color-text-muted)] text-sm">Log aktivitas sprayer</div>
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
                        @forelse($logs as $log)
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
            <x-pagination
                :currentPage="$pagination['current_page']"
                :lastPage="$pagination['last_page']"
                :previousPageUrl="$pagination['previous_page_url']"
                :nextPageUrl="$pagination['next_page_url']"
            />
        </div>
    </div>
</x-app-layout>
