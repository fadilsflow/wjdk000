@props(['currentPage' => 1, 'lastPage' => 1, 'previousPageUrl' => null, 'nextPageUrl' => null])

<div class="px-4 py-3 flex items-center justify-between border-t border-[color:var(--color-bg-elevated)] text-xs text-[color:var(--color-text-muted)]">
    <span>Halaman {{ $currentPage }} dari {{ $lastPage }}</span>
    <div class="flex gap-2">
        @if($previousPageUrl)
            <a href="{{ $previousPageUrl }}" class="btn-outline btn-sm">Sebelumnya</a>
        @else
            <button class="btn-outline btn-sm" disabled>Sebelumnya</button>
        @endif

        @if($nextPageUrl)
            <a href="{{ $nextPageUrl }}" class="btn-outline btn-sm">Selanjutnya</a>
        @else
            <button class="btn-outline btn-sm" disabled>Selanjutnya</button>
        @endif
    </div>
</div>
