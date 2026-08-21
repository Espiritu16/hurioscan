<?php

namespace App\Compartido\Errores;

/**
 * Traducción de la taxonomía de `docs/errores/manejo-errores.md` al status HTTP.
 *
 * El **código** es la identidad estable del error y es lo que las pruebas
 * verifican; el status es solo cómo se representa en una petición web completa.
 * La tabla vive aquí completa —incluidos los códigos de dominios que todavía no
 * existen— porque es una traducción ya aprobada, y repartirla por sprints haría
 * que cada uno tuviera que redescubrir la mitad.
 */
final class EstadoHttpDelError
{
    /** Código => status HTTP, tal como los emparejó Arquitectura. */
    private const TABLA = [
        'VALIDACION_ENTRADA' => 422,
        'NO_AUTENTICADO' => 401,
        'NO_AUTORIZADO' => 403,
        'RECURSO_NO_ENCONTRADO' => 404,
        'PACIENTE_HC_DUPLICADO' => 409,
        'PACIENTE_DNI_DUPLICADO' => 409,
        'SESION_YA_ABIERTA' => 409,
        'TRANSICION_SESION_INVALIDA' => 409,
        'SESION_SIN_HOJAS' => 409,
        'SESION_CON_HOJAS_SIN_REVISAR' => 409,
        'SESION_CERRADA_NO_MODIFICABLE' => 409,
        'TRANSICION_DOCUMENTO_INVALIDA' => 409,
        'ESTADO_DOCUMENTO_INVALIDO' => 422,
        'HOJA_FORMATO_NO_SOPORTADO' => 422,
        'HOJA_DEMASIADO_GRANDE' => 422,
        'IDENTIDAD_NO_ENCONTRADA' => 404,
        'IDENTIDAD_PROVEEDOR_NO_DISPONIBLE' => 503,
        'OCR_NO_DISPONIBLE' => 503,
        'OCR_YA_PROCESADO' => 409,
        'VERSION_DESACTUALIZADA' => 409,
        'BUSQUEDA_TERMINO_VACIO' => 422,
        'PARAMETRO_LISTADO_INVALIDO' => 422,
        'ADMIN_NO_PUEDE_QUITARSE_ROL' => 409,
    ];

    /**
     * Un código fuera de la taxonomía no se degrada a 500 en silencio: eso
     * convertiría un error de programación en una respuesta plausible.
     */
    public static function para(string $codigo): int
    {
        return self::TABLA[$codigo]
            ?? throw new \LogicException("código fuera de la taxonomía de errores: {$codigo}");
    }

    /** @return list<string> */
    public static function codigos(): array
    {
        return array_keys(self::TABLA);
    }
}
