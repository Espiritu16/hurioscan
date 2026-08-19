{{-- Captura del folder (F03-UT-03). Las tres vías de agregar hojas son
     operables con el pulgar en 360 px: ocupan el ancho completo y se apilan.
     Una hoja rechazada muestra el motivo en su propia tarjeta y no borra las
     que ya estaban. --}}
<div class="flex flex-col gap-4">
    <x-indicador-paso :pasos="['Paciente', 'Captura', 'Revisión', 'Cierre']" :actual="2" />

    @if ($enviadaARevision)
        <p role="status" class="rounded-md bg-exito-suave px-3 py-2 text-sm text-exito">
            La sesión pasó a revisión.
        </p>
    @endif

    @if ($errorEnvio)
        <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">{{ $errorEnvio }}</p>
    @endif

    {{-- Tres vías de captura. En 360 px se apilan a ancho completo; el área
         táctil mínima la da el py-4. --}}
    <div class="grid gap-3 sm:grid-cols-3">
        <label class="flex cursor-pointer flex-col gap-1 rounded-lg bg-acento px-4 py-4 text-sobre-acento focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-acento">
            <span class="font-medium">Tomar foto</span>
            <span class="font-mono text-xs opacity-80 uppercase">Cámara</span>
            <input type="file" accept="image/*" capture="environment" wire:model="archivosCamara" class="sr-only" />
        </label>

        <label class="flex cursor-pointer flex-col gap-1 rounded-lg border border-borde bg-superficie px-4 py-4 text-tinta focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-acento">
            <span class="font-medium">Elegir de la galería</span>
            <span class="font-mono text-xs text-tinta-suave uppercase">Imágenes</span>
            <input type="file" accept="image/*" multiple wire:model="archivosGaleria" class="sr-only" />
        </label>

        <label class="flex cursor-pointer flex-col gap-1 rounded-lg border border-dashed border-borde bg-superficie px-4 py-4 text-tinta focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-acento">
            <span class="font-medium">Subir archivos</span>
            <span class="font-mono text-xs text-tinta-suave uppercase">JPG · PNG · PDF</span>
            <input type="file" accept="image/*,application/pdf" multiple wire:model="archivos" class="sr-only" />
        </label>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <x-campo nombre="tipo" etiqueta="Tipo de las hojas siguientes">
            <select id="tipo" wire:model="tipo"
                class="rounded-md border border-borde bg-superficie px-3 py-2 text-sm text-tinta focus:outline-2 focus:outline-offset-1 focus:outline-acento">
                <option value="hoja_atencion">Hoja de atención</option>
                <option value="receta">Receta médica</option>
                <option value="laboratorio">Laboratorio</option>
                <option value="epicrisis">Epicrisis</option>
                <option value="consentimiento">Consentimiento</option>
                <option value="otro">Otro</option>
            </select>
        </x-campo>
        <x-campo nombre="fechaDocumento" etiqueta="Fecha del documento" tipo="date" wire:model="fechaDocumento" />
    </div>

    {{-- Rechazos: uno por hoja, sin tocar las ya capturadas. --}}
    @foreach ($rechazos as $indice => $rechazo)
        <div wire:key="rechazo-{{ $indice }}" role="alert"
            class="flex items-center gap-3 rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">
            <span class="font-medium">{{ $rechazo['nombre'] }}</span>
            <span>{{ $rechazo['motivo'] }}</span>
            <x-boton variante="terciario" class="ms-auto text-peligro" wire:click="descartarRechazo({{ $indice }})">
                Descartar
            </x-boton>
        </div>
    @endforeach

    <div class="flex items-center gap-3">
        <h2 class="font-medium text-tinta">Hojas capturadas</h2>
        <x-etiqueta-estado>{{ count($hojas) }}</x-etiqueta-estado>
        <span class="font-mono text-xs text-tinta-suave uppercase">El orden reproduce el folder físico</span>
    </div>

    @if ($hojas === [])
        <x-estado-vacio
            titulo="Todavía no hay hojas capturadas"
            descripcion="Usa la cámara, la galería o el selector de archivos para agregar la primera hoja del folder."
        />
    @else
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
            @foreach ($hojas as $hoja)
                <li wire:key="hoja-{{ $hoja['id'] }}"
                    class="flex flex-col gap-2 rounded-lg border border-borde bg-superficie p-2">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs text-tinta-suave">{{ str_pad((string) $hoja['orden'], 2, '0', STR_PAD_LEFT) }}</span>
                        <x-etiqueta-estado tipo="advertencia">OCR</x-etiqueta-estado>
                    </div>
                    <div class="aspect-3/4 rounded bg-papel"></div>
                    <p class="truncate font-mono text-xs text-tinta-suave">{{ $hoja['fechaDocumento'] ?? 'Sin fecha' }}</p>
                    <x-boton variante="terciario" wire:click="quitar({{ $hoja['id'] }})">Quitar</x-boton>
                </li>
            @endforeach
        </ul>

        <div>
            <x-boton wire:click="enviarARevision">Continuar a revisión</x-boton>
        </div>
    @endif
</div>
