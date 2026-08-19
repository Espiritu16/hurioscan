{{-- Sesiones pendientes y reanudación (F03-UT-04). --}}
<div class="flex flex-col gap-4">
    @if ($estado === 'carga')
        <p role="status" class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Cargando…</p>
    @elseif ($estado === 'error')
        <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">{{ $error }}</p>
    @elseif ($estado === 'vacio')
        <x-estado-vacio
            titulo="No tienes sesiones pendientes"
            descripcion="Cuando dejes un folder a medias, aparecerá aquí para que lo retomes donde lo dejaste."
        >
            <x-boton variante="secundario">Buscar un paciente</x-boton>
        </x-estado-vacio>
    @else
        <x-tabla>
            <x-slot:cabecera>
                <x-tabla.encabezado>Paciente</x-tabla.encabezado>
                <x-tabla.encabezado>N.º H.C.</x-tabla.encabezado>
                <x-tabla.encabezado>Hojas</x-tabla.encabezado>
                <x-tabla.encabezado>Estado</x-tabla.encabezado>
                <x-tabla.encabezado>Iniciada</x-tabla.encabezado>
                <x-tabla.encabezado><span class="sr-only">Acciones</span></x-tabla.encabezado>
            </x-slot:cabecera>
            @foreach ($sesiones as $sesion)
                <tr wire:key="sesion-{{ $sesion['id'] }}">
                    <td class="px-3 py-2">{{ $sesion['paciente']['apellidos'] }}, {{ $sesion['paciente']['nombres'] }}</td>
                    <td class="px-3 py-2 font-mono text-acento">{{ $sesion['paciente']['numeroHistoria'] }}</td>
                    <td class="px-3 py-2 font-mono">
                        {{ $sesion['hojas'] }}
                        @if ($sesion['hojasSinRevisar'] > 0)
                            <span class="text-tinta-suave">· {{ $sesion['hojasSinRevisar'] }} sin revisar</span>
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        <x-etiqueta-estado :tipo="$sesion['estado'] === 'EN_REVISION' ? 'advertencia' : 'neutro'">
                            {{ $sesion['estado'] }}
                        </x-etiqueta-estado>
                    </td>
                    <td class="px-3 py-2 font-mono text-xs text-tinta-suave">
                        {{ \Illuminate\Support\Carbon::parse($sesion['creadoEn'])->timezone('America/Lima')->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-3 py-2">
                        <x-boton variante="terciario">
                            Retomar en {{ $this->destinoDe($sesion['estado']) }}
                        </x-boton>
                    </td>
                </tr>
            @endforeach
        </x-tabla>
    @endif
</div>
