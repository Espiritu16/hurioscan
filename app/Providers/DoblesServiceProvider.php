<?php

namespace App\Providers;

use App\Compartido\Dobles\Usuarios\ServicioUsuariosDoble;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;
use Illuminate\Support\ServiceProvider;

/**
 * Liga cada interfaz de dominio a su doble de desarrollo.
 *
 * Solo actúa si el interruptor de `config/dobles.php` está activo **y** el
 * entorno es `local` o `testing`. En cualquier otro entorno no liga nada: un
 * binding faltante falla de forma visible, que es preferible a servir datos de
 * ejemplo en producción sin que nadie lo note.
 *
 * Ver `docs/contratos/servicios-aplicacion.md` § Selección del doble.
 */
class DoblesServiceProvider extends ServiceProvider
{
    /**
     * Interruptor de `config/dobles.php` => [interfaz, doble].
     *
     * Cada dominio se agrega aquí en el sprint que construye su doble; un
     * interruptor sin entrada simplemente no liga nada.
     */
    private const DOBLES = [
        'usuarios' => [ServicioUsuarios::class, ServicioUsuariosDoble::class],
    ];

    public function register(): void
    {
        if (! $this->app->environment(['local', 'testing'])) {
            return;
        }

        foreach (self::DOBLES as $interruptor => [$interfaz, $doble]) {
            if (config("dobles.{$interruptor}") === true) {
                $this->app->singleton($interfaz, $doble);
            }
        }
    }
}
