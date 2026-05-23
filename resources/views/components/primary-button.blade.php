<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary btn-sm']) }}>
    {{ $slot }}
</button>
