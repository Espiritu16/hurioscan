<?php

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Compartido\Errores\EstadoHttpDelError;
use App\Http\Middleware\AutorizarOperacion;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        // Un `ErrorDeAplicacion` no se reporta por el camino por defecto de
        // Laravel, porque ese camino escribe el **stack trace** y el trace de
        // PHP lleva los argumentos de cada frame. El frame de
        // `autenticar($email, $password, $recordar)` deja ahí la contraseña en
        // claro: PHP recorta las cadenas a 15 caracteres y el validador exige
        // 8 como mínimo, así que toda contraseña de 8 a 15 caracteres quedaba
        // escrita entera (QA-B01-01, RNF-014, `docs/contratos/usuarios.md`).
        //
        // No se cambia una pérdida silenciosa por otra: en lugar del trace se
        // escribe una línea estructurada con la identidad del error —su
        // `codigo`— y el punto exacto donde se lanzó. Lo único que se pierde es
        // la cadena de llamadas, que es justo lo que transporta los secretos.
        // El nivel se deriva del status ya aprobado en
        // `docs/errores/manejo-errores.md`, sin inventar una segunda tabla que
        // mantener: un 4xx lo causa quien llama y es tráfico normal —un
        // `NO_AUTENTICADO` rutinario no es un incidente—; un 5xx es el sistema
        // fallando y sí lo es.
        $exceptions->report(function (ErrorDeAplicacion $e): bool {
            $estado = EstadoHttpDelError::para($e->getCodigo());

            Log::log($estado >= 500 ? 'error' : 'info', $e->getMessage(), [
                'codigo' => $e->getCodigo(),
                'estado' => $estado,
                'origen' => $e->getFile().':'.$e->getLine(),
            ]);

            // Detiene el reporte por defecto: devolver `false` es lo que
            // impide que Laravel vuelva a registrar la excepción con su trace.
            return false;
        });

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
