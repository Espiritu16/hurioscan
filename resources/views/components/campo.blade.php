{{-- Campo de formulario con etiqueta y error accesible (F00-UT-02).
     Sin slot renderiza un <input>; con slot, el control lo aporta quien llama
     (select, textarea) y este componente solo pone etiqueta y error.

     `error` marca el campo e imprime su mensaje. `invalido` lo marca sin
     mensaje propio, para los casos en que el motivo se anuncia una sola vez
     para todo el formulario (por ejemplo el acceso, donde el contrato exige
     un mensaje único que no revele qué correos existen). --}}
@props(['nombre', 'etiqueta', 'tipo' => 'text', 'error' => null, 'invalido' => false])

@php
    $idError = "{$nombre}-error";
    $marcado = $error || $invalido;
@endphp

<div class="flex flex-col gap-1.5">
    <label for="{{ $nombre }}" class="text-sm font-medium text-tinta">{{ $etiqueta }}</label>

    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        <input
            type="{{ $tipo }}"
            id="{{ $nombre }}"
            name="{{ $nombre }}"
            @if ($marcado) aria-invalid="true" @endif
            @if ($error) aria-describedby="{{ $idError }}" @endif
            {{ $attributes->class([
                'rounded-md border bg-superficie px-3 py-2 text-sm text-tinta placeholder:text-tinta-suave',
                'focus:outline-2 focus:outline-offset-1 focus:outline-acento',
                'border-borde' => ! $marcado,
                'border-peligro' => $marcado,
            ]) }}
        />
    @endif

    @if ($error)
        <p id="{{ $idError }}" role="alert" class="text-sm text-peligro">{{ $error }}</p>
    @endif
</div>
