<?php

namespace App\Dominios\Documentos\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Documentos\Contratos\ServicioDocumentos;
use Livewire\Component;

/**
 * Cola de hojas por reescanear (F05-UT-04).
 *
 * Tras reabrir una hoja, esta desaparece de la lista.
 */
class HojasIlegibles extends Component
{
    /** carga | vacio | error | exito */
    public string $estado = 'carga';

    public array $hojas = [];

    public ?string $error = null;

    public ?string $aviso = null;

    public function mount(ServicioDocumentos $documentos): void
    {
        $this->cargar($documentos);
    }

    public function reabrir(int $documentoId, ServicioDocumentos $documentos): void
    {
        $this->aviso = null;

        try {
            $documentos->reabrirRevision($documentoId);
        } catch (ErrorDeAplicacion $e) {
            $this->error = $e->getMessage();

            return;
        }

        // Reabierta deja de ser ilegible: sale de la cola.
        $this->hojas = array_values(array_filter(
            $this->hojas,
            fn (array $h) => $h['documentoId'] !== $documentoId,
        ));

        $this->estado = $this->hojas === [] ? 'vacio' : 'exito';
        $this->aviso = 'La hoja volvió a revisión.';
    }

    public function render()
    {
        return view('dominios.documentos.ilegibles');
    }

    private function cargar(ServicioDocumentos $documentos): void
    {
        try {
            $respuesta = $documentos->ilegibles();
        } catch (ErrorDeAplicacion) {
            $this->estado = 'error';
            $this->error = 'No se pudo cargar la cola de hojas ilegibles.';

            return;
        }

        $this->hojas = $respuesta['datos'];
        $this->estado = $this->hojas === [] ? 'vacio' : 'exito';
    }
}
