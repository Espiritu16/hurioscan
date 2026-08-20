{{-- Botón en sus tres variantes (F00-UT-02).

     Con `ruta` navega por **nombre**, nunca por URL literal, y degrada igual
     que el menú lateral: si esa ruta todavía no está montada, se muestra
     deshabilitado en vez de romper. Sin `ruta` es un botón normal, para
     acciones del propio componente (`wire:click`) o envíos de formulario. --}}
@props(['variante' => 'primario', 'ruta' => null, 'parametros' => []])

@php
    $clases = [
        'primario' => 'bg-acento text-sobre-acento hover:bg-acento/90',
        'secundario' => 'border border-borde bg-superficie text-tinta hover:bg-acento-suave',
        'terciario' => 'text-acento hover:bg-acento-suave',
    ][$variante];

    $base = [
        'inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-acento',
        'disabled:cursor-not-allowed disabled:opacity-50',
        $clases,
    ];

    $destinoExiste = $ruta !== null && \Illuminate\Support\Facades\Route::has($ruta);
@endphp

@if ($ruta !== null && $destinoExiste)
    <a href="{{ route($ruta, $parametros) }}" {{ $attributes->class($base) }}>
        {{ $slot }}
    </a>
@elseif ($ruta !== null)
    {{-- La ruta aún no existe: se muestra, no se ofrece. --}}
    <button type="button" disabled aria-disabled="true" {{ $attributes->class($base) }}>
        {{ $slot }}
    </button>
@else
    <button {{ $attributes->merge(['type' => 'button'])->class($base) }}>
        {{ $slot }}
    </button>
@endif
