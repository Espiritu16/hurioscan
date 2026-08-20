{{-- Menú lateral con visibilidad por rol (F00-UT-04). Ocultar una opción es
     ayuda de interfaz, no seguridad: el backend valida siempre (RNF-013).
     Consume rutas por nombre; un nombre aún no declarado en routes/web.php
     se muestra deshabilitado gracias a Route::has(), sin romper la vista. --}}
@props(['rol'])

@php
    use Illuminate\Support\Facades\Route;

    $secciones = [
        'Resumen' => [
            ['etiqueta' => 'Panel de avance', 'ruta' => 'avance', 'roles' => ['operador', 'administrador']],
        ],
        'Sesión de digitalización' => [
            ['etiqueta' => 'Pacientes', 'ruta' => 'pacientes', 'roles' => ['operador', 'consulta', 'administrador']],
            ['etiqueta' => 'Sesiones pendientes', 'ruta' => 'sesiones.pendientes', 'roles' => ['operador', 'administrador']],
        ],
        'Consulta del archivo' => [
            ['etiqueta' => 'Búsqueda', 'ruta' => 'buscar', 'roles' => ['operador', 'consulta', 'administrador']],
            ['etiqueta' => 'Hojas ilegibles', 'ruta' => 'ilegibles', 'roles' => ['operador', 'administrador']],
        ],
        'Administración' => [
            ['etiqueta' => 'Usuarios', 'ruta' => 'usuarios', 'roles' => ['administrador']],
            ['etiqueta' => 'Auditoría', 'ruta' => 'auditoria', 'roles' => ['administrador']],
        ],
    ];
@endphp

<aside
    id="menu-lateral"
    {{ $attributes->class([
        'absolute inset-y-0 left-0 z-50 hidden w-64 flex-col gap-6 overflow-y-auto border-r border-borde bg-superficie p-4',
        'lg:static lg:flex',
    ]) }}
>
    <p class="text-lg font-bold text-acento">HuriosCan</p>

    <nav class="flex flex-col gap-6" aria-label="Menú principal">
        @foreach ($secciones as $titulo => $opciones)
            @php
                $visibles = array_filter($opciones, fn ($opcion) => in_array($rol, $opcion['roles'], true));
            @endphp
            @if ($visibles)
                <div class="flex flex-col gap-1">
                    <p class="px-2 font-mono text-xs font-medium tracking-wide text-tinta-suave uppercase">{{ $titulo }}</p>
                    @foreach ($visibles as $opcion)
                        @php $existe = Route::has($opcion['ruta']); @endphp
                        @php $activa = $existe && request()->routeIs($opcion['ruta']); @endphp
                        <a
                            @if ($existe) href="{{ route($opcion['ruta']) }}" @else aria-disabled="true" @endif
                            @if ($activa) aria-current="page" @endif
                            @class([
                                'rounded-md px-2 py-1.5 text-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-acento',
                                'bg-acento-suave font-medium text-acento' => $activa,
                                'text-tinta hover:bg-acento-suave' => $existe && ! $activa,
                                'cursor-not-allowed text-tinta-suave' => ! $existe,
                            ])
                        >
                            {{ $opcion['etiqueta'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>

    @if ($slot->isNotEmpty())
        <div class="mt-auto">{{ $slot }}</div>
    @endif
</aside>
