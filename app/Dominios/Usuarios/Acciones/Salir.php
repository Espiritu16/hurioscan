<?php

namespace App\Dominios\Usuarios\Acciones;

use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use Illuminate\Http\RedirectResponse;

/**
 * `POST /salir` — cierra la sesión de usuario (RF-011).
 *
 * Devuelve siempre al formulario de acceso. La ruta exige autenticación, como
 * declara la matriz de permisos; una vez cerrada la sesión, repetir la
 * operación vuelve a redirigir sin error.
 */
class Salir
{
    public function __invoke(ServicioUsuarios $usuarios): RedirectResponse
    {
        $usuarios->salir();

        return redirect()->route('acceder');
    }
}
