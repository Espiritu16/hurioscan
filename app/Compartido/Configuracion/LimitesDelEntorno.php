<?php

namespace App\Compartido\Configuracion;

/**
 * Verificación de los límites de PHP que la aplicación necesita para funcionar.
 *
 * Deuda heredada de la cadena de frontend (QA-F-03 y QA-F-04): PHP trabaja sobre
 * la petición completa, así que si el cuerpo supera `post_max_size` descarta la
 * petición entera **antes** de que se ejecute una línea de código —`$_FILES`
 * llega vacío y el lote se pierde sin motivo visible—. `scripts/php/hurioscan.ini`
 * pone los valores correctos, pero solo se aplica si se arranca con
 * `scripts/servir-desarrollo.sh`; quien use `composer dev` directo corre con los
 * valores por defecto de la máquina y no se entera.
 *
 * Esta clase es la comprobación pura; `AppServiceProvider` la aplica al arrancar
 * una petición web. Que sea pura es a propósito: así se prueba de verdad, en vez
 * de depender del `php.ini` de quien corra la suite.
 */
final class LimitesDelEntorno
{
    /** Límite del producto por hoja (`docs/contratos/digitalizacion.md`). */
    public const LIMITE_DEL_PRODUCTO_BYTES = 15 * 1024 * 1024;

    /**
     * Directivas que deben permitir **más** que el límite del producto.
     *
     * Nunca lo mismo: el caso que hay que dejar funcionar es justamente el de
     * la hoja que excede el límite, porque debe llegar al dominio para que él
     * la rechace hoja por hoja con su mensaje, en vez de que PHP se lleve el
     * lote entero.
     */
    private const DIRECTIVAS = ['upload_max_filesize', 'post_max_size'];

    /**
     * @param  array<string, string>  $valores  directiva => valor tal como lo devuelve `ini_get`
     * @return list<string> un incumplimiento por directiva, vacío si todo está bien
     */
    public static function incumplimientos(array $valores): array
    {
        $incumplimientos = [];

        foreach (self::DIRECTIVAS as $directiva) {
            $crudo = $valores[$directiva] ?? '';
            $bytes = self::aBytes($crudo);

            // `0` significa «sin tope» en estas directivas: no es un límite bajo.
            if ($bytes === 0) {
                continue;
            }

            if ($bytes > self::LIMITE_DEL_PRODUCTO_BYTES) {
                continue;
            }

            $incumplimientos[] = sprintf(
                '%s vale %s y tiene que superar los 15 MB del límite del producto',
                $directiva,
                $crudo === '' ? '(sin valor)' : $crudo,
            );
        }

        return $incumplimientos;
    }

    /** El texto que ve quien arrancó mal el entorno. Dice qué pasa y qué hacer. */
    public static function explicacion(array $incumplimientos): string
    {
        return 'Los límites de subida de PHP están por debajo de lo que el producto necesita: '
            .implode('; ', $incumplimientos).'. '
            .'Con estos valores PHP descarta la petición entera antes de que la aplicación se ejecute, '
            .'y el operador pierde el lote sin ver ningún error. '
            .'Arranca con ./scripts/servir-desarrollo.sh, que aplica scripts/php/hurioscan.ini.';
    }

    /** Convierte la notación abreviada de PHP (`2M`, `60M`, `1G`) a bytes. */
    private static function aBytes(string $valor): int
    {
        $valor = trim($valor);

        if ($valor === '') {
            return -1;
        }

        $numero = (int) $valor;
        $sufijo = mb_strtolower(mb_substr($valor, -1));

        return match ($sufijo) {
            'k' => $numero * 1024,
            'm' => $numero * 1024 * 1024,
            'g' => $numero * 1024 * 1024 * 1024,
            default => $numero,
        };
    }
}
