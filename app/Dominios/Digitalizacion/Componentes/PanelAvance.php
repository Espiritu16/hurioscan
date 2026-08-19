<?php

namespace App\Dominios\Digitalizacion\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Digitalizacion\Contratos\ServicioDigitalizacion;
use Livewire\Component;

/**
 * Panel de avance de la campaña (F07-UT-02).
 *
 * Sin `totalFoldersAcervo` configurado se muestra el avance absoluto y ninguna
 * barra de porcentaje: un 0 % sería engañoso.
 */
class PanelAvance extends Component
{
    /** carga | vacio | error | exito */
    public string $estado = 'carga';

    public ?string $error = null;

    public array $avance = [];

    public function mount(ServicioDigitalizacion $digitalizacion): void
    {
        try {
            $this->avance = $digitalizacion->avance();
        } catch (ErrorDeAplicacion) {
            $this->estado = 'error';
            $this->error = 'No se pudo cargar el avance de la campaña.';

            return;
        }

        $this->estado = 'exito';
    }

    /** Hay porcentaje solo si el establecimiento configuró el total. */
    public function getHayPorcentajeProperty(): bool
    {
        return ($this->avance['totalFoldersAcervo'] ?? null) !== null
            && ($this->avance['porcentaje'] ?? null) !== null;
    }

    public function render()
    {
        return view('dominios.digitalizacion.avance');
    }
}
