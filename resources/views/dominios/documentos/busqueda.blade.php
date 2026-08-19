{{-- Búsqueda por contenido (F06-UT-02). El fragmento se imprime con {!! !!}
     a propósito: `resaltar()` ya devolvió HTML seguro, porque escapó el texto
     del documento ANTES de insertar las marcas (RNF-012). --}}
<div class="flex flex-col gap-4">
    <form wire:submit="buscar" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <x-buscador
            wire:model="termino"
            etiqueta="Buscar en el archivo digitalizado"
            placeholder="Texto del documento"
            class="sm:flex-1"
        />
        <x-campo nombre="tipo" etiqueta="Tipo" class="sm:w-52">
            <select id="tipo" wire:model.live="tipo"
                class="rounded-md border border-borde bg-superficie px-3 py-2 text-sm text-tinta focus:outline-2 focus:outline-offset-1 focus:outline-acento">
                <option value="">Todos</option>
                <option value="hoja_atencion">Hoja de atención</option>
                <option value="receta">Receta médica</option>
                <option value="laboratorio">Laboratorio</option>
                <option value="epicrisis">Epicrisis</option>
                <option value="consentimiento">Consentimiento</option>
            </select>
        </x-campo>
        <x-boton type="submit">Buscar</x-boton>
    </form>

    @if ($error)
        <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">{{ $error }}</p>
    @endif

    @if ($estado === 'buscando')
        <p role="status" class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Buscando…</p>
    @elseif ($estado === 'vacio')
        {{-- Sin coincidencias es un estado vacío, no un error. --}}
        <x-estado-vacio
            titulo="Ningún documento contiene ese término"
            descripcion="Prueba con una palabra más corta o revisa la ortografía. El buscador solo mira dentro de hojas ya digitalizadas."
        >
            <x-boton variante="secundario">Buscar un paciente</x-boton>
        </x-estado-vacio>
    @elseif ($estado === 'exito')
        <p class="font-mono text-xs tracking-wide text-tinta-suave uppercase">
            {{ $meta['total'] }} coincidencias
        </p>

        <ul class="flex flex-col gap-3">
            @foreach ($resultados as $resultado)
                <li wire:key="resultado-{{ $resultado['documentoId'] }}"
                    class="flex flex-col gap-2 rounded-lg border border-borde bg-superficie p-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-medium text-tinta">{{ str_replace('_', ' ', $resultado['tipo']) }}</span>
                        <span class="font-mono text-xs text-tinta-suave">{{ $resultado['fechaDocumento'] ?? 'Sin fecha' }}</span>
                        <span class="font-mono text-xs text-acento">
                            H.C. {{ $resultado['paciente']['numeroHistoria'] }}
                        </span>
                        <x-boton variante="terciario" class="ms-auto">Abrir visor</x-boton>
                    </div>
                    <p class="rounded bg-papel p-2 font-mono text-xs leading-relaxed text-tinta">
                        {!! $this->resaltar($resultado['fragmento']) !!}
                    </p>
                </li>
            @endforeach
        </ul>
    @endif
</div>
