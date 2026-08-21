<?php

namespace App\Compartido\Persistencia;

/**
 * Instantes que viajan con su desplazamiento explícito (RNF-005).
 *
 * El formato de fecha por defecto de Eloquent (`Y-m-d H:i:s`) no lleva zona, y
 * eso deja dos huecos por los que un instante se desplaza en silencio:
 *
 * - **Al escribir**, PostgreSQL interpreta una cadena sin zona en la zona de la
 *   sesión. Si esa sesión no está en UTC —zona del servidor, un `SET TIME ZONE`
 *   de por medio, un pooler— lo que queda guardado es otro momento.
 * - **Al leer**, Carbon interpreta una cadena sin zona en la zona por defecto
 *   de PHP, con el mismo resultado.
 *
 * En ambos casos no falla nada: la fila se escribe, la fecha se muestra, y solo
 * está corrida unas horas. Con el desplazamiento explícito el valor es
 * absoluto y ninguna de las dos zonas puede moverlo.
 *
 * Lo usan los modelos de dominio; `docs/persistencia/modelo.md` exige
 * `timestamptz` para todo instante.
 */
trait InstantesEnUtc
{
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:sP';
    }
}
