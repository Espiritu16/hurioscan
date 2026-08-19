<?php

namespace App\Dominios\Usuarios\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Consulta del registro de auditoría (F07-UT-04).
 *
 * Los filtros viajan en la URL. Solo se muestran los campos de la allowlist:
 * ningún texto de documento ni contraseña aparece nunca.
 */
class ConsultaAuditoria extends Component
{
    #[Url]
    public string $entidad = '';

    #[Url]
    public string $accion = '';

    /** carga | vacio | error | exito */
    public string $estado = 'carga';

    public array $filas = [];

    public ?string $error = null;

    public function mount(ServicioUsuarios $usuarios): void
    {
        $this->cargar($usuarios);
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['entidad', 'accion'], true)) {
            $this->cargar(app(ServicioUsuarios::class));
        }
    }

    public function render()
    {
        return view('dominios.usuarios.auditoria');
    }

    private function cargar(ServicioUsuarios $usuarios): void
    {
        $filtros = array_filter([
            'entidad' => $this->entidad,
            'accion' => $this->accion,
        ], fn (string $v) => $v !== '');

        try {
            $respuesta = $usuarios->auditoria($filtros);
        } catch (ErrorDeAplicacion $e) {
            $this->estado = 'error';
            $this->error = $e->getCodigo() === 'PARAMETRO_LISTADO_INVALIDO'
                ? 'Ese filtro no es válido.'
                : 'No se pudo cargar la auditoría.';

            return;
        }

        $this->filas = $respuesta['datos'];
        $this->estado = $this->filas === [] ? 'vacio' : 'exito';
    }
}
