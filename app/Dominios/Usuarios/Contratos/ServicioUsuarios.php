<?php

namespace App\Dominios\Usuarios\Contratos;

/**
 * Operaciones de aplicación del dominio Usuarios.
 *
 * Firmas fijadas por Arquitectura en `docs/contratos/servicios-aplicacion.md`;
 * el detalle de cada operación vive en `docs/contratos/usuarios.md`. Los
 * errores se lanzan como `App\Compartido\Errores\ErrorDeAplicacion`, nunca se
 * devuelven como valor. Ningún método recibe el usuario autenticado: la
 * implementación lo resuelve de la sesión.
 */
interface ServicioUsuarios
{
    /** `POST /acceder` */
    public function autenticar(string $email, #[\SensitiveParameter] string $password, bool $recordar = false): array;

    /** `POST /salir` */
    public function salir(): void;

    /** `GET /usuarios` */
    public function listar(int $pagina = 1): array;

    /** `POST /usuarios` */
    public function crear(array $datos): array;

    /** `PATCH /usuarios/{id}` */
    public function actualizar(int $usuarioId, array $cambios): array;

    /** `GET /auditoria` */
    public function auditoria(array $filtros = [], int $pagina = 1): array;
}
