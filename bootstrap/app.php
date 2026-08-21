<?php

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Compartido\Errores\EstadoHttpDelError;
use App\Http\Middleware\AutorizarOperacion;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sin credencial se vuelve al formulario de acceso. Una petición que
        // espera JSON recibe en cambio `NO_AUTENTICADO` con su 401, más abajo.
        $middleware->redirectGuestsTo(fn () => route('acceder'));

        $middleware->alias(['autorizar' => AutorizarOperacion::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // La contraseña no se reenvía a la sesión al fallar una validación
        // (RNF-014): de ahí saldría a un log o a un volcado de depuración.
        $exceptions->dontFlash(['password', 'password_confirmation', 'current_password']);

        // Toda condición de error del dominio sale con el status que le asigna
        // `docs/errores/manejo-errores.md`. El cuerpo conserva el `codigo`, que
        // es la identidad estable del error; el `mensaje` es para la persona y
        // puede cambiar sin romper nada.
        //
        // No hay vista de error propia: las vistas son de la línea frontend y
        // B01 no entrega pantallas. Hasta que exista, una petición web completa
        // recibe el mismo cuerpo estructurado con su status correcto, que es lo
        // que las pruebas verifican.
        $exceptions->render(function (ErrorDeAplicacion $e, Request $request) {
            return response()->json([
                'codigo' => $e->getCodigo(),
                'mensaje' => $e->getMessage(),
                'detalle' => $e->getDetalle(),
            ], EstadoHttpDelError::para($e->getCodigo()));
        });

        // Una credencial ausente, inválida o expirada es siempre
        // `NO_AUTENTICADO`, nunca se degrada a «tratar como anónimo».
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'codigo' => 'NO_AUTENTICADO',
                'mensaje' => 'Necesitas iniciar sesión para continuar.',
                'detalle' => [],
            ], EstadoHttpDelError::para('NO_AUTENTICADO'));
        });
    })->create();
