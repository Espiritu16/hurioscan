{{-- Cola de hojas por reescanear (F05-UT-04). --}}
<div class="flex flex-col gap-4">
    @if ($aviso)
        <p role="status" class="rounded-md bg-exito-suave px-3 py-2 text-sm text-exito">{{ $aviso }}</p>
    @endif

    @if ($estado === 'carga')
        <p role="status" class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Cargando…</p>
    @elseif ($estado === 'error')
        <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">{{ $error }}</p>
    @elseif ($estado === 'vacio')
        <x-estado-vacio
            titulo="No hay hojas ilegibles"
            descripcion="Cuando una hoja se marque como ilegible aparecerá aquí para reescanearla."
        >
            <x-boton ruta="sesiones.pendientes" variante="secundario">Ir a sesiones pendientes</x-boton>
        </x-estado-vacio>
    @else
        <x-tabla>
            <x-slot:cabecera>
                <x-tabla.encabezado>Paciente</x-tabla.encabezado>
                <x-tabla.encabezado>N.º H.C.</x-tabla.encabezado>
                <x-tabla.encabezado>Tipo</x-tabla.encabezado>
                <x-tabla.encabezado>Sesión</x-tabla.encabezado>
                <x-tabla.encabezado><span class="sr-only">Acciones</span></x-tabla.encabezado>
            </x-slot:cabecera>
            @foreach ($hojas as $hoja)
                <tr wire:key="ilegible-{{ $hoja['documentoId'] }}">
                    <td class="px-3 py-2">{{ $hoja['paciente']['apellidos'] }}, {{ $hoja['paciente']['nombres'] }}</td>
                    <td class="px-3 py-2 font-mono text-acento">{{ $hoja['paciente']['numeroHistoria'] }}</td>
                    <td class="px-3 py-2">{{ str_replace('_', ' ', $hoja['tipo']) }}</td>
                    <td class="px-3 py-2 font-mono">{{ $hoja['sesionId'] }}</td>
                    <td class="px-3 py-2">
                        <x-boton variante="terciario" wire:click="reabrir({{ $hoja['documentoId'] }})">
                            Reabrir revisión
                        </x-boton>
                    </td>
                </tr>
            @endforeach
        </x-tabla>
    @endif
</div>
