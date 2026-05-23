@props(['currentPage' => 1, 'lastPage' => 1])

<div class="px-4 py-3 flex items-center justify-between border-t border-[color:var(--color-bg-elevated)] text-xs text-[color:var(--color-text-muted)]">
    <span>Halaman {{ $currentPage }} dari {{ $lastPage }}</span>
    <div class="flex gap-2">
        <button class="btn-outline btn-sm" {{ $currentPage <= 1 ? 'disabled' : '' }}>Sebelumnya</button>
        <button class="btn-outline btn-sm" {{ $currentPage >= $lastPage ? 'disabled' : '' }}>Selanjutnya</button>
    </div>
</div>
