<?php

namespace App\Providers;

use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use App\Dominios\Usuarios\ServicioUsuariosEloquent;
use Illuminate\Support\ServiceProvider;

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
}
