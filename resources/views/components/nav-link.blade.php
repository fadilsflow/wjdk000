@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[color:var(--color-brand)] text-sm font-medium leading-5 text-[color:var(--color-text)] focus:outline-none focus:border-[color:var(--color-brand-border)] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[color:var(--color-text-muted)] hover:text-[color:var(--color-text)] hover:border-[color:var(--color-border)] focus:outline-none focus:text-[color:var(--color-text)] focus:border-[color:var(--color-border)] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
