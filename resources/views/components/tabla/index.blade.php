{{-- Tabla con desplazamiento horizontal propio (F00-UT-03): en 360 px la
     tabla se desplaza dentro de su contenedor, nunca desborda la página.

     `min-w-0` no es decorativo: dentro de un contenedor flex, este div hereda
     `min-width: auto` y crecería hasta el ancho de la tabla, empujando la
     página entera en lugar de desplazarse por dentro.

     `relative` tampoco: sin él, un descendiente posicionado en absoluto —el
     `sr-only` de un encabezado, por ejemplo— toma como referencia un ancestro
     de más arriba, escapa al recorte de este contenedor y vuelve a estirar la
     página. --}}
@props([])

<div {{ $attributes->class(['relative w-full min-w-0 overflow-x-auto rounded-lg border border-borde bg-superficie']) }}>
    <table class="w-full min-w-max text-left text-sm">
        <thead class="border-b border-borde">
            <tr>{{ $cabecera }}</tr>
        </thead>
        <tbody class="divide-y divide-borde [&>tr:nth-child(even)]:bg-franja">
            {{ $slot }}
        </tbody>
    </table>
</div>
