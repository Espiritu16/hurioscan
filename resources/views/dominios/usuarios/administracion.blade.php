{{-- Administración de usuarios (F07-UT-03). Solo se muestran los campos que
     el contrato devuelve; el hash de contraseña no llega ni se imprime. --}}
<div class="flex flex-col gap-4">
    @if ($aviso)
        <p role="status" class="rounded-md bg-exito-suave px-3 py-2 text-sm text-exito">{{ $aviso }}</p>
    @endif

    @if ($error)
        <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">{{ $error }}</p>
    @endif

    @if ($estado === 'carga')
        <p role="status" class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Cargando…</p>
    @elseif ($estado === 'vacio')
        <x-estado-vacio
            titulo="No hay usuarios registrados"
            descripcion="Crea el primer usuario para que el equipo pueda acceder al sistema."
        >
            <x-boton variante="secundario">Crear usuario</x-boton>
        </x-estado-vacio>
    @elseif ($estado === 'exito')
        <x-tabla>
            <x-slot:cabecera>
                <x-tabla.encabezado>Nombre</x-tabla.encabezado>
                <x-tabla.encabezado>Correo</x-tabla.encabezado>
                <x-tabla.encabezado>Rol</x-tabla.encabezado>
                <x-tabla.encabezado>Estado</x-tabla.encabezado>
                <x-tabla.encabezado><span class="sr-only">Acciones</span></x-tabla.encabezado>
            </x-slot:cabecera>
            @foreach ($usuarios as $usuario)
                <tr wire:key="usuario-{{ $usuario['id'] }}">
                    <td class="px-3 py-2">{{ $usuario['nombre'] }}</td>
                    <td class="px-3 py-2 font-mono text-xs">{{ $usuario['email'] }}</td>
                    <td class="px-3 py-2">
                        <label class="sr-only" for="rol-{{ $usuario['id'] }}">
                            Rol de {{ $usuario['nombre'] }}
                        </label>
                        <select
                            id="rol-{{ $usuario['id'] }}"
                            wire:change="cambiarRol({{ $usuario['id'] }}, $event.target.value)"
                            class="rounded-md border border-borde bg-superficie px-2 py-1 text-sm text-tinta focus:outline-2 focus:outline-offset-1 focus:outline-acento"
                        >
                            @foreach (['operador', 'consulta', 'administrador'] as $rol)
                                <option value="{{ $rol }}" @selected($usuario['rol'] === $rol)>{{ $rol }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <x-etiqueta-estado :tipo="$usuario['activo'] ? 'exito' : 'neutro'">
                            {{ $usuario['activo'] ? 'Activo' : 'Inactivo' }}
                        </x-etiqueta-estado>
                    </td>
                    <td class="px-3 py-2">
                        <x-boton
                            variante="terciario"
                            wire:click="cambiarActividad({{ $usuario['id'] }}, {{ $usuario['activo'] ? 'false' : 'true' }})"
                        >
                            {{ $usuario['activo'] ? 'Desactivar' : 'Activar' }}
                        </x-boton>
                    </td>
                </tr>
            @endforeach
        </x-tabla>
    @endif
</div>
