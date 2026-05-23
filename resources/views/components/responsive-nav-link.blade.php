@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-[color:var(--color-brand)] text-start text-base font-medium text-[color:var(--color-text)] bg-[color:var(--color-bg-elevated)] focus:outline-none focus:text-[color:var(--color-text)] focus:bg-[color:var(--color-bg-elevated)] focus:border-[color:var(--color-brand-border)] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-[color:var(--color-text-muted)] hover:text-[color:var(--color-text)] hover:bg-[color:var(--color-bg-elevated)] hover:border-[color:var(--color-border)] focus:outline-none focus:text-[color:var(--color-text)] focus:bg-[color:var(--color-bg-elevated)] focus:border-[color:var(--color-border)] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
