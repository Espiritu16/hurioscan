{{-- Tabla con desplazamiento horizontal propio (F00-UT-03): en 360 px la
     tabla se desplaza dentro de su contenedor, nunca desborda la página. --}}
@props([])

<div {{ $attributes->class(['overflow-x-auto rounded-lg border border-borde bg-superficie']) }}>
    <table class="w-full min-w-max text-left text-sm">
        <thead class="border-b border-borde">
            <tr>{{ $cabecera }}</tr>
        </thead>
        <tbody class="divide-y divide-borde [&>tr:nth-child(even)]:bg-franja">
            {{ $slot }}
        </tbody>
    </table>
</div>
