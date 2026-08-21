<?php

namespace App\Compartido\Autorizacion;

use App\Compartido\Errores\ErrorDeAplicacion;
use Illuminate\Support\Facades\Auth;
use LogicException;

/**
 * Política de autorización de un dominio — el contrato interno que consumen los
 * servicios de aplicación (B02 a B07).
 *
 * Rechaza por defecto: una operación sin fila en la matriz **no pasa**, y una
 * operación cuya fila declara una condición de alcance exige que el servicio
 * diga si la condición se cumple. Omitirla no la da por buena: es un error de
 * programación y se nota como tal.
 *
 * El actor no se recibe como parámetro: se resuelve de la sesión, como fija
 * `docs/contratos/servicios-aplicacion.md`.
 */
abstract class PoliticaDeDominio
{
    /** Nombre del dominio tal como lo agrupa la matriz. */
    abstract public function dominio(): string;

    /**
     * Autoriza o lanza. Nunca devuelve `false`: un permiso que se puede ignorar
     * termina ignorándose.
     *
     * @param  bool|null  $alcanceCumplido  obligatorio si la fila del actor declara condición
     */
    public function exigir(string $operacion, ?bool $alcanceCumplido = null): void
    {
        $this->comprobarQueLaOperacionEsDelDominio($operacion);

        if (MatrizDePermisos::esPublica($operacion)) {
            return;
        }

        $actor = Auth::user();

        if ($actor === null) {
            throw new ErrorDeAplicacion('NO_AUTENTICADO', 'Necesitas iniciar sesión para continuar.');
        }

        $rol = (string) $actor->rol;

        if (! MatrizDePermisos::permite($operacion, $rol)) {
            throw new ErrorDeAplicacion('NO_AUTORIZADO', 'No tienes permiso para esta operación.');
        }

        $this->comprobarAlcance($operacion, $rol, $alcanceCumplido);
    }

    public function permite(string $operacion): bool
    {
        return MatrizDePermisos::permite($operacion, Auth::user()?->rol);
    }

    /**
     * Una operación que no pertenece al dominio de esta política casi siempre es
     * un nombre mal escrito, y un nombre mal escrito sin fila rechazaría con
     * `NO_AUTORIZADO`: parecería que la autorización funciona cuando lo que
     * falla es la llamada. Se separa para que se vea cuál de las dos cosas pasó.
     */
    private function comprobarQueLaOperacionEsDelDominio(string $operacion): void
    {
        if (! in_array($operacion, MatrizDePermisos::operacionesDe($this->dominio()), true)) {
            throw new LogicException(
                "«{$operacion}» no es una operación del dominio {$this->dominio()} en la matriz de permisos"
            );
        }
    }

    private function comprobarAlcance(string $operacion, string $rol, ?bool $alcanceCumplido): void
    {
        $condicion = MatrizDePermisos::condicionDeAlcance($operacion, $rol);

        if ($condicion === null) {
            return;
        }

        if ($alcanceCumplido === null) {
            throw new LogicException(
                "«{$operacion}» exige al rol {$rol} la condición de alcance «{$condicion}»: ".
                'la política necesita que el servicio le diga si se cumple.'
            );
        }

        if (! $alcanceCumplido) {
            // Fuera de alcance no se distingue de inexistente cuando el actor
            // no tiene permiso ni para saber que el recurso existe
            // (`docs/errores/manejo-errores.md`); esa elección la hace cada
            // servicio, que es quien sabe de qué recurso se trata.
            throw new ErrorDeAplicacion('NO_AUTORIZADO', 'No tienes permiso para esta operación.');
        }
    }
}
