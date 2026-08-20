{{-- Resumen y cierre del folder (F05-UT-04). --}}
<div class="flex flex-col gap-4">
    <x-indicador-paso :pasos="['Paciente', 'Captura', 'Revisión', 'Cierre']" :actual="4" />

    @if ($estado === 'cerrada')
        <p role="status" class="rounded-md bg-exito-suave px-3 py-2 text-sm text-exito">
            El folder quedó cerrado y ya cuenta en el avance de la campaña.
        </p>
    @endif

    @if ($error)
        <div role="alert" class="flex flex-col gap-2 rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">
            <p>{{ $error }}</p>
            @if ($sinRevisar !== [])
                <ul class="flex flex-col gap-1">
                    @foreach ($sinRevisar as $hoja)
                        <li>
                            <x-boton
                                variante="terciario"
                                class="text-peligro"
                                ruta="sesiones.revision"
                                :parametros="['sesionId' => $sesionId]"
                            >
                                Hoja {{ $hoja['orden'] ?? $hoja['id'] }} — revisar
                            </x-boton>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-tarjeta-indicador titulo="Hojas del folder" :valor="$resumen['hojas']" unidad="hojas" />
        <x-tarjeta-indicador titulo="Correctas" :valor="$resumen['correctas']" />
        <x-tarjeta-indicador titulo="Corregidas" :valor="$resumen['corregidas']" />
        {{-- Una hoja ilegible aparece en el resumen y no bloquea el cierre. --}}
        <x-tarjeta-indicador titulo="Ilegibles" :valor="$resumen['ilegibles']">
            No impiden cerrar el folder
        </x-tarjeta-indicador>
    </div>

    <section class="flex flex-col gap-2">
        <h2 class="font-medium text-tinta">Hojas por tipo</h2>
        <x-tabla>
            <x-slot:cabecera>
                <x-tabla.encabezado>Tipo</x-tabla.encabezado>
                <x-tabla.encabezado>Hojas</x-tabla.encabezado>
            </x-slot:cabecera>
            @foreach ($resumen['porTipo'] as $tipo => $cantidad)
                <tr wire:key="tipo-{{ $tipo }}">
                    <td class="px-3 py-2">{{ str_replace('_', ' ', $tipo) }}</td>
                    <td class="px-3 py-2 font-mono">{{ $cantidad }}</td>
                </tr>
            @endforeach
        </x-tabla>
    </section>

    @if ($estado !== 'cerrada')
        <div>
            <x-boton wire:click="cerrar" :disabled="$estado === 'confirmando'">
                {{ $estado === 'confirmando' ? 'Cerrando…' : 'Cerrar el folder' }}
            </x-boton>
        </div>
    @endif
</div>
