<?php

namespace App\Dominios\Digitalizacion\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use Livewire\Component;

/**
 * Resumen y cierre del folder (F05-UT-04).
 *
 * El resumen muestra hojas por estado y por tipo antes de confirmar. Una hoja
 * ilegible aparece en el resumen y no impide cerrar; las que siguen sin
 * revisar sí, y se listan con enlace a cada una.
 */
class CierreSesion extends Component
{
    public int $sesionId = 77;

    /** resumen | confirmando | cerrada */
    public string $estado = 'resumen';

    public array $resumen = [
        'hojas' => 14,
        'correctas' => 10,
        'corregidas' => 3,
        'ilegibles' => 1,
        'porTipo' => ['hoja_atencion' => 6, 'receta' => 4, 'laboratorio' => 3, 'consentimiento' => 1],
    ];

    /** Hojas sin revisar que impiden el cierre, con su enlace. */
    public array $sinRevisar = [];

    public ?string $error = null;

    public function cerrar(ServicioDigitalizacion $digitalizacion): void
    {
        $this->estado = 'confirmando';
        $this->error = null;

        try {
            $respuesta = $digitalizacion->cerrarSesion($this->sesionId);
        } catch (ErrorDeAplicacion $e) {
            $this->estado = 'resumen';

            if ($e->getCodigo() === 'SESION_CON_HOJAS_SIN_REVISAR') {
                $this->sinRevisar = $e->getDetalle()['hojas'] ?? [];
                $this->error = 'Quedan hojas sin revisar. Revísalas antes de cerrar el folder.';

                return;
            }

            $this->error = 'No se pudo cerrar el folder.';

            return;
        }

        $this->resumen = $respuesta['resumen'];
        $this->estado = 'cerrada';
    }

    public function render()
    {
        return view('dominios.digitalizacion.cierre');
    }
}
