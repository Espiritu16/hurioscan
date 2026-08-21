<?php

namespace App\Dominios\Usuarios;

/**
 * A qué pantalla entra cada rol después de autenticarse.
 *
 * El rol `consulta` no tiene acceso al panel de avance —la matriz de permisos lo
 * deniega—, así que entra por la búsqueda de pacientes. Los otros dos entran por
 * el panel.
 */
final class DestinoSegunRol
{
    public static function ruta(string $rol): string
    {
        return $rol === 'consulta' ? 'pacientes' : 'avance';
    }
}
