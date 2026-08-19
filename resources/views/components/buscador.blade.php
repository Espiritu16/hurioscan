{{-- Buscador con ícono y etiqueta accesible (F00-UT-02). --}}
@props(['nombre' => 'q', 'etiqueta' => 'Buscar', 'placeholder' => 'Buscar…'])

<div class="relative">
    <label for="{{ $nombre }}" class="sr-only">{{ $etiqueta }}</label>
    <svg
        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-tinta-suave"
        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
    >
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.3-4.3" />
    </svg>
    <input
        type="search"
        id="{{ $nombre }}"
        name="{{ $nombre }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->class([
            'w-full rounded-md border border-borde bg-superficie py-2 pr-3 pl-9 text-sm text-tinta',
            'placeholder:text-tinta-suave focus:outline-2 focus:outline-offset-1 focus:outline-acento',
        ]) }}
    />
</div>
