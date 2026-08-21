<?php

namespace App\Compartido\Autorizacion;

/**
 * Transcripción de la matriz de `docs/requisitos/actores-permisos.md`.
 *
 * Es la fuente concreta contra la que se verifica RNF-013, y aquí es fuente
 * única: la consultan tanto el middleware de rol como las políticas de dominio,
 * de modo que la ruta y el servicio no pueden discrepar.
 *
 * **Solo se transcriben las filas que permiten.** Las filas «No» del documento
 * son redundantes con la regla de fondo —toda combinación que no aparece se
 * trata como denegada— y repetirlas invitaría a leer una ausencia como un
 * permiso olvidado. La precedencia del documento («entre las filas que aplican,
 * gana la denegación») queda cubierta por lo mismo.
 *
 * Cada operación mapea rol => condición de alcance. La condición se guarda como
 * texto porque **el middleware no puede evaluarla**: son condiciones sobre
 * datos («la sesión es propia», «la sesión está CERRADA») que solo el servicio
 * de dominio conoce al cargar la entidad. El middleware aplica la mitad de rol;
 * la política aplica las dos. Tenerlas declaradas evita que una condición
 * desaparezca por olvido al implementar su dominio.
 */
final class MatrizDePermisos
{
    /** Operación alcanzable sin credencial: el actor `Anónimo` del documento. */
    public const PUBLICA = 'publica';

    /**
     * Dominio => operación => (rol => condición de alcance o null).
     *
     * @var array<string, array<string, array<string, string|null>|string>>
     */
    private const MATRIZ = [
        'Usuarios' => [
            'GET /acceder' => self::PUBLICA,
            'POST /acceder' => self::PUBLICA,
            'POST /salir' => ['operador' => null, 'consulta' => null, 'administrador' => null],
            'GET /usuarios' => ['administrador' => null],
            'POST /usuarios' => ['administrador' => null],
            'PATCH /usuarios/{id}' => ['administrador' => null],
            'GET /auditoria' => ['administrador' => null],
        ],
        'Pacientes' => [
            'GET /pacientes' => ['operador' => null, 'consulta' => null, 'administrador' => null],
            'POST /pacientes' => ['operador' => null, 'administrador' => null],
            'POST /pacientes/consultar-dni' => ['operador' => null, 'administrador' => null],
            'GET /pacientes/{id}' => ['operador' => null, 'consulta' => null, 'administrador' => null],
        ],
        'Digitalizacion' => [
            'POST /sesiones' => ['operador' => null],
            'GET /sesiones/pendientes' => ['operador' => 'sesion.operador_id == actor.id', 'administrador' => null],
            'GET /sesiones/{id}' => ['operador' => 'sesion.operador_id == actor.id', 'administrador' => null],
            'POST /sesiones/{id}/hojas' => ['operador' => 'sesión propia y en estado ABIERTA'],
            'DELETE /sesiones/{id}/hojas/{hoja}' => ['operador' => 'sesión propia y en estado ABIERTA'],
            'POST /sesiones/{id}/enviar-a-revision' => ['operador' => 'sesión propia, ABIERTA, con al menos una hoja'],
            'POST /sesiones/{id}/volver-a-captura' => ['operador' => 'sesión propia y en estado EN_REVISION'],
            'POST /sesiones/{id}/cerrar' => ['operador' => 'sesión propia, EN_REVISION, sin hojas sin revisar'],
            'GET /avance' => ['operador' => null, 'administrador' => null],
        ],
        'Documentos' => [
            'PATCH /documentos/{id}/texto' => ['operador' => 'documento de una sesión propia no CERRADA'],
            'POST /documentos/{id}/marcar' => ['operador' => 'documento de una sesión propia no CERRADA'],
            'POST /documentos/{id}/reabrir-revision' => ['operador' => 'documento de una sesión propia no CERRADA', 'administrador' => null],
            'POST /documentos/{id}/reintentar-ocr' => ['operador' => 'documento propio en PENDIENTE_OCR con fallo registrado'],
            'GET /buscar' => ['operador' => null, 'consulta' => null, 'administrador' => null],
            'GET /documentos/{id}' => [
                'operador' => 'documento de una sesión CERRADA, o de una sesión propia',
                'consulta' => 'documento de una sesión CERRADA',
                'administrador' => null,
            ],
            'GET /documentos/{id}/imagen' => [
                'operador' => 'mismo alcance que ver documento',
                'consulta' => 'mismo alcance que ver documento',
                'administrador' => null,
            ],
            'GET /ilegibles' => ['operador' => 'solo las de sesiones propias', 'administrador' => null],
        ],
    ];

    public static function esPublica(string $operacion): bool
    {
        return self::fila($operacion) === self::PUBLICA;
    }

    /** `true` solo si existe una fila que permita a ese rol esa operación. */
    public static function permite(string $operacion, ?string $rol): bool
    {
        $fila = self::fila($operacion);

        if ($fila === self::PUBLICA) {
            return true;
        }

        // Sin fila no hay permiso: es la regla de fondo de RNF-013, y por eso
        // una operación que nadie declaró rechaza en vez de dejar pasar.
        return $fila !== null && $rol !== null && array_key_exists($rol, $fila);
    }

    /** Condición de alcance declarada para ese rol, o `null` si no tiene. */
    public static function condicionDeAlcance(string $operacion, string $rol): ?string
    {
        $fila = self::fila($operacion);

        return is_array($fila) ? ($fila[$rol] ?? null) : null;
    }

    public static function existe(string $operacion): bool
    {
        return self::fila($operacion) !== null;
    }

    /** @return list<string> operaciones declaradas para ese dominio */
    public static function operacionesDe(string $dominio): array
    {
        return array_keys(self::MATRIZ[$dominio] ?? []);
    }

    /** @return list<string> */
    public static function dominios(): array
    {
        return array_keys(self::MATRIZ);
    }

    /** @return array<string, string|null>|string|null */
    private static function fila(string $operacion): array|string|null
    {
        foreach (self::MATRIZ as $operaciones) {
            if (isset($operaciones[$operacion])) {
                return $operaciones[$operacion];
            }
        }

        return null;
    }
}
