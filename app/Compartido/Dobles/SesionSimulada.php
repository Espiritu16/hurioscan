<?php

namespace App\Compartido\Dobles;

/**
 * Doble de desarrollo del servicio de sesión (F01-UT-01).
 *
 * Sustituye a la autenticación real de B01 mientras esta no existe. Solo debe
 * activarse en entorno local y de pruebas; el binding por entorno lo declara
 * quien tenga autoridad sobre la configuración (pendiente, ver handoff F01).
 *
 * Firma que B01 implementará: los métodos públicos de esta clase.
 */
class SesionSimulada
{
    /**
     * Usuarios de ejemplo, uno por rol. La contraseña es la misma para los tres
     * y solo existe en desarrollo.
     */
    private const USUARIOS = [
        'operador@hurioscan.test' => ['id' => 1, 'nombre' => 'R. Quispe Tito', 'rol' => 'operador', 'activo' => true],
        'consulta@hurioscan.test' => ['id' => 2, 'nombre' => 'L. Bustamante', 'rol' => 'consulta', 'activo' => true],
        'admin@hurioscan.test' => ['id' => 3, 'nombre' => 'M. Fernández', 'rol' => 'administrador', 'activo' => true],
        'inactivo@hurioscan.test' => ['id' => 4, 'nombre' => 'J. Huamán', 'rol' => 'operador', 'activo' => false],
    ];

    private const CLAVE = 'hurioscan';

    /**
     * Devuelve el usuario autenticado, o null si las credenciales no son
     * válidas. Credencial inválida, usuario inexistente y usuario inactivo
     * devuelven lo mismo a propósito: el contrato exige un único
     * `NO_AUTENTICADO` para no revelar qué correos están registrados.
     */
    public function autenticar(string $email, string $password): ?array
    {
        $usuario = self::USUARIOS[mb_strtolower(trim($email))] ?? null;

        if ($usuario === null || ! $usuario['activo'] || $password !== self::CLAVE) {
            return null;
        }

        return $usuario;
    }

    /**
     * Ruta de destino tras acceder, según el rol. El rol `consulta` no tiene
     * acceso al panel de avance, así que entra por la búsqueda de pacientes.
     */
    public function destinoSegunRol(string $rol): string
    {
        return $rol === 'consulta' ? 'pacientes' : 'avance';
    }
}
