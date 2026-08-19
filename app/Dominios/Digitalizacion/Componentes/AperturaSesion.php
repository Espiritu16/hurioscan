<?php

namespace App\Dominios\Digitalizacion\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use Livewire\Component;

/**
 * Apertura de sesión desde el paciente seleccionado (F03-UT-02).
 *
 * Si el paciente ya tiene una sesión sin cerrar se ofrece retomarla con un
 * enlace directo, nunca un error sin salida.
 */
class AperturaSesion extends Component
{
    public int $pacienteId = 1;

    public string $pacienteNombre = '';

    /** idle | enviando | ya_abierta | abierta */
    public string $estado = 'idle';

    public ?string $aviso = null;

    public ?int $sesionExistenteId = null;

    public ?int $sesionId = null;

    public function abrir(ServicioDigitalizacion $digitalizacion): void
    {
        $this->estado = 'enviando';
        $this->aviso = null;
        $this->sesionExistenteId = null;

        try {
            $sesion = $digitalizacion->abrirSesion($this->pacienteId);
        } catch (ErrorDeAplicacion $e) {
            if ($e->getCodigo() === 'SESION_YA_ABIERTA') {
                // Con salida: se ofrece retomar la sesión existente.
                $this->estado = 'ya_abierta';
                $this->sesionExistenteId = $e->getDetalle()['sesionExistenteId'] ?? null;
                $this->aviso = 'Este paciente ya tiene una sesión sin cerrar.';

                return;
            }

            $this->estado = 'idle';
            $this->aviso = 'No se pudo abrir la sesión. Inténtalo de nuevo.';

            return;
        }

        $this->estado = 'abierta';
        $this->sesionId = $sesion['id'];
    }

    public function render()
    {
        return view('dominios.digitalizacion.apertura');
    }
}
