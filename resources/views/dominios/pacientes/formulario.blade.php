{{-- Alta de paciente con autocompletado por DNI (F02-UT-03).
     Los cuatro desenlaces se ven distintos y en los cuatro el formulario
     queda utilizable. --}}
<div class="flex max-w-2xl flex-col gap-6">
    @if ($guardado)
        <p role="status" class="rounded-md bg-exito-suave px-3 py-2 text-sm text-exito">
            El paciente quedó registrado.
        </p>
    @endif

    <section class="flex flex-col gap-3 rounded-lg border border-borde bg-superficie p-4">
        <h2 class="font-medium text-tinta">Traer datos por DNI</h2>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <x-campo nombre="dni" etiqueta="DNI" wire:model="dni" placeholder="8 dígitos" class="sm:flex-1" />
            <x-boton
                wire:click="consultarDni"
                :disabled="$estadoConsulta === 'consultando'"
                variante="secundario"
            >
                {{ $estadoConsulta === 'consultando' ? 'Consultando…' : 'Traer datos' }}
            </x-boton>
        </div>

        @if ($estadoConsulta === 'ya_registrado')
            <div role="status" class="flex flex-col gap-2 rounded-md bg-advertencia-suave px-3 py-2 text-sm text-advertencia">
                <p>{{ $avisoConsulta }}</p>
                <p class="text-tinta">
                    {{ $pacienteExistente['apellidos'] }}, {{ $pacienteExistente['nombres'] }}
                    · H.C. <span class="font-mono">{{ $pacienteExistente['numeroHistoria'] }}</span>
                </p>
                <div><x-boton variante="secundario">Abrir su folder</x-boton></div>
            </div>
        @elseif ($estadoConsulta === 'precargado')
            <p role="status" class="rounded-md bg-exito-suave px-3 py-2 text-sm text-exito">{{ $avisoConsulta }}</p>
        @elseif (in_array($estadoConsulta, ['no_encontrado', 'proveedor_no_disponible'], true))
            {{-- Ninguno bloquea el alta: el formulario sigue completo y editable. --}}
            <p role="status" class="rounded-md bg-advertencia-suave px-3 py-2 text-sm text-advertencia">
                {{ $avisoConsulta }}
            </p>
        @endif
    </section>

    {{-- El alta permanece disponible salvo que el paciente ya exista. --}}
    @if ($estadoConsulta !== 'ya_registrado')
        <form wire:submit="guardar" class="flex flex-col gap-4">
            @if ($errorAlta)
                <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">{{ $errorAlta }}</p>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <x-campo nombre="numeroHistoria" etiqueta="N.º de historia clínica" wire:model="numeroHistoria" required />
                <x-campo nombre="fechaNacimiento" etiqueta="Fecha de nacimiento" tipo="date" wire:model="fechaNacimiento" />
                <x-campo nombre="apellidos" etiqueta="Apellidos" wire:model.blur="apellidos" required />
                <x-campo nombre="nombres" etiqueta="Nombres" wire:model.blur="nombres" required />
            </div>

            <p class="font-mono text-xs tracking-wide text-tinta-suave uppercase">
                Origen de los datos: {{ $origenDatos }}
            </p>

            <div><x-boton type="submit">Guardar paciente</x-boton></div>
        </form>
    @endif
</div>
