<?php

namespace App\Dominios\Usuarios\Acciones;

use App\Compartido\Errores\ErrorDeValidacion;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use App\Dominios\Usuarios\DestinoSegunRol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * `POST /acceder` — autentica y crea la sesión de usuario (RF-011).
 *
 * La interfaz habitual es el componente Livewire de F01, que llama al servicio
 * directamente. Esta ruta existe porque el contrato la declara como operación
 * propia y la matriz de permisos le da una fila: sin ella, `POST /acceder` sería
 * una operación documentada que no existe.
 */
class Acceder
{
    public function __invoke(Request $request, ServicioUsuarios $usuarios): RedirectResponse
    {
        $recordar = $this->recordar($request);

        $usuario = $usuarios->autenticar(
            (string) $request->input('email', ''),
            (string) $request->input('password', ''),
            $recordar,
        );

        return redirect()->route(DestinoSegunRol::ruta($usuario['rol']));
    }

    /**
     * `recordar` es opcional con default `false`, y solo admite booleanos
     * reales: una cadena ambigua como `"false"` **no** se interpreta como
     * verdadera por ser no vacía, que es justo lo que haría un cast directo.
     */
    private function recordar(Request $request): bool
    {
        $validador = Validator::make(
            $request->only('recordar'),
            ['recordar' => ['sometimes', 'boolean']],
            ['recordar.boolean' => 'El campo «recordar» solo admite verdadero o falso.'],
        );

        if ($validador->fails()) {
            throw ErrorDeValidacion::desde($validador);
        }

        return filter_var($request->input('recordar', false), FILTER_VALIDATE_BOOLEAN);
    }
}
