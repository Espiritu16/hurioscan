{{-- Barra del paciente fijada durante la sesión de digitalización (F00-UT-04).
     Sticky: se mantiene visible al desplazar (garantía visual de RF-002). --}}
@props(['nombre', 'historia'])

<div {{ $attributes->class([
    'sticky top-0 z-40 flex flex-wrap items-center gap-x-4 gap-y-1 bg-acento px-4 py-2 text-sobre-acento',
]) }}>
    <span class="font-mono text-xs tracking-wide uppercase opacity-80">Fijado para esta sesión</span>
    <span class="font-semibold">{{ $nombre }}</span>
    <span class="font-mono text-sm">H.C. {{ $historia }}</span>
    @if ($slot->isNotEmpty())
        <span class="text-sm opacity-80">{{ $slot }}</span>
    @endif
    @if (isset($acciones))
        <div class="ms-auto">{{ $acciones }}</div>
    @endif
</div>
