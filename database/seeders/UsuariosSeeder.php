<?php

namespace Database\Seeders;

use App\Dominios\Usuarios\Usuario;
use Illuminate\Database\Seeder;

/**
 * Un usuario por rol, para poder recorrer la aplicación con cada uno.
 *
 * Los correos y la contraseña son los mismos que usaba el doble de desarrollo
 * (`ServicioUsuariosDoble`), a propósito: al apagar el doble y quedar la
 * implementación real, quien venía probando la interfaz entra con las mismas
 * credenciales y no descubre el cambio como si fuera un fallo.
 *
 * Son credenciales de **desarrollo**, no un secreto: viven aquí porque el
 * seeder solo se corre a mano sobre una base descartable. Ningún despliegue
 * real las usa; el proyecto no tiene despliegue productivo (AGENTS.md).
 */
class UsuariosSeeder extends Seeder
{
    public const CLAVE_DE_DESARROLLO = 'hurioscan';

    private const USUARIOS = [
        ['nombre' => 'R. Quispe Tito', 'email' => 'operador@hurioscan.test', 'rol' => 'operador', 'activo' => true],
        ['nombre' => 'L. Bustamante', 'email' => 'consulta@hurioscan.test', 'rol' => 'consulta', 'activo' => true],
        ['nombre' => 'M. Fernández', 'email' => 'admin@hurioscan.test', 'rol' => 'administrador', 'activo' => true],
        // Una cuenta desactivada: es el desenlace que más cuesta reproducir a
        // mano y el que la pantalla de acceso debe tratar como cualquier otro
        // fallo de credencial.
        ['nombre' => 'J. Huamán', 'email' => 'inactivo@hurioscan.test', 'rol' => 'operador', 'activo' => false],
    ];

    public function run(): void
    {
        foreach (self::USUARIOS as $usuario) {
            Usuario::query()->updateOrCreate(
                ['email' => $usuario['email']],
                $usuario + ['password' => self::CLAVE_DE_DESARROLLO],
            );
        }
    }
}
