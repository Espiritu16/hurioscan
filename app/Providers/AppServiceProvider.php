<?php

namespace App\Providers;

use App\Compartido\Configuracion\LimitesDelEntorno;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use App\Dominios\Usuarios\ServicioUsuariosEloquent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Implementación real de cada dominio.
     *
     * `DoblesServiceProvider` se registra después (ver `bootstrap/providers.php`)
     * y sustituye estas ligaduras cuando el interruptor de su dominio está
     * activo. Con los interruptores en `false` —el valor por defecto— manda la
     * implementación real.
     */
    private const SERVICIOS = [
        ServicioUsuarios::class => ServicioUsuariosEloquent::class,
    ];

    public function register(): void
    {
        foreach (self::SERVICIOS as $interfaz => $implementacion) {
            $this->app->singleton($interfaz, $implementacion);
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $this->impedirQueUnAvisoContamineLaRespuesta();
        $this->verificarLimitesDeSubida();
    }

    /**
     * Deuda de fondo de QA-F-04.
     *
     * Con `display_errors` activo, un aviso de PHP se imprime **dentro del
     * cuerpo de la respuesta**: el JSON sale precedido de HTML, el cliente falla
     * al parsearlo y la operación se pierde entera sin que nadie vea un error.
     *
     * Aquí se apaga la salida y se deja constancia en el log, para no cambiar
     * una pérdida silenciosa por otra. **No cierra la clase completa**: los
     * avisos que PHP emite antes de arrancar el framework —los de «Request
     * Startup»— ocurren antes de esta línea y solo los evita
     * `scripts/php/hurioscan.ini`. Cierra todo lo que ocurre a partir del
     * arranque, que es lo que sí está al alcance de la aplicación.
     */
    private function impedirQueUnAvisoContamineLaRespuesta(): void
    {
        if (! filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        ini_set('display_errors', '0');

        Log::warning(
            'display_errors estaba activo al atender una petición: un aviso de PHP habría '
            .'contaminado el cuerpo de la respuesta. Se apagó para esta petición; corrige el '
            .'entorno con scripts/php/hurioscan.ini.'
        );
    }

    /**
     * Deuda heredada: que la aplicación verifique sus propios límites de subida
     * al arrancar y falle de forma visible si están por debajo del límite del
     * producto.
     *
     * Falla en vez de avisar porque el defecto que evita es silencioso por
     * naturaleza: con los límites bajos no hay error que ver, solo hojas que no
     * llegan. Un arranque que se niega a servir es ruidoso, y eso es
     * exactamente lo que hacía falta.
     */
    private function verificarLimitesDeSubida(): void
    {
        $incumplimientos = LimitesDelEntorno::incumplimientos([
            'upload_max_filesize' => (string) ini_get('upload_max_filesize'),
            'post_max_size' => (string) ini_get('post_max_size'),
        ]);

        if ($incumplimientos !== []) {
            throw new RuntimeException(LimitesDelEntorno::explicacion($incumplimientos));
        }
    }
}
