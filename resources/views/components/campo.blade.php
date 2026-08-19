{{-- Campo de formulario con etiqueta y error accesible (F00-UT-02).
     Sin slot renderiza un <input>; con slot, el control lo aporta quien llama
     (select, textarea) y este componente solo pone etiqueta y error. --}}
@props(['nombre', 'etiqueta', 'tipo' => 'text', 'error' => null])

@php $idError = "{$nombre}-error"; @endphp

<div class="flex flex-col gap-1.5">
    <label for="{{ $nombre }}" class="text-sm font-medium text-tinta">{{ $etiqueta }}</label>

    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        <input
            type="{{ $tipo }}"
            id="{{ $nombre }}"
            name="{{ $nombre }}"
            @if ($error) aria-invalid="true" aria-describedby="{{ $idError }}" @endif
            {{ $attributes->class([
                'rounded-md border bg-superficie px-3 py-2 text-sm text-tinta placeholder:text-tinta-suave',
                'focus:outline-2 focus:outline-offset-1 focus:outline-acento',
                'border-borde' => ! $error,
                'border-peligro' => $error,
            ]) }}
        />
    @endif

    @if ($error)
        <p id="{{ $idError }}" role="alert" class="text-sm text-peligro">{{ $error }}</p>
    @endif
</div>
