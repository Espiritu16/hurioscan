{{-- Panel de avance de la campaña (F07-UT-02). --}}
<div class="flex flex-col gap-4">
    @if ($estado === 'carga')
        <p role="status" class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Cargando…</p>
    @elseif ($estado === 'error')
        <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">{{ $error }}</p>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-tarjeta-indicador
                titulo="Folders digitalizados"
                :valor="number_format($avance['foldersCerrados'], 0, ',', ' ')"
                :unidad="$this->hayPorcentaje ? '/ '.number_format($avance['totalFoldersAcervo'], 0, ',', ' ') : null"
            >
                @if ($this->hayPorcentaje)
                    <span class="mb-1 block">{{ $avance['porcentaje'] }} % de la meta</span>
                    <span class="block h-1 w-full rounded-full bg-borde">
                        <span class="block h-1 rounded-full bg-acento" style="width: {{ $avance['porcentaje'] }}%"></span>
                    </span>
                @else
                    {{-- Sin total configurado no se dibuja barra ni porcentaje:
                         un 0 % sería engañoso. --}}
                    <span>Total del acervo sin configurar</span>
                @endif
            </x-tarjeta-indicador>

            <x-tarjeta-indicador
                titulo="Hojas procesadas"
                :valor="number_format($avance['hojasProcesadas'], 0, ',', ' ')"
                unidad="hojas"
            />

            <x-tarjeta-indicador
                titulo="Hojas ilegibles"
                :valor="number_format($avance['hojasIlegibles'], 0, ',', ' ')"
            >
                En cola de reescaneo físico
            </x-tarjeta-indicador>

            <x-tarjeta-indicador
                titulo="Ritmo semanal"
                :valor="$avance['ritmoSemanal']"
                unidad="folders/sem"
            >
                Sesiones cerradas en los últimos 7 días
            </x-tarjeta-indicador>
        </div>

        <section class="flex flex-col gap-2">
            <h2 class="font-medium text-tinta">Sesiones recientes</h2>
            @if ($avance['sesionesRecientes'] === [])
                <x-estado-vacio
                    titulo="Todavía no hay sesiones"
                    descripcion="Cuando se digitalice el primer folder aparecerá aquí."
                >
                    <x-boton variante="secundario">Iniciar una sesión</x-boton>
                </x-estado-vacio>
            @else
                <x-tabla>
                    <x-slot:cabecera>
                        <x-tabla.encabezado>N.º H.C.</x-tabla.encabezado>
                        <x-tabla.encabezado>Paciente</x-tabla.encabezado>
                        <x-tabla.encabezado>Hojas</x-tabla.encabezado>
                        <x-tabla.encabezado>Operador</x-tabla.encabezado>
                        <x-tabla.encabezado>Estado</x-tabla.encabezado>
                    </x-slot:cabecera>
                    @foreach ($avance['sesionesRecientes'] as $sesion)
                        <tr wire:key="reciente-{{ $sesion['id'] }}">
                            <td class="px-3 py-2 font-mono text-acento">{{ $sesion['paciente']['numeroHistoria'] }}</td>
                            <td class="px-3 py-2">{{ $sesion['paciente']['apellidos'] }}, {{ $sesion['paciente']['nombres'] }}</td>
                            <td class="px-3 py-2 font-mono">{{ $sesion['hojas'] }}</td>
                            <td class="px-3 py-2">{{ $sesion['operador'] }}</td>
                            <td class="px-3 py-2">
                                <x-etiqueta-estado :tipo="$sesion['estado'] === 'CERRADA' ? 'exito' : 'advertencia'">
                                    {{ $sesion['estado'] }}
                                </x-etiqueta-estado>
                            </td>
                        </tr>
                    @endforeach
                </x-tabla>
            @endif
        </section>
    @endif
</div>
