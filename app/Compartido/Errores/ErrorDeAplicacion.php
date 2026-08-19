<?php

namespace App\Compartido\Errores;

use RuntimeException;

/**
 * Excepción base de las operaciones de aplicación.
 *
 * El código es la identidad estable del error y pertenece a la taxonomía de
 * `docs/errores/manejo-errores.md`; el mensaje es texto para la persona y puede
 * cambiar sin romper nada. La vista decide siempre por código, nunca comparando
 * el texto del mensaje.
 *
 * Firma fijada por Arquitectura en `docs/contratos/servicios-aplicacion.md`.
 */
class ErrorDeAplicacion extends RuntimeException
{
    public function __construct(
        private readonly string $codigo,
        string $mensaje = '',
        private readonly array $detalle = [],
    ) {
        parent::__construct($mensaje);
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getDetalle(): array
    {
        return $this->detalle;
    }
}
