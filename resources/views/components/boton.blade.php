{{-- Botón en sus tres variantes (F00-UT-02). --}}
@props(['variante' => 'primario'])

@php
    $clases = [
        'primario' => 'bg-acento text-sobre-acento hover:bg-acento/90',
        'secundario' => 'border border-borde bg-superficie text-tinta hover:bg-acento-suave',
        'terciario' => 'text-acento hover:bg-acento-suave',
    ][$variante];
@endphp

<button
    {{ $attributes->merge(['type' => 'button'])->class([
        'inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-acento',
        'disabled:cursor-not-allowed disabled:opacity-50',
        $clases,
    ]) }}
>
    {{ $slot }}
</button>
