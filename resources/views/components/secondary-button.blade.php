<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline-primary text-uppercase']) }}>
    {{ $slot }}
</button>
