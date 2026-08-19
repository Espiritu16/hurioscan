{{-- Visor de documento (F06-UT-04). En 360 px la imagen ocupa el ancho y el
     texto va debajo; desde lg van en paralelo. --}}
<div class="flex flex-col gap-4">
    @if ($estado === 'carga')
        <p role="status" class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Cargando…</p>
    @elseif ($estado === 'error')
        <x-estado-vacio titulo="No encontramos ese documento" :descripcion="$error">
            <x-boton variante="secundario">Volver a la búsqueda</x-boton>
        </x-estado-vacio>
    @else
        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
            <h2 class="text-lg font-semibold text-tinta">
                {{ str_replace('_', ' ', $documento['tipo']) }}
            </h2>
            <span class="font-mono text-sm text-tinta-suave">
                {{ $documento['fechaDocumento'] ?? 'Sin fecha legible' }}
            </span>
            <span class="font-mono text-sm text-acento">H.C. {{ $documento['paciente']['numeroHistoria'] }}</span>
            <x-etiqueta-estado :tipo="$this->esIlegible ? 'peligro' : 'exito'">
                {{ $documento['estadoRevision'] }}
            </x-etiqueta-estado>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="flex flex-col gap-2">
                <h3 class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Imagen</h3>
                <div class="aspect-3/4 w-full rounded-lg border border-borde bg-papel"></div>
            </section>

            <section class="flex min-w-0 flex-col gap-2">
                <h3 class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Texto</h3>
                @if ($this->esIlegible)
                    {{-- Se dice explícitamente, no se deja un panel vacío. --}}
                    <div class="rounded-lg border border-borde bg-superficie p-4">
                        <p class="text-sm text-tinta">
                            Esta hoja está marcada como ilegible: no tiene texto asociado.
                        </p>
                        <p class="mt-1 text-sm text-tinta-suave">
                            La imagen sigue disponible y la hoja puede reabrirse para reescanearla.
                        </p>
                    </div>
                @else
                    {{-- El texto del documento se imprime escapado (RNF-012). --}}
                    <pre class="max-h-[32rem] overflow-auto rounded-lg border border-borde bg-superficie p-3 font-mono text-sm whitespace-pre-wrap text-tinta">{{ $this->texto }}</pre>
                @endif
            </section>
        </div>

        <p class="font-mono text-xs text-tinta-suave">
            Digitalizado por {{ $documento['digitalizadoPor']['nombre'] }}
            · motor {{ $documento['motorOcr'] ?? 'sin OCR' }}
        </p>
    @endif
</div>
