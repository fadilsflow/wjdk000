<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-danger btn-sm']) }}>
    {{ $slot }}
</button>
