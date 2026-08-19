<?php

namespace App\Dominios\Pacientes\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Pacientes\Contratos\ServicioPacientes;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Línea de tiempo de documentos del paciente (F06-UT-03).
 *
 * Los filtros viajan en la URL, de modo que refrescar o compartir el enlace
 * conserva la vista. Cambiar un filtro actualiza lista y conteo en la misma
 * acción, sin un segundo clic.
 */
class LineaDeTiempo extends Component
{
    public int $pacienteId = 1;

    #[Url]
    public string $tipo = '';

    #[Url]
    public string $desde = '';

    #[Url]
    public string $hasta = '';

    /** carga | vacio | error | exito */
    public string $estado = 'carga';

    public ?string $error = null;

    public array $paciente = [];

    public array $documentos = [];

    public int $total = 0;

    public function mount(ServicioPacientes $pacientes): void
    {
        $this->cargar($pacientes);
    }

    /** Un solo hook para los tres filtros: cambiar uno recarga y reconteo. */
    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['tipo', 'desde', 'hasta'], true)) {
            $this->cargar(app(ServicioPacientes::class));
        }
    }

    public function render()
    {
        return view('dominios.pacientes.linea-de-tiempo');
    }

    private function cargar(ServicioPacientes $pacientes): void
    {
        try {
            $respuesta = $pacientes->lineaDeTiempo($this->pacienteId, [
                'tipo' => $this->tipo,
                'desde' => $this->desde,
                'hasta' => $this->hasta,
            ]);
        } catch (ErrorDeAplicacion) {
            $this->estado = 'error';
            $this->error = 'No se pudo cargar la línea de tiempo.';

            return;
        }

        $this->paciente = $respuesta['paciente'];
        $this->documentos = $respuesta['datos'];
        $this->total = $respuesta['meta']['total'];
        $this->estado = $this->documentos === [] ? 'vacio' : 'exito';
    }
}
