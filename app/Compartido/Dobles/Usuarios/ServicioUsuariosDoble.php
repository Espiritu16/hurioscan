<?php

namespace App\Compartido\Dobles\Usuarios;

use App\Compartido\Errores\ErrorDeAplicacion;
use App\Dominios\Usuarios\Contratos\ServicioUsuarios;

/**
 * Doble de desarrollo del dominio Usuarios (F01-UT-01).
 *
 * Sustituye a la implementación real de B01 mientras esta no existe. Se activa
 * solo por configuración (`DOBLE_USUARIOS`), en local o testing.
 */
class ServicioUsuariosDoble implements ServicioUsuarios
{
    private const CLAVE = 'hurioscan';

    /** Un usuario por rol, más uno inactivo para poder ver ese desenlace. */
    private const USUARIOS = [
        'operador@hurioscan.test' => ['id' => 1, 'nombre' => 'R. Quispe Tito', 'email' => 'operador@hurioscan.test', 'rol' => 'operador', 'activo' => true],
        'consulta@hurioscan.test' => ['id' => 2, 'nombre' => 'L. Bustamante', 'email' => 'consulta@hurioscan.test', 'rol' => 'consulta', 'activo' => true],
        'admin@hurioscan.test' => ['id' => 3, 'nombre' => 'M. Fernández', 'email' => 'admin@hurioscan.test', 'rol' => 'administrador', 'activo' => true],
        'inactivo@hurioscan.test' => ['id' => 4, 'nombre' => 'J. Huamán', 'email' => 'inactivo@hurioscan.test', 'rol' => 'operador', 'activo' => false],
    ];

    /**
     * Credencial inválida, usuario inexistente y usuario inactivo lanzan el
     * mismo `NO_AUTENTICADO` con el mismo mensaje, para no revelar qué correos
     * están registrados (`docs/contratos/usuarios.md`).
     */
    public function autenticar(string $email, #[\SensitiveParameter] string $password, bool $recordar = false): array
    {
        $usuario = self::USUARIOS[mb_strtolower(trim($email))] ?? null;

        if ($usuario === null || ! $usuario['activo'] || $password !== self::CLAVE) {
            throw new ErrorDeAplicacion('NO_AUTENTICADO', 'Correo o contraseña incorrectos.');
        }

        return $usuario;
    }

    public function salir(): void
    {
        // El doble no mantiene sesión de servidor: nada que invalidar.
    }

    public function listar(int $pagina = 1): array
    {
        // Nunca incluye el hash de contraseña, como exige el contrato.
        $datos = array_values(self::USUARIOS);

        return [
            'datos' => array_map(
                fn (array $usuario) => $usuario + ['creadoEn' => '2026-08-01T09:00:00Z'],
                $datos,
            ),
            'meta' => $this->meta(count($datos), $pagina),
        ];
    }

    public function crear(array $datos): array
    {
        return [
            'id' => 99,
            'nombre' => $datos['nombre'] ?? 'Usuario nuevo',
            'email' => $datos['email'] ?? 'nuevo@hurioscan.test',
            'rol' => $datos['rol'] ?? 'operador',
            'activo' => true,
            'creadoEn' => '2026-08-19T10:00:00Z',
        ];
    }

    /**
     * El administrador de la sesión de ejemplo es el id 3: quitarse a sí mismo
     * el rol o desactivarse se rechaza, porque el sistema podría quedar sin
     * ningún administrador activo.
     */
    public function actualizar(int $usuarioId, array $cambios): array
    {
        $propio = 3;

        $seQuitaElRol = $usuarioId === $propio
            && ((isset($cambios['rol']) && $cambios['rol'] !== 'administrador') || ($cambios['activo'] ?? true) === false);

        if ($seQuitaElRol) {
            throw new ErrorDeAplicacion(
                'ADMIN_NO_PUEDE_QUITARSE_ROL',
                'No puedes quitarte a ti mismo el rol de administrador: el sistema quedaría sin ningún administrador activo.',
            );
        }

        $usuario = array_values(array_filter(
            self::USUARIOS,
            fn (array $u) => $u['id'] === $usuarioId,
        ))[0] ?? throw new ErrorDeAplicacion('RECURSO_NO_ENCONTRADO', 'El usuario no existe.');

        return array_merge($usuario, $cambios, ['actualizadoEn' => '2026-08-19T10:00:00Z']);
    }

    public function auditoria(array $filtros = [], int $pagina = 1): array
    {
        $filas = [
            ['id' => 501, 'entidad' => 'Documento', 'entidadId' => 8142, 'accion' => 'consultar', 'usuario' => ['id' => 2, 'nombre' => 'L. Bustamante'], 'origen' => 'web', 'fecha' => '2026-08-19T07:40:00Z', 'valoresAnteriores' => null, 'valoresNuevos' => null],
            ['id' => 500, 'entidad' => 'Paciente', 'entidadId' => 1204, 'accion' => 'crear', 'usuario' => ['id' => 1, 'nombre' => 'R. Quispe Tito'], 'origen' => 'web', 'fecha' => '2026-08-19T07:56:00Z', 'valoresAnteriores' => null, 'valoresNuevos' => ['numeroHistoria' => '04-116-4412']],
            ['id' => 499, 'entidad' => 'Documento', 'entidadId' => 8140, 'accion' => 'actualizar', 'usuario' => null, 'origen' => 'sistema', 'fecha' => '2026-08-19T06:12:00Z', 'valoresAnteriores' => ['estadoRevision' => 'PENDIENTE_OCR'], 'valoresNuevos' => ['estadoRevision' => 'EN_REVISION']],
        ];

        if (isset($filtros['entidad'])) {
            $filas = array_values(array_filter($filas, fn (array $f) => $f['entidad'] === $filtros['entidad']));
        }

        if (isset($filtros['accion'])) {
            $filas = array_values(array_filter($filas, fn (array $f) => $f['accion'] === $filtros['accion']));
        }

        return ['datos' => $filas, 'meta' => $this->meta(count($filas), $pagina)];
    }

    private function meta(int $total, int $pagina, int $porPagina = 20): array
    {
        return [
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'total' => $total,
            'totalPaginas' => max(1, (int) ceil($total / $porPagina)),
        ];
    }
}
