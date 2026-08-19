{{-- Búsqueda y listado de pacientes (F02-UT-02). --}}
<div class="flex flex-col gap-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <x-buscador
            wire:model.live.debounce.300ms="termino"
            etiqueta="Buscar paciente"
            placeholder="N.º de historia, DNI o apellidos"
            class="sm:max-w-md"
        />
        @if ($this->puedeRegistrar)
            <x-boton ruta="pacientes.alta" class="sm:ms-auto">Registrar paciente nuevo</x-boton>
        @endif
    </div>

    @if ($estado === 'buscando')
        <p class="font-mono text-xs tracking-wide text-tinta-suave uppercase" role="status">Buscando…</p>
    @elseif ($estado === 'error')
        <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">{{ $error }}</p>
    @elseif ($estado === 'vacio')
        <x-estado-vacio
            titulo="No encontramos pacientes con ese dato"
            descripcion="Revisa el número de historia o los apellidos. Si el paciente no existe todavía, puedes registrarlo."
        >
            @if ($this->puedeRegistrar)
                <x-boton ruta="pacientes.alta" variante="secundario">Registrar paciente nuevo</x-boton>
            @endif
        </x-estado-vacio>
    @else
        <x-tabla>
            <x-slot:cabecera>
                <x-tabla.encabezado>N.º H.C.</x-tabla.encabezado>
                <x-tabla.encabezado>Paciente</x-tabla.encabezado>
                <x-tabla.encabezado>DNI</x-tabla.encabezado>
                <x-tabla.encabezado>Documentos</x-tabla.encabezado>
                <x-tabla.encabezado><span class="sr-only">Acciones</span></x-tabla.encabezado>
            </x-slot:cabecera>
            @foreach ($resultados as $paciente)
                <tr wire:key="paciente-{{ $paciente['id'] }}">
                    <td class="px-3 py-2 font-mono text-acento">{{ $paciente['numeroHistoria'] }}</td>
                    <td class="px-3 py-2">{{ $paciente['apellidos'] }}, {{ $paciente['nombres'] }}</td>
                    <td class="px-3 py-2 font-mono">{{ $paciente['dni'] ?? '—' }}</td>
                    <td class="px-3 py-2 font-mono">{{ $paciente['totalDocumentos'] }}</td>
                    <td class="px-3 py-2">
                        {{-- El operador continúa a la captura; los demás roles
                             llegan a la línea de tiempo (experiencia.md). --}}
                        @if ($this->puedeRegistrar)
                            <x-boton
                                variante="terciario"
                                ruta="sesiones.apertura"
                                :parametros="['pacienteId' => $paciente['id']]"
                            >
                                Iniciar digitalización
                            </x-boton>
                        @else
                            <x-boton
                                variante="terciario"
                                ruta="pacientes.detalle"
                                :parametros="['pacienteId' => $paciente['id']]"
                            >
                                Ver línea de tiempo
                            </x-boton>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-tabla>

        @if (($meta['totalPaginas'] ?? 1) > 1)
            <nav class="flex items-center gap-2" aria-label="Paginación">
                <x-boton variante="secundario" wire:click="irAPagina({{ $pagina - 1 }})" :disabled="$pagina <= 1">
                    Anterior
                </x-boton>
                <span class="font-mono text-xs text-tinta-suave">
                    Página {{ $meta['pagina'] }} de {{ $meta['totalPaginas'] }}
                </span>
                <x-boton variante="secundario" wire:click="irAPagina({{ $pagina + 1 }})"
                    :disabled="$pagina >= ($meta['totalPaginas'] ?? 1)">
                    Siguiente
                </x-boton>
            </nav>
        @endif
    @endif
</div>
