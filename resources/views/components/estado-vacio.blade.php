{{-- Estado vacío con salida (F00-UT-03): toda vista sin datos ofrece la
     acción siguiente, nunca un espacio en blanco (experiencia.md). --}}
@props(['titulo', 'descripcion' => null])

<div {{ $attributes->class([
    'flex flex-col items-center gap-3 rounded-lg border border-dashed border-borde bg-superficie p-8 text-center',
]) }}>
    <p class="font-medium text-tinta">{{ $titulo }}</p>
    @if ($descripcion)
        <p class="max-w-prose text-sm text-tinta-suave">{{ $descripcion }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-1">{{ $slot }}</div>
    @endif
</div>
