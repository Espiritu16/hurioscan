{{-- Línea de tiempo del paciente (F06-UT-03). --}}
<div class="flex flex-col gap-4">
    @if ($estado !== 'error' && $paciente !== [])
        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
            <h2 class="text-lg font-semibold text-tinta">
                {{ $paciente['apellidos'] }}, {{ $paciente['nombres'] }}
            </h2>
            <span class="font-mono text-sm text-acento">H.C. {{ $paciente['numeroHistoria'] }}</span>
            <span class="font-mono text-xs tracking-wide text-tinta-suave uppercase">
                {{ $total }} documentos
            </span>
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-3">
        <x-campo nombre="tipo" etiqueta="Tipo">
            <select id="tipo" wire:model.live="tipo"
                class="rounded-md border border-borde bg-superficie px-3 py-2 text-sm text-tinta focus:outline-2 focus:outline-offset-1 focus:outline-acento">
                <option value="">Todos</option>
                <option value="hoja_atencion">Hoja de atención</option>
                <option value="receta">Receta médica</option>
                <option value="laboratorio">Laboratorio</option>
                <option value="otro">Otro</option>
            </select>
        </x-campo>
        <x-campo nombre="desde" etiqueta="Desde" tipo="date" wire:model.live="desde" />
        <x-campo nombre="hasta" etiqueta="Hasta" tipo="date" wire:model.live="hasta" />
    </div>

    @if ($estado === 'carga')
        <p role="status" class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Cargando…</p>
    @elseif ($estado === 'error')
        <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">{{ $error }}</p>
    @elseif ($estado === 'vacio')
        <x-estado-vacio
            titulo="No hay documentos con esos filtros"
            descripcion="Prueba quitando el filtro de tipo o ampliando el rango de fechas."
        >
            <x-boton variante="secundario" wire:click="$set('tipo', '')">Quitar los filtros</x-boton>
        </x-estado-vacio>
    @else
        <ol class="flex flex-col gap-3">
            @foreach ($documentos as $documento)
                <li wire:key="doc-{{ $documento['id'] }}"
                    class="flex flex-col gap-2 rounded-lg border border-borde bg-superficie p-3 sm:flex-row sm:items-center">
                    <span class="font-mono text-sm text-tinta-suave sm:w-28">
                        {{ $documento['fechaDocumento'] ?? 'Sin fecha' }}
                    </span>
                    <span class="font-medium text-tinta">{{ str_replace('_', ' ', $documento['tipo']) }}</span>
                    <x-etiqueta-estado :tipo="$documento['estadoRevision'] === 'ILEGIBLE' ? 'peligro' : 'exito'">
                        {{ $documento['estadoRevision'] }}
                    </x-etiqueta-estado>
                    <p class="min-w-0 flex-1 truncate font-mono text-xs text-tinta-suave">
                        {{ $documento['fragmento'] ?? 'Sin texto asociado' }}
                    </p>
                    <x-boton variante="terciario">Abrir</x-boton>
                </li>
            @endforeach
        </ol>
    @endif
</div>
