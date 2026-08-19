{{-- Tarjeta de indicador del panel de avance (F00-UT-03). --}}
@props(['titulo', 'valor', 'unidad' => null])

<article {{ $attributes->class(['flex flex-col gap-2 rounded-lg border border-borde bg-superficie p-4 shadow-sm']) }}>
    <h3 class="font-mono text-xs font-medium tracking-wide text-tinta-suave uppercase">{{ $titulo }}</h3>
    <p class="flex items-baseline gap-1.5">
        <span class="font-mono text-3xl font-semibold text-tinta">{{ $valor }}</span>
        @if ($unidad)
            <span class="text-sm text-tinta-suave">{{ $unidad }}</span>
        @endif
    </p>
    @if ($slot->isNotEmpty())
        <div class="text-xs text-tinta-suave">{{ $slot }}</div>
    @endif
</article>
