<?php

namespace App\Dominios\Digitalizacion\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use Livewire\Component;

/**
 * Sesiones pendientes y reanudación (F03-UT-04).
 *
 * Retomar lleva a captura o a revisión según el estado de la sesión.
 */
class SesionesPendientes extends Component
{
    /** carga | vacio | error | exito */
    public string $estado = 'carga';

    public ?string $error = null;

    public array $sesiones = [];

    public function mount(ServicioDigitalizacion $digitalizacion): void
    {
        try {
            $respuesta = $digitalizacion->sesionesPendientes();
        } catch (ErrorDeAplicacion) {
            $this->estado = 'error';
            $this->error = 'No se pudieron cargar las sesiones pendientes.';

            return;
        }

        $this->sesiones = $respuesta['datos'];
        $this->estado = $this->sesiones === [] ? 'vacio' : 'exito';
    }

    /** Una sesión ABIERTA vuelve a captura; una EN_REVISION, a revisión. */
    public function destinoDe(string $estadoSesion): string
    {
        return $estadoSesion === 'EN_REVISION' ? 'revisión' : 'captura';
    }

    /** El nombre de ruta que corresponde a ese mismo destino. */
    public function rutaDe(string $estadoSesion): string
    {
        return $estadoSesion === 'EN_REVISION' ? 'sesiones.revision' : 'sesiones.detalle';
    }

    public function render()
    {
        return view('dominios.digitalizacion.pendientes');
    }
}
