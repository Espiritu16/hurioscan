<?php

namespace App\Dominios\Usuarios\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use Livewire\Component;

/**
 * Administración de usuarios (F07-UT-03).
 *
 * El intento de quitarse el propio rol de administrador se rechaza con el
 * motivo visible. Ninguna vista muestra el hash de contraseña.
 */
class AdministracionUsuarios extends Component
{
    /** carga | vacio | error | exito */
    public string $estado = 'carga';

    public array $usuarios = [];

    public ?string $error = null;

    public ?string $aviso = null;

    public function mount(ServicioUsuarios $usuarios): void
    {
        $this->cargar($usuarios);
    }

    public function cambiarRol(int $usuarioId, string $rol, ServicioUsuarios $usuarios): void
    {
        $this->error = null;
        $this->aviso = null;

        try {
            $actualizado = $usuarios->actualizar($usuarioId, ['rol' => $rol]);
        } catch (ErrorDeAplicacion $e) {
            // El motivo se explica, no se silencia.
            $this->error = $e->getMessage();

            return;
        }

        foreach ($this->usuarios as $indice => $usuario) {
            if ($usuario['id'] === $usuarioId) {
                $this->usuarios[$indice]['rol'] = $actualizado['rol'];
            }
        }

        $this->aviso = 'Rol actualizado.';
    }

    public function cambiarActividad(int $usuarioId, bool $activo, ServicioUsuarios $usuarios): void
    {
        $this->error = null;
        $this->aviso = null;

        try {
            $actualizado = $usuarios->actualizar($usuarioId, ['activo' => $activo]);
        } catch (ErrorDeAplicacion $e) {
            $this->error = $e->getMessage();

            return;
        }

        foreach ($this->usuarios as $indice => $usuario) {
            if ($usuario['id'] === $usuarioId) {
                $this->usuarios[$indice]['activo'] = $actualizado['activo'];
            }
        }

        $this->aviso = $activo ? 'Usuario activado.' : 'Usuario desactivado.';
    }

    public function render()
    {
        return view('dominios.usuarios.administracion');
    }

    private function cargar(ServicioUsuarios $usuarios): void
    {
        try {
            $respuesta = $usuarios->listar();
        } catch (ErrorDeAplicacion) {
            $this->estado = 'error';
            $this->error = 'No se pudo cargar la lista de usuarios.';

            return;
        }

        $this->usuarios = $respuesta['datos'];
        $this->estado = $this->usuarios === [] ? 'vacio' : 'exito';
    }
}
