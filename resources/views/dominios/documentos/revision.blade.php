{{-- Revisión del OCR (F05-UT-02 y UT-03).
     El texto extraído se muestra siempre escapado: Blade escapa por defecto
     con {{ }}, y en esta vista no se usa {!! !!} en ningún punto (RNF-012). --}}
<div class="flex flex-col gap-4">
    <x-indicador-paso :pasos="['Paciente', 'Captura', 'Revisión', 'Cierre']" :actual="3" />

    <div class="flex flex-wrap items-center gap-3">
        <h2 class="font-medium text-tinta">
            Hoja {{ $indice + 1 }} de {{ count($hojas) }}
        </h2>
        <x-etiqueta-estado>{{ $this->revisadas }} / {{ count($hojas) }} revisadas</x-etiqueta-estado>
        <div class="ms-auto flex gap-2">
            <x-boton variante="secundario" wire:click="irA({{ $indice - 1 }})" :disabled="$indice === 0">
                Anterior
            </x-boton>
            <x-boton variante="secundario" wire:click="irA({{ $indice + 1 }})"
                :disabled="$indice >= count($hojas) - 1">
                Siguiente
            </x-boton>
        </div>
    </div>

    @if ($aviso)
        <p role="status" class="rounded-md bg-acento-suave px-3 py-2 text-sm text-tinta">{{ $aviso }}</p>
    @endif

    {{-- Conflicto de versión: se muestra el texto vigente y decide la persona.
         No se reenvía en silencio con la versión nueva. --}}
    @if ($textoEnConflicto !== null)
        <div role="alert" class="flex flex-col gap-3 rounded-md border border-advertencia bg-advertencia-suave p-3">
            <p class="text-sm font-medium text-advertencia">Texto vigente guardado por otra sesión</p>
            <pre class="max-h-40 overflow-auto rounded bg-superficie p-2 font-mono text-xs whitespace-pre-wrap text-tinta">{{ $textoEnConflicto }}</pre>
            <div class="flex flex-wrap gap-2">
                <x-boton variante="secundario" wire:click="tomarTextoVigente">Usar el texto vigente</x-boton>
                <x-boton variante="secundario" wire:click="conservarMiTexto">Conservar el mío</x-boton>
            </div>
        </div>
    @endif

    {{-- En 360 px los dos paneles se apilan; desde lg van en paralelo. --}}
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="flex flex-col gap-2">
            <h3 class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Imagen escaneada</h3>
            <div class="aspect-3/4 w-full rounded-lg border border-borde bg-papel"></div>
        </section>

        <section class="flex min-w-0 flex-col gap-2">
            <h3 class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Texto extraído</h3>

            @if ($this->hoja['estadoRevision'] === 'PENDIENTE_OCR')
                {{-- Nunca un panel vacío sin explicación. --}}
                <div class="flex flex-col gap-2 rounded-lg border border-borde bg-superficie p-4">
                    <p class="text-sm text-tinta">El OCR de esta hoja todavía está corriendo.</p>
                    <p class="text-sm text-tinta-suave">
                        Puedes revisar otra hoja mientras tanto; esta quedará disponible al terminar.
                    </p>
                </div>
            @elseif ($this->hoja['estadoRevision'] === 'ILEGIBLE')
                <div class="rounded-lg border border-borde bg-superficie p-4">
                    <p class="text-sm text-tinta">Esta hoja se marcó como ilegible: no tiene texto asociado.</p>
                </div>
            @else
                <textarea
                    wire:model="texto"
                    rows="14"
                    aria-label="Texto extraído de la hoja"
                    class="w-full rounded-lg border border-borde bg-superficie p-3 font-mono text-sm text-tinta focus:outline-2 focus:outline-offset-1 focus:outline-acento"
                >{{ $texto }}</textarea>
                <div class="flex flex-wrap gap-2">
                    <x-boton wire:click="guardar">Guardar corrección</x-boton>
                </div>
            @endif
        </section>
    </div>

    <div class="flex flex-wrap items-center gap-2 border-t border-borde pt-4">
        <span class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Estado de la hoja</span>
        <x-boton variante="secundario" wire:click="marcar('CORRECTA')">Correcta</x-boton>
        <x-boton variante="secundario" wire:click="marcar('CORREGIDA')">Corregida</x-boton>
        <x-boton variante="secundario" wire:click="marcar('ILEGIBLE')">Ilegible</x-boton>
    </div>
</div>
