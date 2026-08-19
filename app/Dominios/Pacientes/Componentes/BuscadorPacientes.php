<?php

namespace App\Dominios\Pacientes\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Pacientes\Contratos\ServicioPacientes;
use Livewire\Component;

/**
 * Búsqueda y listado de pacientes (F02-UT-02).
 *
 * Estados visibles: idle, buscando, vacío, error y éxito. El estado vacío
 * ofrece registrar, salvo al rol `consulta`, que no registra pacientes.
 */
class BuscadorPacientes extends Component
{
    public string $termino = '';

    public int $pagina = 1;

    /** idle | buscando | vacio | error | exito */
    public string $estado = 'idle';

    public ?string $error = null;

    public array $resultados = [];

    public array $meta = [];

    /**
     * Rol del actor; en producción lo resuelve la sesión, no la vista.
     *
     * El default es **sin rol**, no uno concreto: mientras la sesión no
     * aporte uno, no se ofrece ninguna acción reservada. Un default con
     * privilegios mostraría la acción de registrar a quien no la tiene
     * (RNF-013), aunque el backend la rechazara después.
     */
    public string $rol = '';

    public function mount(ServicioPacientes $pacientes): void
    {
        $this->buscar($pacientes);
    }

    /** Al cambiar el término la página vuelve a 1: los resultados son otros. */
    public function updatedTermino(): void
    {
        $this->pagina = 1;
        $this->buscar(app(ServicioPacientes::class));
    }

    public function irAPagina(int $pagina): void
    {
        $this->pagina = max(1, $pagina);
        $this->buscar(app(ServicioPacientes::class));
    }

    public function buscar(ServicioPacientes $pacientes): void
    {
        $this->estado = 'buscando';
        $this->error = null;

        try {
            $respuesta = $pacientes->buscar($this->termino, $this->pagina);
        } catch (ErrorDeAplicacion $e) {
            $this->estado = 'error';
            $this->error = 'No se pudo completar la búsqueda. Inténtalo de nuevo.';
            $this->resultados = [];

            return;
        }

        $this->resultados = $respuesta['datos'];
        $this->meta = $respuesta['meta'];
        $this->estado = $this->resultados === [] ? 'vacio' : 'exito';
    }

    /** El rol `consulta` no ve la acción de registrar (matriz de permisos). */
    public function getPuedeRegistrarProperty(): bool
    {
        return in_array($this->rol, ['operador', 'administrador'], true);
    }

    public function render()
    {
        return view('dominios.pacientes.buscador');
    }
}
