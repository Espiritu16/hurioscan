{{-- Apertura de sesión (F03-UT-02). Una sesión ya abierta no es un callejón
     sin salida: se ofrece retomarla. --}}
<div class="flex max-w-xl flex-col gap-4">
    @if ($estado === 'ya_abierta')
        <div role="status" class="flex flex-col gap-2 rounded-md bg-advertencia-suave px-3 py-2 text-sm text-advertencia">
            <p>{{ $aviso }}</p>
            <div>
                <x-boton
                    variante="secundario"
                    ruta="sesiones.detalle"
                    :parametros="['sesionId' => $sesionExistenteId]"
                >
                    Retomar la sesión {{ $sesionExistenteId }}
                </x-boton>
            </div>
        </div>
    @elseif ($estado === 'abierta')
        <p role="status" class="rounded-md bg-exito-suave px-3 py-2 text-sm text-exito">
            Sesión {{ $sesionId }} abierta. Continúa con la captura.
        </p>
    @elseif ($aviso)
        <p role="alert" class="rounded-md bg-peligro-suave px-3 py-2 text-sm text-peligro">{{ $aviso }}</p>
    @endif

    @if ($estado !== 'ya_abierta' && $estado !== 'abierta')
        <x-boton wire:click="abrir" :disabled="$estado === 'enviando'">
            {{ $estado === 'enviando' ? 'Abriendo…' : 'Iniciar digitalización' }}
        </x-boton>
    @endif
</div>
