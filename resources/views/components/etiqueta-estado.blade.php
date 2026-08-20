{{-- Etiqueta de estado en sus cuatro colores semánticos (F00-UT-03).
     Nunca usa el color de acento: los estados se distinguen de las acciones. --}}
@props(['tipo' => 'neutro'])

@php
    $clases = [
        'exito' => 'bg-exito-suave text-exito',
        'advertencia' => 'bg-advertencia-suave text-advertencia',
        'peligro' => 'bg-peligro-suave text-peligro',
        'neutro' => 'bg-franja text-tinta-suave',
    ][$tipo];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center rounded px-1.5 py-0.5 font-mono text-xs font-medium tracking-wide uppercase',
    $clases,
]) }}>
    {{ $slot }}
</span>
