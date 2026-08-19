{{-- Consulta de auditoría (F07-UT-04). Se imprimen solo los campos de la
     allowlist que el contrato devuelve: entidad, acción, usuario, origen,
     fecha y los valores anterior/nuevo ya acotados. --}}
<div class="flex flex-col gap-4">
    <div class="grid gap-3 sm:grid-cols-2 lg:max-w-xl">
        <x-campo nombre="entidad" etiqueta="Entidad">
            <select id="entidad" wire:model.live="entidad"
                class="rounded-md border border-borde bg-superficie px-3 py-2 text-sm text-tinta focus:outline-2 focus:outline-offset-1 focus:outline-acento">
                <option value="">Todas</option>
                <option value="Paciente">Paciente</option>
                <option value="SesionDigitalizacion">Sesión de digitalización</option>
                <option value="Documento">Documento</option>
                <option value="Usuario">Usuario</option>
            </select>
        </x-campo>
        <x-campo nombre="accion" etiqueta="Acción">
            <select id="accion" wire:model.live="accion"
                class="rounded-md border border-borde bg-superficie px-3 py-2 text-sm text-tinta focus:outline-2 focus:outline-offset-1 focus:outline-acento">
                <option value="">Todas</option>
                <option value="crear">Crear</option>
                <option value="actualizar">Actualizar</option>
                <option value="eliminar">Eliminar</option>
                <option value="consultar">Consultar</option>
            </select>
        </x-campo>
    </div>

    @if ($estado === 'carga')
        <p role="status" class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Cargando…</p>
    @elseif ($estado === 'error')
        <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">{{ $error }}</p>
    @elseif ($estado === 'vacio')
        <x-estado-vacio
            titulo="No hay registros con esos filtros"
            descripcion="Prueba con otra entidad o quita el filtro de acción."
        >
            <x-boton variante="secundario" wire:click="$set('entidad', '')">Quitar los filtros</x-boton>
        </x-estado-vacio>
    @else
        <x-tabla>
            <x-slot:cabecera>
                <x-tabla.encabezado>Fecha</x-tabla.encabezado>
                <x-tabla.encabezado>Entidad</x-tabla.encabezado>
                <x-tabla.encabezado>Acción</x-tabla.encabezado>
                <x-tabla.encabezado>Usuario</x-tabla.encabezado>
                <x-tabla.encabezado>Origen</x-tabla.encabezado>
                <x-tabla.encabezado>Cambio</x-tabla.encabezado>
            </x-slot:cabecera>
            @foreach ($filas as $fila)
                <tr wire:key="auditoria-{{ $fila['id'] }}">
                    <td class="px-3 py-2 font-mono text-xs">
                        {{ \Illuminate\Support\Carbon::parse($fila['fecha'])->timezone('America/Lima')->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-3 py-2">{{ $fila['entidad'] }} <span class="font-mono text-xs text-tinta-suave">#{{ $fila['entidadId'] }}</span></td>
                    <td class="px-3 py-2">{{ $fila['accion'] }}</td>
                    {{-- Una acción del sistema no tiene usuario. --}}
                    <td class="px-3 py-2">{{ $fila['usuario']['nombre'] ?? 'Sistema' }}</td>
                    <td class="px-3 py-2 font-mono text-xs">{{ $fila['origen'] }}</td>
                    <td class="px-3 py-2 font-mono text-xs text-tinta-suave">
                        @if ($fila['valoresNuevos'])
                            {{ collect($fila['valoresNuevos'])->map(fn ($v, $k) => "{$k}: {$v}")->implode(' · ') }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-tabla>
    @endif
</div>
