{{-- Layout base (F00-UT-04): esqueleto HTML, menú lateral por rol, cabecera
     y ranuras para la barra de paciente y las acciones de la cabecera. --}}
@props(['titulo', 'rol' => null])

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $titulo }} — HuriosCan</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="relative flex min-h-screen">
            <x-menu-lateral :rol="$rol">
                {{ $menuPie ?? '' }}
            </x-menu-lateral>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="flex items-center gap-3 border-b border-borde bg-superficie px-4 py-3">
                    <button
                        type="button"
                        data-alterna-menu
                        aria-controls="menu-lateral"
                        aria-expanded="false"
                        class="rounded-md border border-borde p-2 text-tinta focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-acento lg:hidden"
                    >
                        <span class="sr-only">Abrir menú</span>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="truncate text-lg font-semibold text-tinta">{{ $titulo }}</h1>
                    @if (isset($acciones))
                        <div class="ms-auto flex items-center gap-2">{{ $acciones }}</div>
                    @endif
                </header>

                {{ $barra ?? '' }}

                <main class="flex-1 p-4 lg:p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
