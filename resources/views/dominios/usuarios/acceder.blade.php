{{-- Pantalla de acceso (F01-UT-02). Presentación y estados; la autenticación
     real es de B01. Estados: idle, enviando y error de credenciales.
     El mensaje de error es único para credencial inválida, usuario
     inexistente y usuario inactivo (contrato: NO_AUTENTICADO). --}}
@props(['estado' => 'idle', 'error' => null, 'email' => ''])

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Acceder — HuriosCan</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <main class="flex min-h-screen items-center justify-center p-4">
            <div class="flex w-full max-w-sm flex-col gap-6 rounded-lg border border-borde bg-superficie p-6 shadow-sm">
                <div class="flex flex-col gap-1">
                    <p class="text-xl font-bold text-acento">HuriosCan</p>
                    <p class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Archivo clínico · Minsa</p>
                </div>

                @if ($estado === 'error')
                    {{-- Mensaje único: no revela si el correo existe. --}}
                    <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">
                        {{ $error ?? 'Correo o contraseña incorrectos.' }}
                    </p>
                @endif

                <form method="POST" class="flex flex-col gap-4" autocomplete="on">
                    {{-- Tras un error el foco vuelve aquí, al primer campo marcado. --}}
                    <x-campo
                        nombre="email"
                        etiqueta="Correo"
                        tipo="email"
                        :value="$email"
                        autocomplete="username"
                        :invalido="$estado === 'error'"
                        :autofocus="$estado === 'error'"
                        required
                    />
                    <x-campo
                        nombre="password"
                        etiqueta="Contraseña"
                        tipo="password"
                        autocomplete="current-password"
                        :invalido="$estado === 'error'"
                        required
                    />

                    <label class="flex items-center gap-2 text-sm text-tinta">
                        <input type="checkbox" name="recordar" value="1"
                            class="rounded border-borde text-acento focus:outline-2 focus:outline-offset-1 focus:outline-acento" />
                        Recordarme en este equipo
                    </label>

                    <x-boton type="submit" class="w-full" :disabled="$estado === 'enviando'">
                        {{ $estado === 'enviando' ? 'Accediendo…' : 'Acceder' }}
                    </x-boton>
                </form>
            </div>
        </main>
    </body>
</html>
