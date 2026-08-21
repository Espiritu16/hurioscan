<?php

namespace App\Http\Middleware;

use App\Compartido\Autorizacion\MatrizDePermisos;
use App\Compartido\Errores\ErrorDeAplicacion;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de rol — la mitad de RNF-013 que se puede resolver en la ruta.
 *
 * Cada ruta declara qué operación de la matriz sirve:
 *
 *     ->middleware('autorizar:GET /pacientes')
 *
 * Y a partir de ahí solo hay tres desenlaces: la operación es pública y pasa;
 * el rol tiene fila y pasa; o **rechaza**. No hay cuarto caso, y esa es la
 * propiedad: una ruta sin operación declarada, o con una operación que nadie
 * puso en la matriz, no se sirve. Agregar una pantalla sin decidir quién puede
 * verla deja de ser posible sin darse cuenta.
 *
 * Lo que este middleware **no** hace es evaluar las condiciones de alcance
 * («solo las propias», «solo si la sesión está CERRADA»): son condiciones sobre
 * datos que solo el servicio conoce al cargar la entidad. Las aplica
 * `PoliticaDeDominio` desde el servicio. Ocultar o bloquear en la ruta es la
 * primera capa, nunca la única.
 */
class AutorizarOperacion
{
    public function handle(Request $request, Closure $next, ?string $operacion = null): Response
    {
        if ($operacion === null || ! MatrizDePermisos::existe($operacion)) {
            return $this->rechazarPorFaltaDeFila($request, $operacion);
        }

        if (MatrizDePermisos::esPublica($operacion)) {
            return $next($request);
        }

        $actor = Auth::user();

        if ($actor === null) {
            throw new AuthenticationException;
        }

        // Una cuenta desactivada después de entrar es una credencial que dejó
        // de valer, y eso es `NO_AUTENTICADO`: sin esto la sesión ya abierta
        // seguiría sirviendo hasta que caducara sola.
        if (! $actor->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw new AuthenticationException;
        }

        if (! MatrizDePermisos::permite($operacion, $actor->rol)) {
            throw new ErrorDeAplicacion('NO_AUTORIZADO', 'No tienes permiso para esta operación.');
        }

        return $next($request);
    }

    /**
     * Rechaza y deja constancia. El rechazo protege al usuario; el log es para
     * quien montó la ruta, porque desde fuera un 403 legítimo y una ruta mal
     * declarada se ven igual.
     */
    private function rechazarPorFaltaDeFila(Request $request, ?string $operacion): Response
    {
        Log::warning('Ruta sin fila en la matriz de permisos: rechazada por defecto.', [
            'ruta' => $request->route()?->getName() ?? $request->path(),
            'operacion' => $operacion ?? '(no declarada)',
        ]);

        throw new ErrorDeAplicacion('NO_AUTORIZADO', 'No tienes permiso para esta operación.');
    }
}
