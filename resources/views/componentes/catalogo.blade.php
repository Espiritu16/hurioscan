{{-- Catálogo de componentes (F00-UT-05): cada componente con sus variantes y
     estados, para revisar el diseño sin abrir una pantalla real. Solo en
     entorno local (lo garantiza la ruta, declarada por Coordinación). --}}
@php $rol = request()->query('rol', 'administrador'); @endphp

<x-layout titulo="Catálogo de componentes" :rol="$rol">
    <x-slot:acciones>
        <x-buscador placeholder="Buscador de la cabecera (demo)" class="hidden w-64 sm:block" />
    </x-slot:acciones>

    <x-slot:barra>
        <x-barra-paciente nombre="Mamani Choque, Rosa Elena" historia="04-118-2297">
            DNI 41822703 · F. nac. 12/03/1979
            <x-slot:acciones>
                <x-boton variante="secundario" class="border-sobre-acento/40 bg-transparent text-sobre-acento hover:bg-sobre-acento/10">
                    Cambiar paciente
                </x-boton>
            </x-slot:acciones>
        </x-barra-paciente>
    </x-slot:barra>

    <div class="mx-auto flex max-w-4xl flex-col gap-10">
        <section class="flex flex-col gap-2">
            <h2 class="text-xl font-semibold">Menú lateral por rol</h2>
            <p class="text-sm text-tinta-suave">
                El menú de esta misma página se filtra según el rol elegido. Las opciones cuya ruta
                todavía no está declarada aparecen deshabilitadas.
            </p>
            <div class="flex gap-2">
                @foreach (['operador', 'consulta', 'administrador'] as $opcionRol)
                    <a
                        href="{{ request()->fullUrlWithQuery(['rol' => $opcionRol]) }}"
                        @class([
                            'rounded-md px-3 py-1.5 text-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-acento',
                            'bg-acento text-sobre-acento' => $rol === $opcionRol,
                            'border border-borde bg-superficie text-tinta hover:bg-acento-suave' => $rol !== $opcionRol,
                        ])
                    >
                        {{ $opcionRol }}
                    </a>
                @endforeach
            </div>
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold">Tokens de color</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ([
                    'acento' => 'bg-acento',
                    'fondo' => 'bg-fondo',
                    'superficie' => 'bg-superficie',
                    'papel' => 'bg-papel',
                    'tinta' => 'bg-tinta',
                    'tinta-suave' => 'bg-tinta-suave',
                    'borde' => 'bg-borde',
                    'exito' => 'bg-exito',
                    'advertencia' => 'bg-advertencia',
                    'peligro' => 'bg-peligro',
                ] as $nombre => $clase)
                    <div class="flex items-center gap-2">
                        <span class="{{ $clase }} size-8 shrink-0 rounded-md border border-borde"></span>
                        <code class="font-mono text-xs">{{ $nombre }}</code>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold">Botón</h2>
            <div class="flex flex-wrap items-center gap-3">
                <x-boton>Primario</x-boton>
                <x-boton variante="secundario">Secundario</x-boton>
                <x-boton variante="terciario">Terciario</x-boton>
                <x-boton disabled>Deshabilitado</x-boton>
            </div>
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold">Campo</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-campo nombre="dni" etiqueta="DNI" placeholder="8 dígitos" />
                <x-campo nombre="correo" etiqueta="Correo" tipo="email" error="El correo no tiene un formato válido." />
                <x-campo nombre="tipo-documento" etiqueta="Tipo de documento (control por slot)">
                    <select
                        id="tipo-documento"
                        name="tipo-documento"
                        class="rounded-md border border-borde bg-superficie px-3 py-2 text-sm text-tinta focus:outline-2 focus:outline-offset-1 focus:outline-acento"
                    >
                        <option>Hoja de atención</option>
                        <option>Receta médica</option>
                    </select>
                </x-campo>
            </div>
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold">Buscador</h2>
            <x-buscador etiqueta="Buscar en el archivo" placeholder="N.º H.C., DNI o texto del documento" />
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold">Etiqueta de estado</h2>
            <div class="flex flex-wrap gap-3">
                <x-etiqueta-estado tipo="exito">Digitalizado</x-etiqueta-estado>
                <x-etiqueta-estado tipo="advertencia">Parcial</x-etiqueta-estado>
                <x-etiqueta-estado tipo="peligro">Ilegible</x-etiqueta-estado>
                <x-etiqueta-estado>Corregida</x-etiqueta-estado>
            </div>
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold">Tabla</h2>
            <x-tabla>
                <x-slot:cabecera>
                    <x-tabla.encabezado url="#" orden="asc">N.º H.C.</x-tabla.encabezado>
                    <x-tabla.encabezado url="#">Paciente</x-tabla.encabezado>
                    <x-tabla.encabezado>Hojas</x-tabla.encabezado>
                    <x-tabla.encabezado>Estado</x-tabla.encabezado>
                </x-slot:cabecera>
                @foreach ([
                    ['04-118-2297', 'Mamani Choque, Rosa E.', 14, 'exito', 'Digitalizado'],
                    ['04-117-8840', 'Huamán Ríos, Julio C.', 22, 'advertencia', 'Parcial'],
                    ['04-116-4412', 'Ccalla Ancco, Marina', 9, 'peligro', 'Ilegible'],
                ] as [$historia, $paciente, $hojas, $tipo, $estado])
                    <tr>
                        <td class="px-3 py-2 font-mono text-acento">{{ $historia }}</td>
                        <td class="px-3 py-2">{{ $paciente }}</td>
                        <td class="px-3 py-2 font-mono">{{ $hojas }}</td>
                        <td class="px-3 py-2"><x-etiqueta-estado :tipo="$tipo">{{ $estado }}</x-etiqueta-estado></td>
                    </tr>
                @endforeach
            </x-tabla>
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold">Tarjeta de indicador</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <x-tarjeta-indicador titulo="Folders digitalizados" valor="1 248" unidad="/ 3 410">
                    36.6 % de la meta · faltan 2 162
                </x-tarjeta-indicador>
                <x-tarjeta-indicador titulo="Hojas procesadas" valor="18 942" unidad="hojas">
                    Promedio 15.2 hojas / folder
                </x-tarjeta-indicador>
                <x-tarjeta-indicador titulo="Ritmo semanal" valor="186" unidad="folders/sem" />
            </div>
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold">Estado vacío</h2>
            <x-estado-vacio
                titulo="¿El paciente no existe en el sistema?"
                descripcion="Registra la ficha mínima y continúa con la captura sin salir de la sesión."
            >
                <x-boton variante="secundario">Registrar paciente nuevo</x-boton>
            </x-estado-vacio>
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold">Indicador de paso</h2>
            <div class="flex flex-col gap-4 rounded-lg border border-borde bg-superficie p-4">
                <x-indicador-paso :pasos="['Paciente', 'Captura', 'Revisión', 'Cierre']" :actual="2" />
            </div>
        </section>
    </div>
</x-layout>
