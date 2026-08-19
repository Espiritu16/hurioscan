{{-- Pantalla de acceso (F01-UT-02). Vista del componente Livewire
     App\Dominios\Usuarios\Componentes\FormularioAcceso.

     Estados: idle, enviando y error de credenciales. El mensaje de error es
     único para credencial inválida, usuario inexistente y usuario inactivo
     (contrato: un solo NO_AUTENTICADO que no revela qué correos existen). --}}
<main class="flex min-h-screen items-center justify-center p-4">
    <div class="flex w-full max-w-sm flex-col gap-6 rounded-lg border border-borde bg-superficie p-6 shadow-sm">
        <div class="flex flex-col gap-1">
            <p class="text-xl font-bold text-acento">HuriosCan</p>
            <p class="font-mono text-xs tracking-wide text-tinta-suave uppercase">Archivo clínico · Minsa</p>
        </div>

        @if ($estado === 'error')
            <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">
                {{ $error }}
            </p>
        @endif

        <form wire:submit="acceder" class="flex flex-col gap-4">
            {{-- Tras un error el foco vuelve aquí, al primer campo marcado. --}}
            <x-campo
                nombre="email"
                etiqueta="Correo"
                tipo="email"
                wire:model="email"
                autocomplete="username"
                :invalido="$estado === 'error'"
                :autofocus="$estado === 'error'"
                required
            />
            <x-campo
                nombre="password"
                etiqueta="Contraseña"
                tipo="password"
                wire:model="password"
                autocomplete="current-password"
                :invalido="$estado === 'error'"
                required
            />

            <label class="flex items-center gap-2 text-sm text-tinta">
                <input type="checkbox" wire:model="recordar"
                    class="rounded border-borde text-acento focus:outline-2 focus:outline-offset-1 focus:outline-acento" />
                Recordarme en este equipo
            </label>

            <x-boton type="submit" class="w-full" :disabled="$estado === 'enviando'">
                <span wire:loading.remove wire:target="acceder">Acceder</span>
                <span wire:loading wire:target="acceder">Accediendo…</span>
            </x-boton>
        </form>
    </div>
</main>
