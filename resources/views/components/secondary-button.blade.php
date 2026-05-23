<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-secondary btn-sm']) }}>
    {{ $slot }}
</button>
