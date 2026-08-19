<?php

namespace App\Dominios\Usuarios\Componentes;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

/**
 * Pantalla de acceso (F01-UT-02).
 *
 * Estados visibles: idle, enviando y error de credenciales. El mensaje de
 * error es el mismo para credencial inválida, usuario inexistente y usuario
 * inactivo, porque el contrato exige un único `NO_AUTENTICADO` que no revele
 * qué correos están registrados.
 */
class FormularioAcceso extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $recordar = false;

    /** idle | enviando | error */
    public string $estado = 'idle';

    public ?string $error = null;

    public function acceder(ServicioUsuarios $usuarios): void
    {
        $this->estado = 'enviando';
        $this->error = null;

        try {
            $usuario = $usuarios->autenticar($this->email, $this->password, $this->recordar);
        } catch (ErrorDeAplicacion $e) {
            // La vista decide por código, nunca comparando el texto del mensaje.
            $this->estado = 'error';
            $this->error = $e->getCodigo() === 'NO_AUTENTICADO'
                ? 'Correo o contraseña incorrectos.'
                : 'No se pudo completar el acceso. Inténtalo de nuevo.';
            $this->password = '';

            return;
        }

        $this->estado = 'idle';
        $this->redirect($this->destinoSegunRol($usuario['rol']));
    }

    public function render()
    {
        return view('dominios.usuarios.acceder');
    }

    /**
     * El rol `consulta` no tiene acceso al panel de avance (contrato de
     * `GET /avance`), así que entra por la búsqueda de pacientes.
     */
    private function destinoSegunRol(string $rol): string
    {
        $nombre = $rol === 'consulta' ? 'pacientes' : 'avance';

        // Mientras las rutas no estén declaradas, se vuelve al propio
        // formulario en vez de romper con una ruta inexistente.
        return Route::has($nombre) ? route($nombre) : '/';
    }
}
